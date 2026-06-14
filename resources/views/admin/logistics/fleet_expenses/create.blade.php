@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Add fleet expense" subtitle="Record fuel, maintenance, rent, or toll for an own-fleet vehicle.">
        <x-slot:actions>
            <a href="{{ route('admin.fleet-expenses.index') }}" class="erp-btn-secondary">Back</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.fleet-expenses.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.logistics.fleet_expenses.partials.form', ['expense' => $expense])
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.fleet-expenses.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save expense</button>
            </div>
        </form>
    </div>
</div>
@endsection
