@props([
    'variant' => 'inline',
    'nameMode' => null,
])

@php
    $display = $appBrandDisplay ?? ['title' => $appBrandName ?? 'ERP', 'tagline' => null, 'full' => $appBrandName ?? 'ERP'];
    $logoUrl = $appLogoUrl ?? \App\Helpers\SystemSettings::defaultLogoUrl();
    $nameMode = $nameMode ?? match ($variant) {
        'sidebar', 'header', 'loader', 'inline', 'login' => 'none',
        default => 'none',
    };
    $variantClasses = match ($variant) {
        'sidebar' => 'erp-brand-mark erp-brand-mark--sidebar',
        'login' => 'erp-brand-mark erp-brand-mark--login',
        'header' => 'erp-brand-mark erp-brand-mark--header',
        'loader' => 'erp-brand-mark erp-brand-mark--loader',
        default => 'erp-brand-mark erp-brand-mark--inline',
    };
    if ($nameMode === 'none') {
        $variantClasses .= ' erp-brand-mark--logo-only';
    }
@endphp

<div {{ $attributes->merge(['class' => $variantClasses]) }}>
    @if(! ($appLogoIsCustom ?? false))
        <img src="{{ asset('images/brand/saf-logo-light.svg') }}"
             alt="{{ $display['full'] }}"
             class="erp-brand-mark__logo shrink-0 object-contain dark:hidden" />
        <img src="{{ asset('images/brand/saf-logo-dark.svg') }}"
             alt="{{ $display['full'] }}"
             class="erp-brand-mark__logo hidden shrink-0 object-contain dark:block" />
    @else
        <img src="{{ $logoUrl }}"
             alt="{{ $display['full'] }}"
             class="erp-brand-mark__logo shrink-0 object-contain" />
    @endif

    @if($nameMode === 'full')
        <div class="erp-brand-mark__text min-w-0">
            <span class="erp-brand-mark__title">{{ $display['title'] }}</span>
            @if(!empty($display['tagline']))
                <span class="erp-brand-mark__tagline">{{ $display['tagline'] }}</span>
            @endif
        </div>
    @elseif($nameMode === 'companion' && !empty($display['tagline']))
        <span class="erp-brand-mark__companion">{{ $display['tagline'] }}</span>
    @endif
</div>
