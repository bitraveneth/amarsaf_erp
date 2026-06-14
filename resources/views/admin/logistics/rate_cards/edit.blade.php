@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Edit rate card" :subtitle="$card->transportCarrier->name ?? ''">
        <x-slot:actions><a href="{{ route('admin.carrier-rate-cards.index') }}" class="erp-btn-secondary">Back</a></x-slot:actions>
    </x-admin.page-header>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.carrier-rate-cards.update', $card) }}" method="POST" class="space-y-6">
            @csrf @method('PATCH')
            @include('admin.logistics.rate_cards.partials.form', ['card' => $card])
            <div class="flex justify-end gap-3"><a href="{{ route('admin.carrier-rate-cards.index') }}" class="erp-btn-secondary">Cancel</a><button type="submit" class="erp-btn-primary">Update</button></div>
        </form>
    </div>
</div>
@endsection
