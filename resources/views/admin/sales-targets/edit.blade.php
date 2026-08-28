@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <x-admin.page-header
        icon="chart"
        eyebrow="Sales"
        title="Edit target"
        :subtitle="'Update '.$salesTarget->ownerName().' for '.$salesTarget->period_start->format('F Y').'.'"
    >
        <x-slot:actions>
            <a href="{{ route('admin.sales-targets.index', ['month' => $salesTarget->period_start->format('Y-m')]) }}" class="erp-btn-secondary">Board</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.sales-targets.update', $salesTarget) }}" method="POST" class="erp-target-hero space-y-6">
        @csrf
        @method('PATCH')
        @include('admin.sales-targets.partials.form', ['salesTarget' => $salesTarget])
        <div class="erp-form-actions">
            <a href="{{ route('admin.sales-targets.index', ['month' => $salesTarget->period_start->format('Y-m')]) }}" class="erp-btn-secondary">Cancel</a>
            <button type="submit" class="erp-btn-primary">Update target</button>
        </div>
    </form>
</div>
@endsection
