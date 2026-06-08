@php
    $notice = $headerNotice ?? null;
@endphp

@if(! empty($notice['message']))
    <div
        class="header-notice header-notice--{{ $notice['type'] ?? 'info' }} print-hidden"
        role="status"
        aria-live="polite"
    >
        <div class="header-notice__inner">
            <span class="header-notice__badge">{{ $notice['label'] ?? 'Notice' }}</span>
            @if(! empty($notice['link']))
                <a href="{{ $notice['link'] }}" class="header-notice__message header-notice__message--link">
                    {{ $notice['message'] }}
                </a>
            @else
                <p class="header-notice__message">{{ $notice['message'] }}</p>
            @endif
        </div>
    </div>
@endif
