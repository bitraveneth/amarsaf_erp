@props([
    'hr' => [],
])

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Team & HR"
        description="Leave, overtime approvals, and payroll activity this month."
        class="mb-4"
    >
        <x-slot:actions>
            <a href="{{ route('admin.employees.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Employees</a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-supply-grid dash-supply-grid--three">
        <a href="{{ route('admin.leaves.index') }}" class="dash-supply-tile dash-supply-tile--neutral">
            <span class="dash-supply-tile__value">{{ number_format($hr['on_leave_today'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">On leave today</span>
            <span class="dash-supply-tile__caption">Approved leave covering today</span>
        </a>
        <a href="{{ route('admin.overtime.index') }}" class="dash-supply-tile dash-supply-tile--{{ ($hr['pending_overtime'] ?? 0) > 0 ? 'warning' : 'neutral' }}">
            <span class="dash-supply-tile__value">{{ number_format($hr['pending_overtime'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">Pending overtime</span>
            <span class="dash-supply-tile__caption">Entries awaiting approval</span>
        </a>
        <a href="{{ route('admin.reports.payroll') }}" class="dash-supply-tile dash-supply-tile--brand">
            <span class="dash-supply-tile__value">{{ number_format($hr['payroll_runs'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">Payroll runs MTD</span>
            <span class="dash-supply-tile__caption">Salary distributions this month</span>
        </a>
    </div>
</section>
