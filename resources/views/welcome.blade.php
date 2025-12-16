<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SaferP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-shell">
    <div class="public-hero">
        <header class="public-header">
            <div class="brand">
                <span class="brand-title">SAFERP</span>
            </div>
            <nav class="public-nav">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="button-primary">Go to Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="button-secondary">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="button-primary">Admin sign in</a>
                @endauth
            </nav>
        </header>

        <main class="public-main">
            <section class="public-intro">
                <h1>Run your business in one place.</h1>
                <p>
                    SaferP centralises product master data, agent orders, logistics, inventory,
                    production, and finance so you can trace every bottle from source to shelf.
                </p>
                <div class="public-cta">
                    <span class="public-hint">Access is restricted to authorised SaferPV staff.</span>
                </div>
            </section>
        </main>

        <footer class="public-footer">
            <small>&copy; {{ date('Y') }} SaferPV. All rights reserved.</small>
        </footer>
    </div>
</body>
</html>
