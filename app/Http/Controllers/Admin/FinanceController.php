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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $invoice->recalculateStatus();

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Withholding updated.');
    }

    public function createFromOrder(Order $order)
    {
        if ($order->status !== 'delivered') {
            abort(400, 'Only delivered orders can be invoiced.');
        }

        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return redirect()
                ->route('admin.finance.show', $existingInvoice)
                ->with('status', 'Invoice already exists for this order.');
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

        $invoice = null;

        DB::transaction(function () use ($invoiceData, $order, $netTotal, $vatAmount, &$invoice) {
            $invoice = Invoice::create(array_merge($invoiceData, [
                'number' => 'INV-TMP-' . Str::uuid(),
            ]));

            $invoice->update([
                'number' => $this->formatInvoiceNumber($invoice->id),
            ]);

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
        });

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

        $receipt = null;

        DB::transaction(function () use ($invoice, $data, &$receipt) {
            $receipt = Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'received_at' => $data['received_at'] ?? Carbon::today(),
                'notes' => $data['notes'] ?? null,
            ]);

            $description = $this->receiptLedgerDescription($receipt, $invoice);

            LedgerEntry::create([
                'account' => 'Bank',
                'description' => $description,
                'debit' => $receipt->amount,
                'credit' => 0,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => $description,
                'debit' => 0,
                'credit' => $receipt->amount,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            $invoice->recalculateStatus();
        });

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Receipt recorded.');
    }

    public function destroyReceipt(Receipt $receipt)
    {
        $invoice = $receipt->invoice;

        if ($invoice) {
            DB::transaction(function () use ($invoice, $receipt) {
                $this->deleteReceiptLedgerEntries($invoice, $receipt);
                $receipt->delete();
                $invoice->recalculateStatus();
            });
        } else {
            $receipt->delete();
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

        $credit = null;

        DB::transaction(function () use ($invoice, $data, &$credit) {
            $credit = CreditNote::create([
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'number' => 'CN-TMP-' . Str::uuid(),
                'issued_at' => Carbon::today(),
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
            ]);

            $credit->update([
                'number' => $this->formatCreditNoteNumber($credit->id),
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

            $invoice->recalculateStatus();
        });

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
            $invoice->recalculateStatus();
        }

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Credit note deleted.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['order.agent', 'items.product', 'receipts', 'creditNotes']);

        $grossTotal    = $invoice->net_total + $invoice->vat_amount;
        $cashTotal     = $grossTotal - $invoice->withholding;
        $creditsTotal  = $invoice->creditNotes->sum('amount');
        $receiptsTotal = $invoice->receipts->sum('amount');
        $outstanding   = $cashTotal - $creditsTotal - $receiptsTotal;

        $pdf = Pdf::loadView('admin.finance.invoice_pdf', [
            'invoice'       => $invoice,
            'grossTotal'    => $grossTotal,
            'cashTotal'     => $cashTotal,
            'creditsTotal'  => $creditsTotal,
            'receiptsTotal' => $receiptsTotal,
            'outstanding'   => $outstanding,
        ]);

        return $pdf->download('invoice-' . $invoice->number . '.pdf');
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

    protected function formatInvoiceNumber(int $invoiceId): string
    {
        return 'INV-' . str_pad((string) $invoiceId, 6, '0', STR_PAD_LEFT);
    }

    protected function formatCreditNoteNumber(int $creditNoteId): string
    {
        return 'CN-' . str_pad((string) $creditNoteId, 6, '0', STR_PAD_LEFT);
    }

    protected function receiptLedgerDescription(Receipt $receipt, Invoice $invoice): string
    {
        return 'Receipt #' . $receipt->id . ' for ' . $invoice->number;
    }

    protected function deleteReceiptLedgerEntries(Invoice $invoice, Receipt $receipt): void
    {
        $exactDescription = $this->receiptLedgerDescription($receipt, $invoice);

        $exactEntries = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('description', $exactDescription)
            ->get();

        if ($exactEntries->isNotEmpty()) {
            LedgerEntry::whereKey($exactEntries->pluck('id'))->delete();

            return;
        }

        $legacyEntries = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('description', 'Receipt for ' . $invoice->number)
            ->where(function ($query) use ($receipt) {
                $query->where('debit', $receipt->amount)
                    ->orWhere('credit', $receipt->amount);
            })
            ->get();

        $isSafeLegacyPair = $legacyEntries->count() === 2
            && $legacyEntries->contains(fn (LedgerEntry $entry) => $entry->account === 'Bank' && (float) $entry->debit === (float) $receipt->amount)
            && $legacyEntries->contains(fn (LedgerEntry $entry) => $entry->account === 'Accounts Receivable' && (float) $entry->credit === (float) $receipt->amount);

        if ($isSafeLegacyPair) {
            LedgerEntry::whereKey($legacyEntries->pluck('id'))->delete();
        }
    }
}
