<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SAFERP') }}</title>
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
                    SAFERP centralises product master data, agent orders, logistics, inventory,
                    production, and finance so you can trace every bottle from source to shelf.
                </p>
                <div class="public-cta">
                    <span class="public-hint">Access is restricted to authorised SAFERP staff.</span>
                </div>
            </section>

            <section class="public-process">
                <h2>How the system works</h2>
                <div class="process-flow">
                    <div class="process-step">1. Configure products, packaging, tax classes and batches</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">2. Set up agents, price lists and commission rules</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">3. Record production runs, approve QC and create batch stock</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">4. Stock appears by batch in warehouses and locations</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">5. Create agent orders with SKUs, quantities and delivery dates</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">6. Reserve FEFO stock and generate picking lists</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">7. Plan routes, assign vehicles and deliver with POD</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">8. Create invoices from delivered orders</div>
                    <span class="process-arrow">↓</span>
                    <div class="process-step">9. Record receipts and credit notes and review reports</div>
                </div>
            </section>
        </main>

        <footer class="public-footer">
            <small>&copy; {{ date('Y') }} SAFERP. All rights reserved.</small>
        </footer>
    </div>
</body>
</html>
