@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'period' => null,
    'pageClass' => '',
])

<div {{ $attributes->merge(['class' => 'erp-dash-page ' . $pageClass]) }}>
    <x-dashboard.hero
        :eyebrow="$eyebrow"
        :title="$title"
        :subtitle="$subtitle"
        :period="$period"
    >
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-dashboard.hero>

    @isset($alerts)
        <div class="report-page__alerts space-y-4">{{ $alerts }}</div>
    @endisset

    @isset($filters)
        <div class="report-page__filters">{{ $filters }}</div>
    @endisset

    @isset($kpis)
        <div class="erp-dash-kpi-grid report-page__kpis">{{ $kpis }}</div>
    @endisset

    <div class="report-page__body space-y-6">
        {{ $slot }}
    </div>
</div>
