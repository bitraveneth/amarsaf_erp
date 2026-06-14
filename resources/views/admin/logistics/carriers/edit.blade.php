@extends('layouts.app')

@section('content')
<div class="dash-page space-y-6">
    <x-admin.page-header :title="'Edit carrier — ' . $carrier->name" subtitle="Update contact details or deactivate a transport vendor.">
        <x-slot:actions>
            <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-btn-secondary">Back to carriers</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.logistics.carriers.update', $carrier) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')
            @include('admin.logistics.carriers.partials.form', ['carrier' => $carrier])
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Update carrier</button>
            </div>
        </form>

        @if(!$carrier->logisticsBills()->exists() && !$carrier->carrierRateCards()->exists())
            <form action="{{ route('admin.logistics.carriers.destroy', $carrier) }}" method="POST" class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800"
                  onsubmit="return confirm('Delete this carrier?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="erp-btn-action-danger">Delete carrier</button>
            </form>
        @endif
    </div>
</div>
@endsection
