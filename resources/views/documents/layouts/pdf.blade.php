<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $payload['title'] ?? 'Document' }} · {{ $payload['number'] ?? '' }}</title>
    @include('documents.partials.styles', [
        'company' => $company,
        'orientation' => $orientation ?? ($payload['orientation'] ?? 'portrait'),
    ])
    @if(($payload['sheet'] ?? '') === 'invoice')
        @include('documents.invoice.styles', [
            'orientation' => $orientation ?? ($payload['orientation'] ?? 'portrait'),
        ])
    @endif
</head>
<body class="{{ (($orientation ?? ($payload['orientation'] ?? 'portrait')) === 'landscape') ? 'doc-pdf-landscape' : 'doc-pdf-portrait' }}">
    @include('documents.partials.body', [
        'company' => $company,
        'payload' => $payload,
        'forPdf' => true,
    ])
</body>
</html>
