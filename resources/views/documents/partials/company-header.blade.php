<div class="doc-header">
    <div class="doc-header-left">
        @if(!empty($company['logo_path']))
            <img src="file://{{ str_replace('\\', '/', $company['logo_path']) }}" alt="" class="doc-logo">
        @elseif(!empty($company['logo_url']))
            <img src="{{ $company['logo_url'] }}" alt="" class="doc-logo">
        @else
            <span class="doc-logo-fallback">{{ $company['initials'] ?? 'SA' }}</span>
        @endif
        <span class="doc-company-name">{{ $company['legal_name'] ?? config('app.name') }}</span>
        <div class="doc-company-meta">
            @if(!empty($company['address'])){{ $company['address'] }}<br>@endif
            @if(!empty($company['phone'])){{ $company['phone'] }}@endif
            @if(!empty($company['phone']) && !empty($company['email'])) · @endif
            @if(!empty($company['email'])){{ $company['email'] }}@endif
        </div>
    </div>
    <div class="doc-header-right">
        <div class="doc-title">{{ $payload['title'] ?? 'Document' }}</div>
        @if(!empty($payload['subtitle']))
            <div class="doc-subtitle">{{ $payload['subtitle'] }}</div>
        @endif
        <div class="doc-subtitle">{{ $payload['number'] ?? '' }}</div>
        @if(!empty($payload['status']))
            <span class="doc-badge">{{ $payload['status'] }}</span>
        @endif
        <div class="doc-generated">
            {{ __('documents.labels.generated') }} {{ optional($payload['generated_at'] ?? now())->format('d M Y, H:i') }}
        </div>
    </div>
</div>
