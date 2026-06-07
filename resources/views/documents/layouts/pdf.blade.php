<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $payload['title'] ?? 'Document' }} · {{ $payload['number'] ?? '' }}</title>
    @include('documents.partials.styles', compact('company'))
</head>
<body>
    @include('documents.partials.body', compact('company', 'payload'))
</body>
</html>
