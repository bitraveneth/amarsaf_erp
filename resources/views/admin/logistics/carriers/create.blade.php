@extends('layouts.app')

@section('content')
<div class="dash-page space-y-6">
    <x-admin.page-header title="Add transport carrier" subtitle="Register a hired truck, courier, or freight vendor for logistics bills.">
        <x-slot:actions>
            <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-btn-secondary">Back to carriers</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.logistics.carriers.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.logistics.carriers.partials.form', ['carrier' => $carrier])
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save carrier</button>
            </div>
        </form>
    </div>
</div>
@endsection
