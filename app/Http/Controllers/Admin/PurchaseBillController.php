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
use App\Services\Accounting\AccountingService;
use App\Services\Accounting\InventoryAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseBillController extends Controller
{
    public function __construct(
        protected AccountingService $accounting,
        protected InventoryAccountingService $inventoryAccounting
    ) {
    }

    public function index()
    {
        $bills = PurchaseBill::with('supplier', 'payments')->latest('bill_date')->paginate(15);
        return view('admin.bills.index', compact('bills'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with('taxClass')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.bills.create', compact('suppliers', 'products', 'warehouses'));
    }

    public function edit(PurchaseBill $bill)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with('taxClass')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        $bill->load('items');

        return view('admin.bills.edit', compact('bill', 'suppliers', 'products', 'warehouses'));
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
            'items.*.vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        [$items, $netTotal, $vatTotal] = $this->normalizeBillItems($data['items']);

        $bill = null;

        DB::transaction(function () use ($data, $items, $netTotal, $vatTotal, &$bill) {
            $bill = PurchaseBill::create([
                'supplier_id' => $data['supplier_id'],
                'number' => 'PB-TMP-' . Str::uuid(),
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'net_total' => $netTotal,
                'vat_amount' => $vatTotal,
                'status' => 'open',
            ]);

            $bill->update([
                'number' => $this->formatPurchaseBillNumber($bill->id),
            ]);

            foreach ($items as $item) {
                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'vat_rate' => $item['vat_rate'],
                    'line_total' => $item['line_total'],
                    'vat_amount' => $item['vat_amount'],
                ]);
            }

            $this->refreshLedgerEntries($bill, $netTotal, $vatTotal);
        });

        return redirect()
            ->route('admin.bills.index')
            ->with('status', 'Purchase bill recorded. Inventory will be updated from the goods receipt process.');
    }

    public function update(Request $request, PurchaseBill $bill)
    {
        if ($bill->payments()->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->with('error', 'Bills with payments cannot be edited. Please clear payments first.');
        }

        // If this bill has already posted material stock, we block edits to
        // avoid drifting away from inventory reality. Adjustments should be
        // handled via the Inventory adjustments module instead.
        if (StockEntry::where('purchase_bill_id', $bill->id)->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->with('error', 'This bill has already posted material stock. Please use inventory adjustments instead of editing the bill.');
        }

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
            'items.*.vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        [$items, $netTotal, $vatTotal] = $this->normalizeBillItems($data['items']);

        DB::transaction(function () use ($data, $bill, $items, $netTotal, $vatTotal) {
            // Update bill header
            $bill->update([
                'supplier_id' => $data['supplier_id'],
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'net_total' => $netTotal,
                'vat_amount' => $vatTotal,
            ]);

            // Remove existing items
            $bill->items()->delete();

            // Recreate items
            foreach ($items as $item) {
                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'vat_rate' => $item['vat_rate'],
                    'line_total' => $item['line_total'],
                    'vat_amount' => $item['vat_amount'],
                ]);
            }

            $this->refreshLedgerEntries($bill, $netTotal, $vatTotal);
        });

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill updated.');
    }

    public function storePayment(Request $request, PurchaseBill $bill)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|max:100',
        ]);

        $amount = isset($data['amount']) ? (float) $data['amount'] : (float) $bill->outstanding;

        if ($amount <= 0) {
            return redirect()
                ->route('admin.bills.index')
                ->with('error', 'This bill is already fully paid.');
        }

        if ($amount > $bill->outstanding + 0.00001) {
            return redirect()
                ->route('admin.bills.index')
                ->withErrors(['amount' => 'Payment amount exceeds the remaining amount due for this bill.']);
        }

        $this->recordBillPayment(
            $bill,
            $amount,
            Carbon::parse($data['paid_at'] ?? Carbon::today()),
            $data['method'] ?? null
        );

        return redirect()->route('admin.bills.index')->with('status', 'Bill payment recorded.');
    }

    public function storeBatchPayment(Request $request)
    {
        $data = $request->validate([
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'integer|exists:purchase_bills,id',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|max:100',
            'batch_reference' => 'nullable|string|max:100',
        ]);

        $paidAt = Carbon::parse($data['paid_at'] ?? Carbon::today());
        $batchReference = trim((string) ($data['batch_reference'] ?? ''));
        if ($batchReference === '') {
            $batchReference = 'PAYB-' . now()->format('Ymd-His');
        }

        $bills = PurchaseBill::with('payments')
            ->whereIn('id', $data['bill_ids'])
            ->get();

        $paidCount = 0;
        $totalPaid = 0.0;

        DB::transaction(function () use ($bills, $paidAt, $data, $batchReference, &$paidCount, &$totalPaid) {
            foreach ($bills as $bill) {
                $amount = (float) $bill->outstanding;
                if ($amount <= 0) {
                    continue;
                }

                $this->recordBillPayment(
                    $bill,
                    $amount,
                    $paidAt,
                    $data['method'] ?? null,
                    $batchReference
                );

                $paidCount++;
                $totalPaid += $amount;
            }
        });

        if ($paidCount === 0) {
            return redirect()
                ->route('admin.bills.index')
                ->with('error', 'No outstanding balance remained on the selected bills.');
        }

        return redirect()
            ->route('admin.bills.index')
            ->with('status', "Batch {$batchReference} recorded {$paidCount} bill payment(s) totalling BDT " . number_format($totalPaid, 2) . '.');
    }

    protected function recordBillPayment(
        PurchaseBill $bill,
        float $amount,
        Carbon $paidAt,
        ?string $method = null,
        ?string $batchReference = null
    ): BillPayment {
        $amount = round($amount, 2);

        $payment = BillPayment::create([
            'purchase_bill_id' => $bill->id,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'method' => $method,
            'batch_reference' => $batchReference,
        ]);

        $description = $batchReference
            ? "Batch payment {$batchReference} for {$bill->number}"
            : 'Payment for ' . $bill->number;

        $this->accounting->post(
            'bill_payment',
            $paidAt,
            [
                ['account_key' => 'accounts_payable', 'debit' => $amount, 'credit' => 0],
                ['account_key' => 'bank_default', 'debit' => 0, 'credit' => $amount],
            ],
            [
                'description' => $description,
                'source_type' => BillPayment::class,
                'source_id' => $payment->id,
            ]
        );

        $bill->refresh()->recalculateStatus();

        return $payment;
    }

    public function destroy(PurchaseBill $bill)
    {
        if ($bill->goodsReceipts()->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->withErrors([
                    'bill' => 'This bill is linked to one or more goods receipts and cannot be deleted.',
                ]);
        }

        // If this bill has posted stock entries, we block deletion to avoid
        // silently removing financial data while stock remains.
        if (StockEntry::where('purchase_bill_id', $bill->id)->exists()) {
            return redirect()
                ->route('admin.bills.index')
                ->withErrors([
                    'bill' => 'This bill has posted material stock. Use inventory adjustments instead of deleting the bill.',
                ]);
        }

        if ($bill->payments()->exists() || $this->hasFinancialPostings($bill)) {
            return redirect()
                ->route('admin.bills.index')
                ->withErrors([
                    'bill' => 'Posted purchase bills cannot be deleted. Preserve the AP audit trail and issue a controlled supplier adjustment instead.',
                ]);
        }

        DB::transaction(function () use ($bill) {
            $bill->items()->delete();
            $bill->delete();
        });

        return redirect()->route('admin.bills.index')->with('status', 'Purchase bill deleted.');
    }

    protected function hasFinancialPostings(PurchaseBill $bill): bool
    {
        return LedgerEntry::whereIn('description', [
            'Purchase bill ' . $bill->number,
            'Payment for ' . $bill->number,
        ])->exists();
    }

    protected function normalizeBillItems(array $items): array
    {
        $productVatRates = Product::with('taxClass')
            ->whereIn('id', collect($items)->pluck('product_id')->filter()->all())
            ->get()
            ->mapWithKeys(function (Product $product) {
                return [$product->id => (float) (optional($product->taxClass)->rate ?? 0)];
            });

        $normalized = [];
        $netTotal = 0.0;
        $vatTotal = 0.0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = round((float) $item['unit_price'], 2);
            $lineTotal = round($quantity * $unitPrice, 2);
            $vatRate = isset($item['vat_rate']) && $item['vat_rate'] !== ''
                ? round((float) $item['vat_rate'], 2)
                : round((float) ($productVatRates[$item['product_id'] ?? null] ?? 0), 2);
            $vatAmount = round($lineTotal * ($vatRate / 100), 2);

            $normalized[] = [
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'vat_rate' => $vatRate,
                'line_total' => $lineTotal,
                'vat_amount' => $vatAmount,
            ];

            $netTotal += $lineTotal;
            $vatTotal += $vatAmount;
        }

        return [$normalized, round($netTotal, 2), round($vatTotal, 2)];
    }

    protected function refreshLedgerEntries(PurchaseBill $bill, float $netTotal, float $vatTotal): void
    {
        $description = 'Purchase bill ' . $bill->number;

        $this->accounting->deleteByDescription($description);

        $debitLines = $this->inventoryAccounting->refreshPurchaseBillLedger($bill, $netTotal, $vatTotal);

        $lines = $debitLines;

        if ($vatTotal > 0) {
            $lines[] = ['account_key' => 'input_vat', 'debit' => $vatTotal, 'credit' => 0];
        }

        $lines[] = ['account_key' => 'accounts_payable', 'debit' => 0, 'credit' => $netTotal + $vatTotal];

        $this->accounting->post(
            'purchase_bill',
            Carbon::parse($bill->bill_date),
            $lines,
            [
                'description' => $description,
                'source_type' => PurchaseBill::class,
                'source_id' => $bill->id,
            ]
        );
    }

    protected function formatPurchaseBillNumber(int $purchaseBillId): string
    {
        return 'PB-' . str_pad((string) $purchaseBillId, 6, '0', STR_PAD_LEFT);
    }
}
