<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $payload['title'] ?? 'Document' }} · {{ $payload['number'] ?? '' }}</title>
    <link rel="stylesheet" href="{{ asset('css/document-print.css') }}?v={{ @filemtime(public_path('css/document-print.css')) ?: 1 }}">
    @include('documents.partials.styles', compact('company'))
</head>
<body class="doc-preview-body">
    <div class="doc-toolbar print-hidden">
        <div class="doc-toolbar-inner">
            <div>
                <div class="doc-toolbar-title">{{ $payload['title'] ?? 'Document' }}</div>
                <div class="doc-toolbar-subtitle">{{ $payload['number'] ?? '' }}</div>
            </div>
            <div class="doc-toolbar-actions">
                @if($backUrl)
                    <a href="{{ $backUrl }}" class="doc-btn doc-btn-secondary">{{ __('documents.actions.back') }}</a>
                @endif
                <button type="button" class="doc-btn doc-btn-secondary" onclick="window.print()">{{ __('documents.actions.print') }}</button>
                <a href="{{ route('admin.documents.pdf', ['type' => $type, 'id' => $id]) }}" class="doc-btn doc-btn-primary">{{ __('documents.actions.download_pdf') }}</a>
            </div>
        </div>
    </div>

    <div class="doc-preview-page">
        @include('documents.partials.body', compact('company', 'payload'))
    </div>

    @if(!empty($autoPrint))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
