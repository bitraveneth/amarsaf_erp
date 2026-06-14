<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FleetExpense;
use App\Models\FleetRecurringCharge;
use App\Models\Vehicle;
use App\Services\Accounting\FleetExpensePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FleetRecurringChargeController extends Controller
{
    public function __construct(protected FleetExpensePostingService $posting)
    {
    }

    public function index()
    {
        $charges = FleetRecurringCharge::with('vehicle')->orderBy('vehicle_id')->paginate(20);

        return view('admin.logistics.recurring.index', compact('charges'));
    }

    public function create()
    {
        return view('admin.logistics.recurring.create', [
            'charge' => new FleetRecurringCharge([
                'expense_type' => FleetExpense::TYPE_RENT,
                'day_of_month' => 1,
                'payment_type' => 'bank',
                'payment_account_key' => 'bank_default',
                'is_active' => true,
            ]),
            'vehicles' => Vehicle::orderBy('name')->get(),
            'types' => FleetExpense::types(),
        ]);
    }

    public function store(Request $request)
    {
        FleetRecurringCharge::create($this->validated($request));

        return redirect()->route('admin.fleet-recurring.index')->with('status', 'Recurring charge saved.');
    }

    public function destroy(FleetRecurringCharge $fleetRecurring)
    {
        $fleetRecurring->delete();

        return redirect()->route('admin.fleet-recurring.index')->with('status', 'Recurring charge removed.');
    }

    public function generate(Request $request)
    {
        $forMonth = Carbon::parse($request->input('for_month', now()->format('Y-m-01')))->startOfMonth();
        $generated = 0;

        DB::transaction(function () use ($forMonth, &$generated) {
            $charges = FleetRecurringCharge::with('vehicle')->where('is_active', true)->get();

            foreach ($charges as $charge) {
                if ($charge->last_generated_for && Carbon::parse($charge->last_generated_for)->isSameMonth($forMonth)) {
                    continue;
                }

                $expenseDate = $forMonth->copy()->day(min($charge->day_of_month, $forMonth->daysInMonth));

                $expense = FleetExpense::create([
                    'vehicle_id' => $charge->vehicle_id,
                    'expense_type' => $charge->expense_type,
                    'expense_date' => $expenseDate,
                    'amount' => $charge->amount,
                    'description' => $charge->description ?: ('Recurring ' . ($charge->vehicle?->name ?? 'vehicle') . ' charge'),
                    'reference' => 'REC-' . $forMonth->format('Ym'),
                    'status' => FleetExpense::STATUS_RECORDED,
                    'payment_type' => $charge->payment_type,
                    'payment_account_key' => $charge->payment_account_key ?: 'bank_default',
                    'analytic_label' => 'vehicle:' . $charge->vehicle_id,
                ]);

                $this->posting->sync($expense);

                $charge->update(['last_generated_for' => $forMonth->toDateString()]);
                $generated++;
            }
        });

        return redirect()
            ->route('admin.fleet-recurring.index')
            ->with('status', $generated > 0
                ? "Generated {$generated} fleet expense(s) for " . $forMonth->format('F Y') . '.'
                : 'No new recurring charges to generate for that month.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'expense_type' => 'required|in:' . implode(',', array_keys(FleetExpense::types())),
            'amount' => 'required|numeric|min:0.01',
            'day_of_month' => 'required|integer|min:1|max:28',
            'description' => 'nullable|string|max:255',
            'payment_type' => 'required|in:bank,cash,payable',
            'payment_account_key' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['payment_account_key'] = $data['payment_account_key'] ?: 'bank_default';

        return $data;
    }
}
