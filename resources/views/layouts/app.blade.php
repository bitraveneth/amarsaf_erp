<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SAFERP') }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="admin-shell">
        @include('layouts.partials.admin-header')
        @if(session('status') || ($errors ?? null) && $errors->any())
            <div class="admin-flash">
                @if(session('status'))
                    <div class="flash flash-success">
                        <span class="flash-icon" aria-hidden="true">✓</span>
                        <span>{{ session('status') }}</span>
                        <button type="button" class="flash-close" aria-label="Dismiss message">×</button>
                    </div>
                @endif
                @if(($errors ?? null) && $errors->any())
                    <div class="flash flash-error">
                        <span class="flash-icon" aria-hidden="true">!</span>
                        <div>
                            <strong>Something went wrong.</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="flash-close" aria-label="Dismiss errors">×</button>
                    </div>
                @endif
            </div>
        @endif
        <div class="admin-content">
            @include('layouts.partials.admin-sidebar')
            <main class="admin-main">
                @yield('content')
            </main>
        </div>
        @include('layouts.partials.admin-footer')
        @auth
            <a href="{{ route('admin.help') }}" class="help-bubble" title="Help &amp; system guide">?</a>
        @endauth
    </div>
    @stack('scripts')
</body>
</html>
