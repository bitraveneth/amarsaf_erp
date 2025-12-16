<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PurchaseBillController extends Controller
{
    public function index()
    {
        $bills = PurchaseBill::with('supplier', 'payments')->latest('bill_date')->paginate(15);
        return view('admin.bills.index', compact('bills'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('admin.bills.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $netTotal = 0;
        foreach ($data['items'] as $item) {
            $netTotal += $item['quantity'] * $item['unit_price'];
        }

        $bill = PurchaseBill::create([
            'supplier_id' => $data['supplier_id'],
            'number' => 'PB-' . str_pad((string)(PurchaseBill::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'bill_date' => $data['bill_date'],
            'due_date' => $data['due_date'] ?? null,
            'net_total' => $netTotal,
            'vat_amount' => 0,
            'status' => 'open',
        ]);

        foreach ($data['items'] as $item) {
            PurchaseBillItem::create([
                'purchase_bill_id' => $bill->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        LedgerEntry::create([
            'account' => 'Purchases',
            'description' => 'Purchase bill ' . $bill->number,
            'debit' => $netTotal,
            'credit' => 0,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        LedgerEntry::create([
            'account' => 'Accounts Payable',
            'description' => 'Purchase bill ' . $bill->number,
            'debit' => 0,
            'credit' => $netTotal,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill recorded.');
    }

    public function storePayment(Request $request, PurchaseBill $bill)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string',
        ]);

        $paidAt = $data['paid_at'] ?? Carbon::today();

        $payment = BillPayment::create([
            'purchase_bill_id' => $bill->id,
            'amount' => $data['amount'],
            'paid_at' => $paidAt,
            'method' => $data['method'] ?? null,
        ]);

        LedgerEntry::create([
            'account' => 'Accounts Payable',
            'description' => 'Payment for ' . $bill->number,
            'debit' => $payment->amount,
            'credit' => 0,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => 'Payment for ' . $bill->number,
            'debit' => 0,
            'credit' => $payment->amount,
        ]);

        $paidTotal = $bill->payments()->sum('amount');
        if ($paidTotal >= $bill->net_total + $bill->vat_amount) {
            $bill->update(['status' => 'paid']);
        } elseif ($paidTotal > 0) {
            $bill->update(['status' => 'part_paid']);
        }

        return redirect()->route('admin.bills.index')->with('status', 'Bill payment recorded.');
    }
}

