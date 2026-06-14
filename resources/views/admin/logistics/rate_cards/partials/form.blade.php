<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Carrier *</label>
        <select name="transport_carrier_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">Select carrier</option>
            @foreach($carriers as $carrier)
                <option value="{{ $carrier->id }}" @selected(old('transport_carrier_id', $card->transport_carrier_id ?? '') == $carrier->id)>{{ $carrier->name }}</option>
            @endforeach
        </select>
        @error('transport_carrier_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500"><a href="{{ route('admin.logistics.carriers.create') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400">Add new carrier</a></p>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Route (optional)</label>
        <select name="delivery_route_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All routes</option>
            @foreach($routes as $route)
                <option value="{{ $route->id }}" @selected(old('delivery_route_id', $card->delivery_route_id ?? '') == $route->id)>{{ $route->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rate unit *</label>
        <select name="unit" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            @foreach($units as $value => $label)
                <option value="{{ $value }}" @selected(old('unit', $card->unit ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rate (BDT) *</label>
        <input type="number" name="rate" step="0.01" min="0.01" value="{{ old('rate', $card->rate ?? '') }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <input type="text" name="notes" value="{{ old('notes', $card->notes ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
    </div>
    <div>
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $card->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-brand-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
        </label>
    </div>
</div>
