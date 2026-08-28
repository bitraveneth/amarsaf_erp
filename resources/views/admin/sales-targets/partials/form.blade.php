<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <x-admin.form.field name="agent_id" label="Agent">
        <select id="agent_id" name="agent_id" class="erp-input">
            <option value="">Not an agent target</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ old('agent_id', $salesTarget?->agent_id) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
            @endforeach
        </select>
    </x-admin.form.field>

    <x-admin.form.field name="employee_id" label="Seller">
        <select id="employee_id" name="employee_id" class="erp-input">
            <option value="">Not a seller target</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ old('employee_id', $salesTarget?->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}{{ $employee->work_zone ? ' · '.$employee->work_zone : '' }}</option>
            @endforeach
        </select>
    </x-admin.form.field>

    <x-admin.form.field name="period_start" label="Period start">
        <input id="period_start" name="period_start" type="date" value="{{ old('period_start', optional($salesTarget?->period_start)->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d')) }}" class="erp-input">
    </x-admin.form.field>

    <x-admin.form.field name="period_end" label="Period end">
        <input id="period_end" name="period_end" type="date" value="{{ old('period_end', optional($salesTarget?->period_end)->format('Y-m-d') ?? now()->endOfMonth()->format('Y-m-d')) }}" class="erp-input">
    </x-admin.form.field>

    <x-admin.hover-hint
        class="erp-hover-hint--start md:col-span-2"
        title="Target amount"
        text="Achievement is invoiced net sales. Seller targets use completed visit-plan agents, then the employee work zone."
    >
        <x-admin.form.field name="target_value" label="Target value" required>
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                <input id="target_value" name="target_value" type="number" step="0.01" min="0.01" value="{{ old('target_value', $salesTarget?->target_value) }}" class="erp-input pl-12">
            </div>
        </x-admin.form.field>
    </x-admin.hover-hint>
</div>
