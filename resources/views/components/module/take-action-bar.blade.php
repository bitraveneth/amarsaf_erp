@props([
    'title' => 'Take action',
    'description' => 'Warehouse operations — dispatch, fleet, and site management.',
    'groups' => [],
    'primaryAction' => null,
])

@php
    $primary = $primaryAction ?? collect($groups)->flatMap(fn ($g) => $g['actions'] ?? [])->firstWhere('primary', true);
@endphp

<section {{ $attributes->merge(['class' => 'dash-take-action-v2']) }}>
    <div class="dash-take-action-v2__hero">
        <div class="dash-take-action-v2__intro">
            <div class="dash-take-action-v2__icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                </svg>
            </div>
            <div>
                <p class="dash-take-action-v2__title">{{ $title }}</p>
                <p class="dash-take-action-v2__desc">{{ $description }}</p>
            </div>
        </div>

        @if($primary)
            <a href="{{ $primary['href'] }}" class="dash-take-action-v2__primary">
                <span class="dash-take-action-v2__primary-icon" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </span>
                <span>
                    <span class="dash-take-action-v2__primary-label">{{ $primary['label'] }}</span>
                    @if(! empty($primary['hint']))
                        <span class="dash-take-action-v2__primary-hint">{{ $primary['hint'] }}</span>
                    @endif
                </span>
            </a>
        @endif
    </div>

    <div class="dash-take-action-v2__groups">
        @foreach($groups as $group)
            <div class="dash-take-action-v2__group">
                <p class="dash-take-action-v2__group-label">{{ $group['label'] }}</p>
                <div class="dash-take-action-v2__tiles">
                    @foreach($group['actions'] as $action)
                        @if(! empty($action['primary']))
                            @continue
                        @endif
                        <a href="{{ $action['href'] }}" class="dash-take-action-v2__tile group">
                            <span class="dash-take-action-v2__tile-icon" aria-hidden="true">
                                @switch($action['icon'] ?? 'default')
                                    @case('delivery')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" /></svg>
                                        @break
                                    @case('load')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375a1.125 1.125 0 0 0-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                                        @break
                                    @case('slip')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        @break
                                    @case('route')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
                                        @break
                                    @case('fleet')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" /></svg>
                                        @break
                                    @case('warehouse')
                                    @case('sites')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                                        @break
                                    @case('bins')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5v9l9 5.25M12 12.75V22.5" /></svg>
                                        @break
                                    @case('add')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        @break
                                    @default
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                @endswitch
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5">
                                    <span class="dash-take-action-v2__tile-label">{{ $action['label'] }}</span>
                                    @if(! empty($action['badge']) && (int) $action['badge'] > 0)
                                        <span class="dash-take-action-v2__tile-badge">{{ number_format((int) $action['badge']) }}</span>
                                    @endif
                                </span>
                                @if(! empty($action['hint']))
                                    <span class="dash-take-action-v2__tile-hint">{{ $action['hint'] }}</span>
                                @endif
                            </span>
                            <svg class="dash-take-action-v2__tile-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
