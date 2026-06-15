@props([
    'category',
])

@php
    $meta = collect(\App\Support\ReportsCatalog::menuCategories())->firstWhere('key', $category);
@endphp

@if($meta && ! empty($meta['hubRoute']))
    <a href="{{ route($meta['hubRoute']) }}" {{ $attributes->merge(['class' => 'erp-btn-secondary']) }}>
        {{ $meta['label'] }}
    </a>
@endif
