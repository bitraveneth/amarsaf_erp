<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Supplier;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.bills.create', compact('suppliers', 'products', 'warehouses'));
    }

    public function edit(PurchaseBill $bill)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $bill->load('items');

        return view('admin.bills.edit', compact('bill', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
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

        $bill = null;

        DB::transaction(function () use ($data, $netTotal, &$bill) {
            $bill = PurchaseBill::create([
                'supplier_id' => $data['supplier_id'],
                'number' => 'PB-TMP-' . Str::uuid(),
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'net_total' => $netTotal,
                'vat_amount' => 0,
                'status' => 'open',
            ]);

            $bill->update([
                'number' => $this->formatPurchaseBillNumber($bill->id),
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

            // Post raw material stock into default warehouse so that
            // Material stock & BOM consumption can work end-to-end.
            $this->postMaterialStockForBill($bill, $data['items'], $data['warehouse_id'] ?? null);
        });

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill recorded.');
    }

    public function update(Request $request, PurchaseBill $bill)
    {
        if ($bill->payments()->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->with('status', 'Bills with payments cannot be edited. Please clear payments first.');
        }

        // If this bill has already posted material stock, we block edits to
        // avoid drifting away from inventory reality. Adjustments should be
        // handled via the Inventory adjustments module instead.
        if (StockEntry::where('purchase_bill_id', $bill->id)->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->with('status', 'This bill has already posted material stock. Please use inventory adjustments instead of editing the bill.');
        }

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

        DB::transaction(function () use ($data, $bill, $netTotal) {
            // Update bill header
            $bill->update([
                'supplier_id' => $data['supplier_id'],
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'net_total' => $netTotal,
            ]);

            // Remove existing items
            $bill->items()->delete();

            // Recreate items
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

            // Refresh related ledger entries for this bill
            $description = 'Purchase bill ' . $bill->number;

            LedgerEntry::where('description', $description)->delete();

            LedgerEntry::create([
                'account' => 'Purchases',
                'description' => $description,
                'debit' => $netTotal,
                'credit' => 0,
                'order_id' => null,
                'invoice_id' => null,
            ]);

            LedgerEntry::create([
                'account' => 'Accounts Payable',
                'description' => $description,
                'debit' => 0,
                'credit' => $netTotal,
                'order_id' => null,
                'invoice_id' => null,
            ]);
        });

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill updated.');
    }

    public function storePayment(Request $request, PurchaseBill $bill)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string',
        ]);

        $paidAt = $data['paid_at'] ?? Carbon::today();

        // যদি amount না আসে (index page থেকে), তাহলে পুরো বাকি টাকা pay করি
        $alreadyPaid = $bill->payments()->sum('amount');
        $totalDue    = ($bill->net_total + $bill->vat_amount) - $alreadyPaid;

        if (empty($data['amount'])) {
            $data['amount'] = $totalDue;
        }

        // Already paid or invalid amount
        if ($data['amount'] <= 0) {
            return redirect()
                ->route('admin.bills.index')
                ->with('status', 'This bill is already fully paid.');
        }

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

    public function destroy(PurchaseBill $bill)
    {
        // If this bill has posted stock entries, we block deletion to avoid
        // silently removing financial data while stock remains.
        if (StockEntry::where('purchase_bill_id', $bill->id)->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->with('status', 'This bill has posted material stock. Use inventory adjustments instead of deleting the bill.');
        }

        DB::transaction(function () use ($bill) {
            // Delete ledger entries related to this bill (bill + payments)
            $billDescription = 'Purchase bill ' . $bill->number;
            $paymentDescription = 'Payment for ' . $bill->number;

            LedgerEntry::whereIn('description', [$billDescription, $paymentDescription])->delete();

            // Delete payments & items then the bill itself
            $bill->payments()->delete();
            $bill->items()->delete();
            $bill->delete();
        });

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill deleted.');
    }

    /**
     * Simple helper to create StockEntry rows for each material on a bill.
     * For now we post everything into the "Factory" warehouse (or the first
     * warehouse found) so that material stock and BOM consumption can work.
     */
    protected function postMaterialStockForBill(PurchaseBill $bill, array $items, ?int $explicitWarehouseId = null): void
    {
        // Determine target warehouse for these materials
        $warehouseId = $explicitWarehouseId
            ?? Warehouse::where('type', 'factory')->orderBy('id')->value('id')
            ?? Warehouse::orderBy('id')->value('id');

        if (! $warehouseId) {
            // No warehouse defined yet – nothing we can do.
            return;
        }

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = $item['quantity'] ?? 0;

            if (! $productId || $qty <= 0) {
                continue;
            }

            StockEntry::create([
                'purchase_bill_id' => $bill->id,
                'warehouse_id' => $warehouseId,
                'warehouse_location_id' => null,
                'product_id' => $productId,
                'batch_id' => null,
                'quantity' => $qty,
                'status' => 'available',
            ]);
        }
    }

    protected function formatPurchaseBillNumber(int $purchaseBillId): string
    {
        return 'PB-' . str_pad((string) $purchaseBillId, 6, '0', STR_PAD_LEFT);
    }
}
