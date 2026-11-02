@props(['notice' => null])

@if(!empty($notice))
    <div class="header-notice header-notice--warning print-hidden" role="status" aria-live="polite">
        <div class="header-notice__inner" style="align-items: flex-start; flex-wrap: wrap; gap: 0.75rem 1rem;">
            <span class="header-notice__badge">Ledger</span>
            <div style="flex: 1 1 18rem; min-width: 0;">
                <p class="header-notice__message">{{ $notice['title'] }}</p>
                <p style="margin: 0.2rem 0 0; font-size: 0.75rem; font-weight: 500; line-height: 1.45;">{{ $notice['why'] }}</p>
                @if(!empty($notice['examples']))
                    <ul style="margin: 0.35rem 0 0; padding-left: 1rem; font-size: 0.75rem; line-height: 1.4;">
                        @foreach($notice['examples'] as $example)
                            <li>{{ $example['label'] }} — {{ $example['detail'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                @if(!empty($notice['can_sync']) && \App\Helpers\Permission::can(auth()->user(), 'accounting.manage'))
                    <form method="POST" action="{{ route('admin.expenses.sync-ledger') }}">
                        @csrf
                        <input type="hidden" name="scope" value="missing">
                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                        <button type="submit" style="border: 1px solid currentColor; background: transparent; color: inherit; border-radius: 999px; padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                            Sync missing expenses
                        </button>
                    </form>
                @endif
                <a href="{{ $notice['report_url'] }}" class="header-notice__message header-notice__message--link" style="font-size: 0.75rem;">Income statement</a>
            </div>
        </div>
    </div>
@endif
