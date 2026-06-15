@props([
    'reports' => [],
])

@if(count($reports) > 0)
    <section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
        <x-dashboard.section-header
            title="Reports shortcuts"
            description="Jump straight to the reports you use most."
            class="mb-4"
        >
            <x-slot:actions>
                <a href="{{ route('admin.reports.dashboard') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">All reports</a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="dash-report-shortcuts">
            @foreach($reports as $report)
                <a href="{{ $report['href'] ?? '#' }}" class="dash-report-shortcut">
                    @if(! empty($report['badge']))
                        <span class="dash-report-shortcut__badge">{{ $report['badge'] }}</span>
                    @endif
                    <span class="dash-report-shortcut__title">{{ $report['title'] ?? 'Report' }}</span>
                    @if(! empty($report['hint']))
                        <span class="dash-report-shortcut__hint">{{ $report['hint'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
