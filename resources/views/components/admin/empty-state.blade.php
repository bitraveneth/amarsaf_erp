@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'erp-empty']) }}>
    <p class="erp-empty-title">{{ $title }}</p>
    @if($description)
        <p class="erp-empty-text">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
