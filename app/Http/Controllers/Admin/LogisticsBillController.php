<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\LogisticsBill;
use App\Models\LogisticsBillLine;
use App\Models\LogisticsBillPayment;
use App\Models\TransportCarrier;
use App\Services\Accounting\AccountingService;
use App\Services\Accounting\LogisticsBillPostingService;
use App\Support\LogisticsFreightEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LogisticsBillController extends Controller
{
    public function __construct(
        protected LogisticsBillPostingService $posting,
        protected AccountingService $accounting,
    ) {
    }

    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $allowedStatuses = ['draft', 'open', 'part_paid', 'paid'];

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;
        $carrierId = $request->query('transport_carrier_id');

        $billsQuery = LogisticsBill::query()
            ->with(['transportCarrier', 'deliveryRoute', 'payments'])
            ->withCount('lines')
            ->orderByDesc('bill_date');

        if (in_array($statusFilter, $allowedStatuses, true)) {
            $billsQuery->where('status', $statusFilter);
        } else {
            $statusFilter = null;
        }

        if ($from) {
            $billsQuery->whereDate('bill_date', '>=', $from);
        }

        if ($to) {
            $billsQuery->whereDate('bill_date', '<=', $to);
        }

        if ($carrierId) {
            $billsQuery->where('transport_carrier_id', $carrierId);
        }

        $bills = $billsQuery->paginate(20)->withQueryString();

        $unpaidBills = LogisticsBill::query()
            ->whereIn('status', ['open', 'part_paid'])
            ->with('payments')
            ->get();

        $stats = [
            'total' => LogisticsBill::count(),
            'open' => LogisticsBill::where('status', 'open')->count(),
            'part_paid' => LogisticsBill::where('status', 'part_paid')->count(),
            'paid' => LogisticsBill::where('status', 'paid')->count(),
            'unpaid_value' => (float) $unpaidBills->sum(fn (LogisticsBill $bill) => $bill->outstanding),
            'period_billed' => (float) LogisticsBill::query()
                ->whereBetween('bill_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->selectRaw('SUM(net_total + vat_amount) as gross')
                ->value('gross'),
        ];

        $carriers = TransportCarrier::orderBy('name')->get();

        return view('admin.logistics.bills.index', compact(
            'bills',
            'statusFilter',
            'stats',
            'carriers',
            'from',
            'to',
            'carrierId',
        ));
    }

    public function create(Request $request)
    {
        $estimator = app(LogisticsFreightEstimator::class);
        $suggestion = null;

        if ($request->filled('route_id') || $request->filled('crates')) {
            $suggestion = $estimator->suggest(
                $request->integer('route_id') ?: null,
                $request->integer('crates'),
                $request->integer('carrier_id') ?: $request->integer('transport_carrier_id') ?: null,
            );
        }

        return view('admin.logistics.bills.create', [
            'bill' => new LogisticsBill([
                'bill_date' => $request->query('trip_date', now()->toDateString()),
                'service_type' => LogisticsBill::SERVICE_EXTERNAL_FREIGHT,
                'status' => 'open',
                'transport_carrier_id' => $suggestion['carrier_id'] ?? $request->query('carrier_id') ?? $request->query('transport_carrier_id'),
                'delivery_route_id' => $suggestion['route_id'] ?? $request->query('route_id'),
                'trip_date' => $request->query('trip_date'),
            ]),
            'carriers' => TransportCarrier::active()->orderBy('name')->get(),
            'routes' => DeliveryRoute::orderBy('name')->get(),
            'serviceTypes' => LogisticsBill::serviceTypes(),
            'suggestion' => $suggestion,
            'formState' => $this->buildFormState(
                TransportCarrier::active()->orderBy('name')->get(),
                DeliveryRoute::orderBy('name')->get(),
                $suggestion,
            ),
        ]);
    }

    protected function buildFormState($carriers, $routes, ?array $suggestion = null): array
    {
        $initialRows = old('lines');
        if ($initialRows === null && $suggestion) {
            $initialRows = [[
                'description' => $suggestion['description'] ?? '',
                'amount' => $suggestion['amount'] ?? '',
            ]];
        }
        if (empty($initialRows)) {
            $initialRows = [['description' => '', 'amount' => '']];
        }

        return [
            'carriers' => $carriers->map(fn ($carrier) => [
                'id' => $carrier->id,
                'name' => $carrier->name,
            ])->values()->all(),
            'routes' => $routes->map(fn ($route) => [
                'id' => $route->id,
                'name' => $route->name,
            ])->values()->all(),
            'serviceTypes' => LogisticsBill::serviceTypes(),
            'selectedCarrierId' => (string) old(
                'transport_carrier_id',
                $suggestion['carrier_id'] ?? request('carrier_id') ?? request('transport_carrier_id') ?? ''
            ),
            'billDate' => old('bill_date', request('trip_date', now()->toDateString())),
            'dueDate' => old('due_date', ''),
            'vendorNumber' => old('number', ''),
            'serviceType' => old('service_type', LogisticsBill::SERVICE_EXTERNAL_FREIGHT),
            'routeId' => (string) old(
                'delivery_route_id',
                $suggestion['route_id'] ?? request('route_id') ?? ''
            ),
            'tripDate' => old('trip_date', request('trip_date', '')),
            'notes' => old('notes', ''),
            'vatAmount' => old('vat_amount', 0),
            'initialRows' => collect($initialRows)->map(fn ($row) => [
                'description' => $row['description'] ?? '',
                'amount' => $row['amount'] ?? '',
            ])->values()->all(),
        ];
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $lines = collect($data['lines'])->filter(fn ($line) => (float) ($line['amount'] ?? 0) > 0)->values();

        if ($lines->isEmpty()) {
            return back()->withInput()->withErrors(['lines' => 'Add at least one line with an amount greater than zero.']);
        }

        $bill = DB::transaction(function () use ($data, $lines) {
            unset($data['lines']);

            $data['net_total'] = $lines->sum(fn ($line) => (float) $line['amount']);

            $bill = LogisticsBill::create($data);

            foreach ($lines as $line) {
                LogisticsBillLine::create([
                    'logistics_bill_id' => $bill->id,
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                ]);
            }

            $bill->refresh();
            $this->posting->sync($bill);

            return $bill;
        });

        return redirect()
            ->route('admin.logistics-bills.show', $bill)
            ->with('status', 'Logistics bill recorded.');
    }

    public function show(LogisticsBill $logisticsBill)
    {
        $logisticsBill->load(['transportCarrier', 'deliveryRoute', 'lines', 'payments']);

        return view('admin.logistics.bills.show', [
            'bill' => $logisticsBill,
        ]);
    }

    public function storePayment(Request $request, LogisticsBill $logisticsBill)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|max:100',
        ]);

        $amount = round((float) ($data['amount'] ?? $logisticsBill->outstanding), 2);

        if ($amount <= 0 || $amount > $logisticsBill->outstanding + 0.01) {
            return back()->withErrors(['amount' => 'Enter a valid payment amount up to the outstanding balance.']);
        }

        DB::transaction(function () use ($logisticsBill, $data, $amount) {
            $paidAt = Carbon::parse($data['paid_at'] ?? Carbon::today());

            $payment = LogisticsBillPayment::create([
                'logistics_bill_id' => $logisticsBill->id,
                'amount' => $amount,
                'paid_at' => $paidAt,
                'method' => $data['method'] ?? null,
            ]);

            $this->accounting->post(
                'logistics_bill_payment',
                $paidAt,
                [
                    ['account_key' => 'trade_creditors', 'debit' => $amount, 'credit' => 0],
                    ['account_key' => 'bank_default', 'debit' => 0, 'credit' => $amount],
                ],
                [
                    'description' => 'Payment for ' . $logisticsBill->document_number,
                    'source_type' => LogisticsBillPayment::class,
                    'source_id' => $payment->id,
                ]
            );

            $logisticsBill->refresh()->recalculateStatus();
        });

        return redirect()
            ->route('admin.logistics-bills.show', $logisticsBill)
            ->with('status', 'Payment recorded.');
    }

    public function destroy(LogisticsBill $logisticsBill)
    {
        DB::transaction(function () use ($logisticsBill) {
            foreach ($logisticsBill->payments as $payment) {
                $this->accounting->deleteByJournalTypeAndSource(
                    'logistics_bill_payment',
                    LogisticsBillPayment::class,
                    $payment->id
                );
            }

            $logisticsBill->payments()->delete();
            $this->accounting->deleteByJournalTypeAndSource('logistics_bill', LogisticsBill::class, $logisticsBill->id);
            $logisticsBill->lines()->delete();
            $logisticsBill->delete();
        });

        return redirect()
            ->route('admin.logistics-bills.index')
            ->with('status', 'Logistics bill deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'transport_carrier_id' => 'required|exists:transport_carriers,id',
            'number' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'service_type' => 'required|in:' . implode(',', array_keys(LogisticsBill::serviceTypes())),
            'delivery_route_id' => 'nullable|exists:delivery_routes,id',
            'trip_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'vat_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,open',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.amount' => 'required|numeric|min:0',
        ]);
    }
}
