@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Add rate card" subtitle="Define freight pricing for a transport carrier.">
        <x-slot:actions><a href="{{ route('admin.carrier-rate-cards.index') }}" class="erp-btn-secondary">Back</a></x-slot:actions>
    </x-admin.page-header>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.carrier-rate-cards.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.logistics.rate_cards.partials.form', ['card' => $card])
            <div class="flex justify-end gap-3"><a href="{{ route('admin.carrier-rate-cards.index') }}" class="erp-btn-secondary">Cancel</a><button type="submit" class="erp-btn-primary">Save</button></div>
        </form>
    </div>
</div>
@endsection
