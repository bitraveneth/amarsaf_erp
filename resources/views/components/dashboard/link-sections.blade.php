@props([
    'sections' => [],
])

<div {{ $attributes->merge(['class' => 'erp-dash-link-sections']) }}>
    @foreach($sections as $section)
        <div class="erp-dash-link-sections__block">
            @if(! empty($section['title']))
                <div class="erp-dash-link-sections__head">
                    <h3 class="erp-dash-link-sections__title">{{ $section['title'] }}</h3>
                    @if(! empty($section['subtitle']))
                        <p class="erp-dash-link-sections__subtitle">{{ $section['subtitle'] }}</p>
                    @endif
                </div>
            @endif
            <x-dashboard.link-grid :links="$section['links'] ?? []" />
        </div>
    @endforeach
</div>
