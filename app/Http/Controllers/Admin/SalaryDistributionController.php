<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalaryDistributionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $query = SalaryDistribution::with('employee')
            ->whereBetween('period_start', [$from, $to]);

        if ($employeeId = $request->query('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        $rows = $query->orderBy('employee_id')->paginate(20)->withQueryString();

        $total = (clone $query)->sum('base_salary')
            + (clone $query)->sum('bonus')
            + (clone $query)->sum('ta_allowances')
            + (clone $query)->sum('da_allowances')
            + (clone $query)->sum('commission');

        $employees = Employee::orderBy('name')->get();

        return view('admin.finance.salary_distributions.index', compact('rows', 'month', 'total', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $distribution = new SalaryDistribution([
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);

        return view('admin.finance.salary_distributions.create', compact('employees', 'distribution'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $distribution = SalaryDistribution::create($data);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('salary-distributions', 'public');
            $distribution->update(['document_path' => $path]);
        }

        return redirect()->route('admin.salary-distributions.index')->with('status', 'Salary distribution recorded.');
    }

    public function edit(SalaryDistribution $salaryDistribution)
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.finance.salary_distributions.edit', [
            'distribution' => $salaryDistribution,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, SalaryDistribution $salaryDistribution)
    {
        $data = $this->validated($request);

        $salaryDistribution->update($data);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('salary-distributions', 'public');
            $salaryDistribution->update(['document_path' => $path]);
        }

        return redirect()->route('admin.salary-distributions.index')->with('status', 'Salary distribution updated.');
    }

    public function destroy(SalaryDistribution $salaryDistribution)
    {
        $salaryDistribution->delete();

        return redirect()->route('admin.salary-distributions.index')->with('status', 'Salary distribution deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'ta_allowances' => 'nullable|numeric|min:0',
            'da_allowances' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:255',
            'document' => 'nullable|file|max:10240',
        ]);
    }
}

