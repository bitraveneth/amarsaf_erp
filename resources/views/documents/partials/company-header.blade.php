<table class="doc-banner">
    <tr>
        <td class="doc-banner-brand">
            <table class="doc-brand-table">
                <tr>
                    <td class="doc-logo-cell">
                        @if(($forPdf ?? false) && !empty($company['logo_file_uri']))
                            <img src="{{ $company['logo_file_uri'] }}" alt="" class="doc-logo">
                        @elseif(!empty($company['logo_url']))
                            <img src="{{ $company['logo_url'] }}" alt="" class="doc-logo">
                        @else
                            <div class="doc-logo-fallback">{{ $company['initials'] ?? 'SA' }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="doc-company-name">{{ $company['legal_name'] ?? config('app.name') }}</div>
                        <div class="doc-company-meta">
                            @if(!empty($company['address'])){{ $company['address'] }}<br>@endif
                            @if(!empty($company['phone'])){{ $company['phone'] }}@endif
                            @if(!empty($company['phone']) && !empty($company['email'])) · @endif
                            @if(!empty($company['email'])){{ $company['email'] }}@endif
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="doc-banner-doc">
            <div class="doc-title">{{ $payload['title'] ?? 'Document' }}</div>
            @if(!empty($payload['subtitle']))
                <div class="doc-copy-pill">{{ $payload['subtitle'] }}</div>
            @endif
            @if(!empty($payload['number']))
                <div class="doc-number">{{ $payload['number'] }}</div>
            @endif
            @if(($payload['copy_count'] ?? 1) > 1)
                <div class="doc-status">{{ __('documents.labels.copy_count', ['current' => $payload['copy_index'], 'total' => $payload['copy_count']]) }}</div>
            @endif
            @if(!empty($payload['status']))
                <div class="doc-status">{{ $payload['status'] }}</div>
            @endif
        </td>
    </tr>
</table>
