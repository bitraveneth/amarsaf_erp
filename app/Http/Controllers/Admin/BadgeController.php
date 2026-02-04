<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Employee;
use App\Models\EmployeeBadge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::orderBy('name')->get();

        return view('admin.employees.badges.index', compact('badges'));
    }

    public function create()
    {
        return view('admin.employees.badges.create', ['badge' => new Badge()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedBadge($request);

        Badge::create($data);

        return redirect()->route('admin.badges.index')->with('status', 'Badge created.');
    }

    public function edit(Badge $badge)
    {
        return view('admin.employees.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge)
    {
        $data = $this->validatedBadge($request, $badge->id);

        $badge->update($data);

        return redirect()->route('admin.badges.index')->with('status', 'Badge updated.');
    }

    public function destroy(Badge $badge)
    {
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('status', 'Badge deleted.');
    }

    public function grantForm(Employee $employee)
    {
        $badges = Badge::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.badges.grant', compact('employee', 'badges'));
    }

    public function grant(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'badge_id' => 'required|exists:badges,id',
            'granted_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $badge = Badge::findOrFail($data['badge_id']);

        EmployeeBadge::create([
            'employee_id' => $employee->id,
            'badge_id' => $badge->id,
            'granted_at' => $data['granted_at'] ?? now(),
            'granted_by' => auth()->user()->name ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.employees.edit', $employee)
            ->with('status', 'Badge granted to employee.');
    }

    public function grantFromBadgeForm(Badge $badge)
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.employees.badges.grant_from_badge', compact('badge', 'employees'));
    }

    public function grantFromBadge(Request $request, Badge $badge)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'granted_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        EmployeeBadge::create([
            'employee_id' => $data['employee_id'],
            'badge_id' => $badge->id,
            'granted_at' => $data['granted_at'] ?? now(),
            'granted_by' => auth()->user()->name ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.badges.index')
            ->with('status', 'Badge granted to employee.');
    }

    protected function validatedBadge(Request $request, ?int $badgeId = null): array
    {
        $idRule = $badgeId ? ',' . $badgeId : '';

        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:badges,code' . $idRule,
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
