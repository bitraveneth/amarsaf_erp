<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\SalesTarget;
use App\Models\VisitPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->resolveMonth($request);

        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        $companyTarget = $this->companyTargetFor($periodStart, $periodEnd);

        $targets = SalesTarget::with(['employee', 'agent'])
            ->whereDate('period_start', '<=', $periodEnd->toDateString())
            ->whereDate('period_end', '>=', $periodStart->toDateString())
            ->orderByRaw("CASE kind WHEN 'company' THEN 0 WHEN 'agent' THEN 1 ELSE 2 END")
            ->orderByDesc('target_value')
            ->get();

        $rows = $targets->map(function (SalesTarget $target) {
            $achieved = $this->achievedValue($target);

            return [
                'target' => $target,
                'achieved' => $achieved,
                'remaining' => max((float) $target->target_value - $achieved, 0),
                'progress' => (float) $target->target_value > 0
                    ? min(100, round(($achieved / (float) $target->target_value) * 100, 2))
                    : 0.0,
            ];
        });

        $allocations = $rows->filter(fn (array $row) => ! $row['target']->isCompany());
        $allocatedTotal = (float) $allocations->sum(fn (array $row) => (float) $row['target']->target_value);
        $companyValue = (float) ($companyTarget?->target_value ?? 0);
        $unassigned = $companyValue > 0 ? ($companyValue - $allocatedTotal) : 0.0;
        $companyAchieved = $companyTarget
            ? $this->achievedValue($companyTarget)
            : $this->companyAchieved($periodStart, $periodEnd);

        $summary = [
            'company' => $companyValue,
            'allocated' => $allocatedTotal,
            'unassigned' => $unassigned,
            'achieved' => $companyAchieved,
            'progress' => $companyValue > 0
                ? min(100, round(($companyAchieved / $companyValue) * 100, 2))
                : 0.0,
        ];

        return view('admin.sales-targets.index', compact('rows', 'month', 'summary', 'companyTarget'));
    }

    public function create(Request $request)
    {
        $month = $this->resolveMonth($request);
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        $companyTarget = $this->companyTargetFor($periodStart, $periodEnd);

        $existing = SalesTarget::query()
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->get();

        $agentAmounts = $existing->where('kind', SalesTarget::KIND_AGENT)->pluck('target_value', 'agent_id');
        $employeeAmounts = $existing->where('kind', SalesTarget::KIND_EMPLOYEE)->pluck('target_value', 'employee_id');

        $agents = Agent::query()
            ->where(function ($query) use ($agentAmounts) {
                $query->where('is_active', true);
                $ids = $agentAmounts->keys()->filter()->all();
                if ($ids !== []) {
                    $query->orWhereIn('id', $ids);
                }
            })
            ->orderBy('name')
            ->get();

        $employees = $this->salesEmployees($employeeAmounts->keys());

        return view('admin.sales-targets.create', compact(
            'month',
            'periodStart',
            'periodEnd',
            'companyTarget',
            'agents',
            'employees',
            'agentAmounts',
            'employeeAmounts'
        ));
    }

    public function store(Request $request)
    {
        if ($request->has('company_target')) {
            return $this->storeDistribution($request);
        }

        $data = $this->validated($request);
        $data['kind'] = filled($data['agent_id'] ?? null)
            ? SalesTarget::KIND_AGENT
            : SalesTarget::KIND_EMPLOYEE;

        SalesTarget::create($data);

        return redirect()->route('admin.sales-targets.index')->with('status', 'Sales target saved.');
    }

    public function edit(SalesTarget $salesTarget)
    {
        $employees = Employee::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();

        return view('admin.sales-targets.edit', compact('salesTarget', 'employees', 'agents'));
    }

    public function update(Request $request, SalesTarget $salesTarget)
    {
        $data = $this->validated($request, $salesTarget);
        $data['kind'] = filled($data['agent_id'] ?? null)
            ? SalesTarget::KIND_AGENT
            : SalesTarget::KIND_EMPLOYEE;

        $salesTarget->update($data);

        return redirect()->route('admin.sales-targets.index')->with('status', 'Sales target updated.');
    }

    protected function storeDistribution(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|date_format:Y-m',
            'company_target' => 'required|numeric|min:0',
            'agent_targets' => 'array',
            'agent_targets.*' => 'nullable|numeric|min:0',
            'employee_targets' => 'array',
            'employee_targets.*' => 'nullable|numeric|min:0',
        ]);

        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $periodStart = $month->copy()->startOfMonth()->toDateString();
        $periodEnd = $month->copy()->endOfMonth()->toDateString();
        $companyValue = round((float) $data['company_target'], 2);

        $company = $this->companyTargetFor($month->copy()->startOfMonth(), $month->copy()->endOfMonth());
        if ($companyValue > 0) {
            if ($company) {
                $company->update(['target_value' => $companyValue, 'kind' => SalesTarget::KIND_COMPANY]);
            } else {
                SalesTarget::create([
                    'kind' => SalesTarget::KIND_COMPANY,
                    'employee_id' => null,
                    'agent_id' => null,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'target_value' => $companyValue,
                ]);
            }
        } elseif ($company) {
            $company->delete();
        }

        $this->syncOwnerTargets(
            SalesTarget::KIND_AGENT,
            'agent_id',
            $data['agent_targets'] ?? [],
            $periodStart,
            $periodEnd
        );
        $this->syncOwnerTargets(
            SalesTarget::KIND_EMPLOYEE,
            'employee_id',
            $data['employee_targets'] ?? [],
            $periodStart,
            $periodEnd
        );

        return redirect()
            ->route('admin.sales-targets.index', ['month' => $data['month']])
            ->with('status', 'Company target and allocations saved.');
    }

    protected function syncOwnerTargets(string $kind, string $fk, array $amounts, string $periodStart, string $periodEnd): void
    {
        $existing = SalesTarget::query()
            ->where('kind', $kind)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->get()
            ->keyBy($fk);

        foreach ($amounts as $ownerId => $raw) {
            $ownerId = (int) $ownerId;
            if ($ownerId < 1) {
                continue;
            }

            $value = round((float) $raw, 2);
            $row = $existing->get($ownerId);

            if ($value <= 0) {
                $row?->delete();
                continue;
            }

            if ($row) {
                $row->update(['target_value' => $value]);
                continue;
            }

            SalesTarget::create([
                'kind' => $kind,
                'agent_id' => $fk === 'agent_id' ? $ownerId : null,
                'employee_id' => $fk === 'employee_id' ? $ownerId : null,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'target_value' => $value,
            ]);
        }
    }

    protected function validated(Request $request, ?SalesTarget $salesTarget = null): array
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id',
            'agent_id' => 'nullable|exists:agents,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'target_value' => 'required|numeric|min:0.01',
        ]);

        $validator->after(function ($validator) use ($request, $salesTarget) {
            $hasEmployee = filled($request->input('employee_id'));
            $hasAgent = filled($request->input('agent_id'));

            if ($hasEmployee === $hasAgent) {
                $validator->errors()->add('employee_id', 'Select exactly one target owner: either an employee or an agent.');
            }

            $query = SalesTarget::query()
                ->whereDate('period_start', '<=', $request->input('period_end'))
                ->whereDate('period_end', '>=', $request->input('period_start'))
                ->where('kind', '!=', SalesTarget::KIND_COMPANY);

            if ($hasEmployee) {
                $query->where('employee_id', $request->input('employee_id'))->whereNull('agent_id');
            }

            if ($hasAgent) {
                $query->where('agent_id', $request->input('agent_id'))->whereNull('employee_id');
            }

            if ($salesTarget) {
                $query->whereKeyNot($salesTarget->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('target_value', 'A target already exists for this owner in an overlapping period.');
            }
        });

        return $validator->validate();
    }

    protected function resolveMonth(Request $request): Carbon
    {
        return $request->filled('month')
            ? Carbon::parse($request->query('month').'-01')->startOfMonth()
            : Carbon::now()->startOfMonth();
    }

    protected function companyTargetFor(Carbon $periodStart, Carbon $periodEnd): ?SalesTarget
    {
        return SalesTarget::query()
            ->where('kind', SalesTarget::KIND_COMPANY)
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->first();
    }

    protected function salesEmployees($keepIds)
    {
        return Employee::query()
            ->where(function ($query) use ($keepIds) {
                $query->where('job_position', 'like', '%sales%')
                    ->orWhere('department', 'like', '%sales%');
                $ids = collect($keepIds)->filter()->all();
                if ($ids !== []) {
                    $query->orWhereIn('id', $ids);
                }
            })
            ->orderBy('name')
            ->get();
    }

    protected function achievedValue(SalesTarget $target): float
    {
        $from = $target->period_start->toDateString();
        $to = $target->period_end->toDateString();

        if ($target->isCompany()) {
            return $this->companyAchieved($target->period_start, $target->period_end);
        }

        $query = Invoice::query()
            ->with('creditNotes')
            ->whereDate('issued_at', '>=', $from)
            ->whereDate('issued_at', '<=', $to)
            ->whereHas('order.agent');

        if ($target->agent_id) {
            return $this->sumNetSalesAfterCredits($query
                ->whereHas('order', function ($orderQuery) use ($target) {
                    $orderQuery->where('agent_id', $target->agent_id);
                })
                ->get(), $from, $to);
        }

        $employee = $target->employee;
        if (! $employee) {
            return 0.0;
        }

        if (Schema::hasTable('visit_plans')) {
            $plans = VisitPlan::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
                ->get(['agent_id', 'status']);

            if ($plans->isNotEmpty()) {
                $agentIds = $plans
                    ->whereIn('status', ['visited', 'completed'])
                    ->pluck('agent_id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($agentIds->isNotEmpty()) {
                    return $this->sumNetSalesAfterCredits($query
                        ->whereHas('order', function ($orderQuery) use ($agentIds) {
                            $orderQuery->whereIn('agent_id', $agentIds);
                        })
                        ->get(), $from, $to);
                }

                return 0.0;
            }
        }

        if ($employee->work_zone) {
            return $this->sumNetSalesAfterCredits($query
                ->whereHas('order.agent', function ($agentQuery) use ($employee) {
                    $agentQuery->where('zone', $employee->work_zone);
                })
                ->get(), $from, $to);
        }

        return 0.0;
    }

    protected function companyAchieved(Carbon $from, Carbon $to): float
    {
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $invoices = Invoice::query()
            ->with('creditNotes')
            ->whereDate('issued_at', '>=', $fromDate)
            ->whereDate('issued_at', '<=', $toDate)
            ->whereHas('order.agent')
            ->get();

        return $this->sumNetSalesAfterCredits($invoices, $fromDate, $toDate);
    }

    protected function sumNetSalesAfterCredits($invoices, string $from, string $to): float
    {
        return round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);
    }
}
