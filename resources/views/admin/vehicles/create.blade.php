@extends('layouts.app')

@section('content')
<div class="dash-page space-y-6">
    <x-admin.page-header
        title="Add vehicle"
        subtitle="Register a truck or van for scheduling, load planning, and fleet expense tracking."
    >
        <x-slot:actions>
            <a href="{{ route('admin.vehicles.index') }}" class="erp-btn-secondary">Back to registry</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vehicle details</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Core fleet information used across logistics and delivery.</p>
        </div>

        <form action="{{ route('admin.vehicles.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Vehicle name <span class="text-error-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g. Truck 1, Van A"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('name')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle type</label>
                    <input type="text"
                           id="type"
                           name="type"
                           value="{{ old('type', 'truck') }}"
                           placeholder="truck, van, pickup"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('type')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="license_plate" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">License plate</label>
                    <input type="text"
                           id="license_plate"
                           name="license_plate"
                           value="{{ old('license_plate') }}"
                           placeholder="e.g. DHA-1234"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('license_plate')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="ownership" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ownership</label>
                    <select id="ownership" name="ownership" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach(\App\Models\Vehicle::ownershipOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(old('ownership', 'own') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fuel_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fuel type</label>
                    <select id="fuel_type" name="fuel_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">—</option>
                        @foreach(\App\Models\Vehicle::fuelTypeOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(old('fuel_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="driver" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Default driver</label>
                    <input type="text"
                           id="driver"
                           name="driver"
                           value="{{ old('driver') }}"
                           placeholder="Driver name"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('driver')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="capacity_crates" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity (crates)</label>
                    <input type="number"
                           id="capacity_crates"
                           name="capacity_crates"
                           min="0"
                           step="1"
                           value="{{ old('capacity_crates') }}"
                           placeholder="e.g. 100"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('capacity_crates')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6">
                <input type="hidden" name="is_active" value="0">
                <label for="is_active" class="inline-flex items-center gap-2">
                    <input type="checkbox"
                           id="is_active"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', 1))
                           class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inactive vehicles are hidden from delivery scheduling.</p>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.vehicles.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save vehicle</button>
            </div>
        </form>
    </div>
</div>
@endsection
