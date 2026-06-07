@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'dash-section-header']) }}>
    <div class="dash-section-header__intro">
        <h2 class="dash-section-title">{{ $title }}</h2>
        @if($description)
            <p class="dash-section-desc">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="dash-section-header__actions shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
