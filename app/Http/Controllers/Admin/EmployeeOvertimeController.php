<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Support\OvertimeCalculator;
use Illuminate\Http\Request;

class EmployeeOvertimeController extends Controller
{
    public function __construct(protected OvertimeCalculator $calculator)
    {
    }

    public function all(Request $request)
    {
        $query = EmployeeOvertime::with('employee')->orderByDesc('work_date');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $records = $query->paginate(20)->withQueryString();

        return view('admin.employees.overtime.all', compact('records'));
    }

    public function index(Employee $employee)
    {
        $records = $employee->overtime()->orderByDesc('work_date')->paginate(15);
        $hourlyRate = $this->calculator->hourlyRate($employee);

        return view('admin.employees.overtime.index', compact('employee', 'records', 'hourlyRate'));
    }

    public function create(Employee $employee)
    {
        $hourlyRate = $this->calculator->hourlyRate($employee);

        return view('admin.employees.overtime.create', [
            'employee' => $employee,
            'record' => new EmployeeOvertime([
                'work_date' => now()->toDateString(),
                'rate_multiplier' => 1.5,
                'hourly_rate' => $hourlyRate,
                'status' => EmployeeOvertime::STATUS_PENDING,
            ]),
            'hourlyRate' => $hourlyRate,
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request, $employee);
        $employee->overtime()->create($data);

        return redirect()
            ->route('admin.employees.overtime.index', $employee)
            ->with('status', 'Overtime request recorded.');
    }

    public function edit(Employee $employee, EmployeeOvertime $overtime)
    {
        $this->assertBelongsToEmployee($employee, $overtime);

        return view('admin.employees.overtime.edit', [
            'employee' => $employee,
            'record' => $overtime,
            'hourlyRate' => (float) ($overtime->hourly_rate ?: $this->calculator->hourlyRate($employee)),
        ]);
    }

    public function update(Request $request, Employee $employee, EmployeeOvertime $overtime)
    {
        $this->assertBelongsToEmployee($employee, $overtime);

        if ($overtime->status === EmployeeOvertime::STATUS_PAID) {
            return back()->withErrors(['status' => 'Paid overtime cannot be edited.']);
        }

        $data = $this->validated($request, $employee);

        if (in_array($data['status'], [EmployeeOvertime::STATUS_APPROVED, EmployeeOvertime::STATUS_REJECTED], true)) {
            $data['approved_by'] = auth()->user()->name ?? $overtime->approved_by;
            $data['approved_at'] = now();
        }

        $overtime->update($data);

        return redirect()
            ->route('admin.employees.overtime.index', $employee)
            ->with('status', 'Overtime request updated.');
    }

    public function destroy(Employee $employee, EmployeeOvertime $overtime)
    {
        $this->assertBelongsToEmployee($employee, $overtime);

        return redirect()
            ->route('admin.employees.overtime.index', $employee)
            ->withErrors(['overtime' => 'Overtime history cannot be deleted. Update the status instead.']);
    }

    public function createGlobal()
    {
        return view('admin.employees.overtime.create_global', [
            'employees' => Employee::orderBy('name')->get(),
            'record' => new EmployeeOvertime([
                'work_date' => now()->toDateString(),
                'rate_multiplier' => 1.5,
                'status' => EmployeeOvertime::STATUS_PENDING,
            ]),
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));
        $data = $this->validated($request, $employee);
        $employee->overtime()->create($data);

        return redirect()
            ->route('admin.overtime.index')
            ->with('status', 'Overtime request recorded.');
    }

    protected function validated(Request $request, Employee $employee): array
    {
        $data = $request->validate([
            'employee_id' => 'sometimes|exists:employees,id',
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'rate_multiplier' => 'required|numeric|min:1|max:3',
            'hourly_rate' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $hourlyRate = isset($data['hourly_rate']) && $data['hourly_rate'] !== ''
            ? (float) $data['hourly_rate']
            : $this->calculator->hourlyRate($employee);

        $computed = $this->calculator->buildRecord(
            $employee,
            $data['work_date'],
            (float) $data['hours'],
            (float) $data['rate_multiplier'],
            $hourlyRate,
        );

        unset($data['employee_id']);

        return array_merge($data, $computed);
    }

    protected function assertBelongsToEmployee(Employee $employee, EmployeeOvertime $overtime): void
    {
        if ($overtime->employee_id !== $employee->id) {
            abort(404);
        }
    }
}
