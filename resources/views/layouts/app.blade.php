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
        <header class="admin-header">
            <div class="brand">
                <span>SAFERP</span>
                <small>Admin panel</small>
            </div>
            <nav class="header-links">
                @guest
                    <a href="{{ route('login') }}">Sign in</a>
                @endguest
                @auth
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Sign out</button>
                    </form>
                @endauth
            </nav>
        </header>
        <div class="admin-content">
            <aside class="admin-sidebar">
                <p class="sidebar-title">Control</p>
                <ul class="sidebar-main">
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="sidebar-icon" aria-hidden="true">📊</span>
                            Dashboard
                        </a>
                    </li>
                </ul>

                @php
                    $masterActive = request()->routeIs('admin.products.*')
                        || request()->routeIs('admin.packaging.*')
                        || request()->routeIs('admin.tax-classes.*')
                        || request()->routeIs('admin.batches.*');
                @endphp
                <div class="sidebar-group{{ $masterActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-group-header">
                        <span class="sidebar-group-title">Product &amp; Master Data</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Product &amp; Master Data"
                                aria-expanded="{{ $masterActive ? 'true' : 'false' }}"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('admin.products.index') }}"
                               class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧾</span>
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.packaging.index') }}"
                               class="{{ request()->routeIs('admin.packaging.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📦</span>
                                Packaging
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tax-classes.index') }}"
                               class="{{ request()->routeIs('admin.tax-classes.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">💰</span>
                                Tax classes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.batches.index') }}"
                               class="{{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧪</span>
                                Batches
                            </a>
                        </li>
                    </ul>
                </div>

                @php
                    $salesActive = request()->routeIs('admin.agents.*')
                        || request()->routeIs('admin.orders.*')
                        || request()->routeIs('admin.commissions.*')
                        || request()->routeIs('admin.settlements.*');
                    $inventoryActive = request()->routeIs('admin.warehouses.*')
                        || request()->routeIs('admin.warehouse-locations.*')
                        || request()->routeIs('admin.delivery-routes.*')
                        || request()->routeIs('admin.deliveries.*')
                        || request()->routeIs('admin.vehicles.*')
                        || request()->routeIs('admin.vehicle-schedule.*')
                        || request()->routeIs('admin.stock.*')
                        || request()->routeIs('admin.inventory.*')
                        || request()->routeIs('admin.vehicle-load.*');
                    $productionActive = request()->routeIs('admin.production.*');
                    $financeActive = request()->routeIs('admin.finance.*')
                        || request()->routeIs('admin.suppliers.*')
                        || request()->routeIs('admin.bills.*')
                        || request()->routeIs('admin.accounts.*')
                        || request()->routeIs('admin.finance.reconciliation')
                        || request()->routeIs('admin.reports.*');
                @endphp

                <div class="sidebar-group{{ $salesActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-group-header">
                        <span class="sidebar-group-title">Sales</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Sales"
                                aria-expanded="{{ $salesActive ? 'true' : 'false' }}"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('admin.agents.index') }}"
                               class="{{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧍</span>
                                Agents
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.index') }}"
                               class="{{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧾</span>
                                Orders
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.picking-overview') }}"
                               class="{{ request()->routeIs('admin.orders.picking-overview') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📑</span>
                                Picking lists
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.commissions.index') }}"
                               class="{{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">💸</span>
                                Commissions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settlements.index') }}"
                               class="{{ request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">✅</span>
                                Commission payouts
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-group{{ $inventoryActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-group-header">
                        <span class="sidebar-group-title">Inventory &amp; Logistics</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Inventory and Logistics"
                                aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('admin.warehouses.index') }}"
                               class="{{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🏭</span>
                                Warehouses
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.warehouse-locations.index') }}"
                               class="{{ request()->routeIs('admin.warehouse-locations.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🗂️</span>
                                Locations &amp; slotting
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.delivery-routes.index') }}"
                               class="{{ request()->routeIs('admin.delivery-routes.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🗺️</span>
                                Routes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.deliveries.index') }}"
                               class="{{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🚚</span>
                                Deliveries &amp; packing
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vehicles.index') }}"
                               class="{{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🚐</span>
                                Vehicles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vehicle-schedule.index') }}"
                               class="{{ request()->routeIs('admin.vehicle-schedule.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📅</span>
                                Fleet schedule
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.stock.transfers') }}"
                               class="{{ request()->routeIs('admin.stock.transfers') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🔁</span>
                                Stock transfers
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.stock.movements') }}"
                               class="{{ request()->routeIs('admin.stock.movements') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📃</span>
                                Movements log
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.inventory.index') }}"
                               class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📊</span>
                                Inventory health
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vehicle-load.index') }}"
                               class="{{ request()->routeIs('admin.vehicle-load.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🚛</span>
                                Vehicle load
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.stock.audit') }}"
                               class="{{ request()->routeIs('admin.stock.audit*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📏</span>
                                Stock audit
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-group{{ $productionActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-group-header">
                        <span class="sidebar-group-title">Production</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Production"
                                aria-expanded="{{ $productionActive ? 'true' : 'false' }}"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('admin.production.index') }}"
                               class="{{ request()->routeIs('admin.production.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🏭</span>
                                Production runs
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-group{{ $financeActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-group-header">
                        <span class="sidebar-group-title">Finance</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Finance"
                                aria-expanded="{{ $financeActive ? 'true' : 'false' }}"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('admin.finance.index') }}"
                               class="{{ request()->routeIs('admin.finance.index') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">💼</span>
                                Invoices &amp; receipts
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.suppliers.index') }}"
                               class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🏢</span>
                                Suppliers
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bills.index') }}"
                               class="{{ request()->routeIs('admin.bills.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📥</span>
                                Purchase bills
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.accounts.index') }}"
                               class="{{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📚</span>
                                Chart of accounts
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.finance.reconciliation') }}"
                               class="{{ request()->routeIs('admin.finance.reconciliation') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🏦</span>
                                Bank reconciliation
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.pl') }}"
                               class="{{ request()->routeIs('admin.reports.pl') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">📈</span>
                                Profit &amp; Loss
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.vat') }}"
                               class="{{ request()->routeIs('admin.reports.vat') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧾</span>
                                VAT report
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.bs') }}"
                               class="{{ request()->routeIs('admin.reports.bs') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">🧮</span>
                                Balance Sheet
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.cashflow') }}"
                               class="{{ request()->routeIs('admin.reports.cashflow') ? 'active' : '' }}">
                                <span class="sidebar-icon" aria-hidden="true">💧</span>
                                Cashflow
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <main class="admin-main">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
