<div class="form-section space-y-5">
    <div class="section-header border-b border-gray-200 pb-4 dark:border-gray-800">
        <h3 class="text-title-sm font-semibold text-gray-900 dark:text-white">Expense details</h3>
        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Pick a category to route this expense to the correct ledger.</p>
    </div>

    <div class="form-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-6">
        <div class="flex flex-col gap-1.5">
            <label for="date" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Date <span class="text-error-500">*</span>
            </label>
            <input id="date" name="date" type="date"
                   value="{{ old('date', optional($expense->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   required class="erp-input">
            @error('date')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="expense_category_id" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Category <span class="text-error-500">*</span>
            </label>
            <select id="expense_category_id" name="expense_category_id" required class="erp-input">
                @foreach($expenseCategories ?? [] as $category)
                    <option value="{{ $category->id }}" @selected((int) old('expense_category_id', $expense->expense_category_id) === $category->id)>
                        {{ $category->name }} → {{ $category->account?->name }}
                    </option>
                @endforeach
            </select>
            @error('expense_category_id')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5 md:col-span-2">
            <label for="account_id" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Ledger override <span class="text-xs font-normal text-gray-500">(optional)</span>
            </label>
            <select id="account_id" name="account_id" class="erp-input">
                <option value="">Use category default</option>
                @foreach($expenseAccounts ?? [] as $account)
                    <option value="{{ $account->id }}" @selected((int) old('account_id', $expense->account_id) === $account->id)>
                        {{ $account->code }} · {{ $account->name }}
                    </option>
                @endforeach
            </select>
            @error('account_id')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="amount" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Amount <span class="text-error-500">*</span>
            </label>
            <input id="amount" name="amount" type="number" step="0.01" min="0"
                   value="{{ old('amount', $expense->amount ?? '') }}" required class="erp-input">
            @error('amount')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="payment_type" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Payment <span class="text-error-500">*</span>
            </label>
            @php $paymentType = old('payment_type', $expense->payment_type ?? 'bank'); @endphp
            <select id="payment_type" name="payment_type" required class="erp-input">
                <option value="bank" @selected($paymentType === 'bank')>Paid from bank</option>
                <option value="cash" @selected($paymentType === 'cash')>Paid from cash</option>
                <option value="payable" @selected($paymentType === 'payable')>Accrued / payable</option>
            </select>
            @error('payment_type')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="payment_account_key" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Bank / cash account
            </label>
            @php $paymentKey = old('payment_account_key', $expense->payment_account_key ?? 'bank_default'); @endphp
            <select id="payment_account_key" name="payment_account_key" required class="erp-input">
                @foreach($paymentAccountKeys ?? [] as $key => $label)
                    <option value="{{ $key }}" @selected($paymentKey === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_account_key')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="reference" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
            <input id="reference" name="reference" value="{{ old('reference', $expense->reference ?? '') }}" class="erp-input">
            @error('reference')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="analytic_label" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Cost center / department
            </label>
            <input id="analytic_label" name="analytic_label" value="{{ old('analytic_label', $expense->analytic_label ?? '') }}" class="erp-input" placeholder="e.g. Sales, Factory">
            @error('analytic_label')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2 flex flex-col gap-1.5">
            <label for="description" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea id="description" name="description" rows="3" class="erp-input">{{ old('description', $expense->description ?? '') }}</textarea>
            @error('description')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="status" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            @php $currentStatus = strtolower(old('status', $expense->status ?? 'recorded')); @endphp
            <select id="status" name="status" class="erp-input">
                <option value="recorded" @selected($currentStatus === 'recorded')>Recorded</option>
                <option value="reviewed" @selected($currentStatus === 'reviewed')>Reviewed</option>
            </select>
            @error('status')<p class="text-theme-xs text-error-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
