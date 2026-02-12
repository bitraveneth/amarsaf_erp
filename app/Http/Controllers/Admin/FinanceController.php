<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\LedgerEntry;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('order.agent')->latest()->paginate(10);
        return view('admin.finance.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.agent', 'items.product', 'receipts', 'creditNotes']);
        return view('admin.finance.show', compact('invoice'));
    }

    public function updateWithholding(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'withholding' => 'required|numeric|min:0',
        ]);

        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        if ($data['withholding'] > $grossTotal) {
            return back()->withErrors([
                'withholding' => 'Withholding cannot exceed invoice total (net + VAT).',
            ]);
        }

        $invoice->withholding = $data['withholding'];
        $invoice->save();

        // Recalculate status based on new withholding, existing credits and receipts
        $cashTotal  = $grossTotal - $invoice->withholding;
        $credited   = $invoice->creditNotes()->sum('amount');
        $received   = $invoice->receipts()->sum('amount');
        $outstanding = $cashTotal - $credited - $received;

        if ($outstanding <= 0) {
            $invoice->update(['status' => 'paid']);
        } elseif ($received > 0 || $credited > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } else {
            $invoice->update(['status' => 'issued']);
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Withholding updated.');
    }

    public function createFromOrder(Order $order)
    {
        if ($order->status !== 'delivered') {
            abort(400, 'Only delivered orders can be invoiced.');
        }

        $order->loadMissing('items.product.taxClass', 'agent');

        $netTotal = 0;
        $vatAmount = 0;

        foreach ($order->items as $item) {
            $lineTotal = $item->quantity * $item->unit_price;
            $netTotal += $lineTotal;

            $rate = optional($item->product->taxClass)->rate ?? 0;
            if ($rate > 0) {
                $vatAmount += $lineTotal * ($rate / 100);
            }
        }

        if ($netTotal == 0) {
            $netTotal = $order->total;
        }

        $vatAmount = round($vatAmount, 2);

        $invoiceData = [
            'order_id' => $order->id,
            'number' => 'INV-' . str_pad((string)(Invoice::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'issued_at' => Carbon::today(),
            'due_at' => Carbon::today()->addDays(7),
            'net_total' => $netTotal,
            'vat_amount' => $vatAmount,
            'withholding' => 0,
            'status' => 'issued',
        ];

        // Auto-calc withholding based on agent's configured rate (if any)
        $agent = $order->agent;
        if ($agent && $agent->withholding_rate > 0) {
            $grossTotal = $netTotal + $vatAmount;
            $invoiceData['withholding'] = round($grossTotal * ($agent->withholding_rate / 100), 2);
        }

        $invoice = Invoice::create($invoiceData);

        foreach ($order->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'description' => $item->product?->name ?? 'Order item',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->quantity * $item->unit_price,
            ]);
        }

        LedgerEntry::create([
            'account' => 'Accounts Receivable',
            'description' => 'Invoice ' . $invoice->number,
            'debit' => $netTotal + $vatAmount,
            'credit' => 0,
            'order_id' => $order->id,
            'invoice_id' => $invoice->id,
        ]);

        LedgerEntry::create([
            'account' => 'Sales Revenue',
            'description' => 'Invoice ' . $invoice->number,
            'debit' => 0,
            'credit' => $netTotal,
            'order_id' => $order->id,
            'invoice_id' => $invoice->id,
        ]);

        if ($vatAmount > 0) {
            LedgerEntry::create([
                'account' => 'VAT Payable',
                'description' => 'VAT on ' . $invoice->number,
                'debit' => 0,
                'credit' => $vatAmount,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);
        }

        return redirect()->route('admin.finance.index')->with('status', 'Invoice created from order.');
    }

    public function storeReceipt(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'received_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $receipt = Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'received_at' => $data['received_at'] ?? Carbon::today(),
            'notes' => $data['notes'] ?? null,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => 'Receipt for ' . $invoice->number,
            'debit' => $receipt->amount,
            'credit' => 0,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);

        LedgerEntry::create([
            'account' => 'Accounts Receivable',
            'description' => 'Receipt for ' . $invoice->number,
            'debit' => 0,
            'credit' => $receipt->amount,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);

        // Recalculate net outstanding considering withholding, credit notes and receipts
        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        $cashTotal  = $grossTotal - $invoice->withholding;
        $credited   = $invoice->creditNotes()->sum('amount');
        $received   = $invoice->receipts()->sum('amount');
        $outstanding = $cashTotal - $credited - $received;

        if ($outstanding <= 0) {
            $invoice->update(['status' => 'paid']);
        } elseif ($received > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } else {
            $invoice->update(['status' => 'issued']);
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Receipt recorded.');
    }

    public function destroyReceipt(Receipt $receipt)
    {
        $invoice = $receipt->invoice;

        if ($invoice) {
            LedgerEntry::where('invoice_id', $invoice->id)
                ->whereIn('account', ['Bank', 'Accounts Receivable'])
                ->where(function ($query) use ($receipt) {
                    $query->where('debit', $receipt->amount)
                          ->orWhere('credit', $receipt->amount);
                })
                ->delete();
        }

        $amount = $receipt->amount;
        $receipt->delete();

        if ($invoice) {
            $grossTotal = $invoice->net_total + $invoice->vat_amount;
            $cashTotal  = $grossTotal - $invoice->withholding;
            $credited   = $invoice->creditNotes()->sum('amount');
            $paid       = $invoice->receipts()->sum('amount');
            $outstanding = $cashTotal - $credited - $paid;

            if ($outstanding <= 0) {
                $invoice->update(['status' => 'paid']);
            } elseif ($paid > 0 || $credited > 0) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'issued']);
            }
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Receipt deleted.');
    }

    public function showCreditNoteForm(Invoice $invoice)
    {
        $invoice->load('order.agent');
        return view('admin.finance.credit_note', compact('invoice'));
    }

    public function storeCreditNote(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);
        // Maximum credit cannot exceed outstanding cash amount after withholding
        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        $cashTotal  = $grossTotal - $invoice->withholding;
        $maxCredit = $cashTotal;
        $alreadyCredited = CreditNote::where('invoice_id', $invoice->id)->sum('amount');

        if ($data['amount'] > ($maxCredit - $alreadyCredited)) {
            return back()->withErrors(['amount' => 'Credit amount exceeds remaining invoice value.']);
        }

        $credit = CreditNote::create([
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id,
            'number' => 'CN-' . str_pad((string)(CreditNote::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'issued_at' => Carbon::today(),
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? null,
        ]);

        LedgerEntry::create([
            'account' => 'Sales Returns',
            'description' => 'Credit note ' . $credit->number,
            'debit' => $credit->amount,
            'credit' => 0,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);

        LedgerEntry::create([
            'account' => 'Accounts Receivable',
            'description' => 'Credit note ' . $credit->number,
            'debit' => 0,
            'credit' => $credit->amount,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);

        // Recalculate status after applying credit
        $credited   = $invoice->creditNotes()->sum('amount');
        $received   = $invoice->receipts()->sum('amount');
        $outstanding = $cashTotal - $credited - $received;

        if ($outstanding <= 0) {
            $invoice->update(['status' => 'paid']);
        } elseif ($received > 0 || $credited > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } else {
            $invoice->update(['status' => 'issued']);
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Credit note created.');
    }

    public function destroyCreditNote(CreditNote $creditNote)
    {
        $invoice = $creditNote->invoice;

        if ($invoice) {
            LedgerEntry::where('invoice_id', $invoice->id)
                ->where('description', 'Credit note ' . $creditNote->number)
                ->delete();
        }

        $creditNote->delete();

        if ($invoice) {
            $grossTotal = $invoice->net_total + $invoice->vat_amount;
            $cashTotal  = $grossTotal - $invoice->withholding;
            $credited   = $invoice->creditNotes()->sum('amount');
            $received   = $invoice->receipts()->sum('amount');
            $outstanding = $cashTotal - $credited - $received;

            if ($outstanding <= 0) {
                $invoice->update(['status' => 'paid']);
            } elseif ($received > 0 || $credited > 0) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'issued']);
            }
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Credit note deleted.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->receipts()->exists() || $invoice->creditNotes()->exists()) {
            return redirect()->route('admin.finance.index')
                ->with('status', 'Invoice has receipts or credit notes and cannot be deleted.');
        }

        LedgerEntry::where('invoice_id', $invoice->id)->delete();
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('admin.finance.index')->with('status', 'Invoice deleted.');
    }
}
