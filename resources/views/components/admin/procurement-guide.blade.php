@props([
    'title' => 'What happens next',
    'steps' => [],
])

@if(count($steps) > 0)
    <section {{ $attributes->merge(['class' => 'erp-proc-guide']) }}>
        <header class="erp-proc-guide__head">
            <div class="erp-proc-guide__icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h2 class="erp-proc-guide__title">{{ $title }}</h2>
                <p class="erp-proc-guide__subtitle">Follow these steps — each role knows where to go.</p>
            </div>
        </header>
        <ol class="erp-proc-guide__steps">
            @foreach($steps as $step)
                @php
                    $state = $step['state'] ?? 'upcoming';
                @endphp
                <li @class(['erp-proc-guide__step', 'is-' . $state])>
                    <div class="erp-proc-guide__marker" aria-hidden="true">
                        @if($state === 'complete')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span>{{ $loop->iteration }}</span>
                        @endif
                    </div>
                    <div class="erp-proc-guide__body">
                        <p class="erp-proc-guide__label">{{ $step['label'] }}</p>
                        @if(!empty($step['hint']))
                            <p class="erp-proc-guide__hint">{{ $step['hint'] }}</p>
                        @endif
                        @if(!empty($step['action_url']) && !empty($step['action_label']) && $state === 'current')
                            <a href="{{ $step['action_url'] }}" class="erp-proc-guide__action">
                                {{ $step['action_label'] }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
@endif
