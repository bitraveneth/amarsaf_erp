<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Work date <span class="text-error-500">*</span></label>
            <input type="date" name="work_date" value="{{ old('work_date', optional($record->work_date)->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @error('work_date')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Hours <span class="text-error-500">*</span></label>
            <input type="number" name="hours" step="0.25" min="0.25" max="24" value="{{ old('hours', $record->hours) }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @error('hours')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">OT multiplier</label>
            <select name="rate_multiplier" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @foreach(\App\Models\EmployeeOvertime::multipliers() as $value => $label)
                    <option value="{{ $value }}" @selected((string) old('rate_multiplier', $record->rate_multiplier ?? '1.50') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Hourly rate (BDT)</label>
            <input type="number" name="hourly_rate" step="0.01" min="0" value="{{ old('hourly_rate', $record->hourly_rate ?? ($hourlyRate ?? 0)) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From active contract: salary ÷ 26 days ÷ 8 hours.</p>
            @error('hourly_rate')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $record->status ?? 'pending') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
            <input type="text" name="reason" value="{{ old('reason', $record->reason) }}" placeholder="e.g. Month-end dispatch, production rush" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @error('reason')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
