@php
    $isFuel = old('expense_type', $expense->expense_type ?? 'fuel') === 'fuel';
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div>
        <label for="vehicle_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle <span class="text-error-500">*</span></label>
        <select id="vehicle_id" name="vehicle_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">Select vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $expense->vehicle_id ?? '') == $vehicle->id)>{{ $vehicle->name }}</option>
            @endforeach
        </select>
        @error('vehicle_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="expense_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cost type <span class="text-error-500">*</span></label>
        <select id="expense_type" name="expense_type" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @foreach($types as $value => $label)
                <option value="{{ $value }}" @selected(old('expense_type', $expense->expense_type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('expense_type')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="expense_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Date <span class="text-error-500">*</span></label>
        <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('expense_date')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (BDT) <span class="text-error-500">*</span></label>
        <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount', $expense->amount ?? '') }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('amount')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="payment_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment</label>
        <select id="payment_type" name="payment_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="bank" @selected(old('payment_type', $expense->payment_type ?? 'bank') === 'bank')>Paid from bank</option>
            <option value="cash" @selected(old('payment_type', $expense->payment_type ?? '') === 'cash')>Cash</option>
            <option value="payable" @selected(old('payment_type', $expense->payment_type ?? '') === 'payable')>Pay later (AP)</option>
        </select>
    </div>

    <div>
        <label for="payment_account_key" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pay from account</label>
        <select id="payment_account_key" name="payment_account_key" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @foreach($paymentAccountKeys as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_account_key', $expense->payment_account_key ?? 'bank_default') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Reference / receipt no.</label>
        <input type="text" id="reference" name="reference" value="{{ old('reference', $expense->reference ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label for="delivery_route_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Route (optional)</label>
        <select id="delivery_route_id" name="delivery_route_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">—</option>
            @foreach($routes as $route)
                <option value="{{ $route->id }}" @selected(old('delivery_route_id', $expense->delivery_route_id ?? '') == $route->id)>{{ $route->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="trip_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Trip date (optional)</label>
        <input type="date" id="trip_date" name="trip_date" value="{{ old('trip_date', optional($expense->trip_date)->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <input type="text" id="description" name="description" value="{{ old('description', $expense->description ?? '') }}" placeholder="e.g. CNG fill Agrabad route" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>
</div>

<div class="mt-6 rounded-xl border border-brand-200 bg-brand-50/50 p-4 dark:border-brand-500/30 dark:bg-brand-500/5">
    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Fuel details (optional)</h4>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">For fuel entries — litres × rate can auto-fill amount.</p>
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="fuel_litres" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Litres</label>
            <input type="number" id="fuel_litres" name="fuel_litres" step="0.01" min="0" value="{{ old('fuel_litres', $expense->fuel_litres ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label for="fuel_rate" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rate / litre</label>
            <input type="number" id="fuel_rate" name="fuel_rate" step="0.01" min="0" value="{{ old('fuel_rate', $expense->fuel_rate ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label for="odometer_km" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Odometer (km)</label>
            <input type="number" id="odometer_km" name="odometer_km" min="0" value="{{ old('odometer_km', $expense->odometer_km ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
    </div>
</div>

<input type="hidden" name="status" value="{{ old('status', $expense->status ?? 'recorded') }}">
