<aside class="admin-sidebar">
    <p class="sidebar-title">ERP System</p>

    @php
        $role = auth()->user()->role ?? 'admin';

        $controlActive = request()->routeIs('admin.products.*')
            || request()->routeIs('admin.packaging.*')
            || request()->routeIs('admin.tax-classes.*')
            || request()->routeIs('admin.agents.*')
            || request()->routeIs('admin.commissions.rules')
            || request()->routeIs('admin.warehouses.*')
            || request()->routeIs('admin.warehouse-locations.*')
            || request()->routeIs('admin.delivery-routes.*')
            || request()->routeIs('admin.vehicles.*')
            || request()->routeIs('admin.employees.*')
            || request()->routeIs('admin.contracts.*')
            || request()->routeIs('admin.allowances.*')
            || request()->routeIs('admin.equipment.*')
            || request()->routeIs('admin.leaves.*')
            || request()->routeIs('admin.badges.*')
            || request()->routeIs('admin.help');

        $productsSubActive = request()->routeIs('admin.products.*')
            || request()->routeIs('admin.packaging.*')
            || request()->routeIs('admin.tax-classes.*')
            || request()->routeIs('admin.products.prices.*');
        $customersSubActive = request()->routeIs('admin.agents.*')
            || request()->routeIs('admin.commissions.rules');
        $warehousesSubActive = request()->routeIs('admin.warehouses.*')
            || request()->routeIs('admin.warehouse-locations.*')
            || request()->routeIs('admin.delivery-routes.*')
            || request()->routeIs('admin.vehicles.*');
        $employeesSubActive = request()->routeIs('admin.employees.*')
            || request()->routeIs('admin.contracts.*')
            || request()->routeIs('admin.allowances.*')
            || request()->routeIs('admin.equipment.*')
            || request()->routeIs('admin.leaves.*')
            || request()->routeIs('admin.badges.*');
        $systemSettingsSubActive = request()->routeIs('admin.help');

        $manufacturingActive = request()->routeIs('admin.boms.*')
            || request()->routeIs('admin.production.*')
            || request()->routeIs('admin.batches.*')
            || request()->routeIs('admin.reports.production');

        $inventoryActive = request()->routeIs('admin.inventory.*')
            || request()->routeIs('admin.stock.*')
            || request()->routeIs('admin.vehicle-load.*')
            || request()->routeIs('admin.deliveries.pod-index')
            || request()->routeIs('admin.deliveries.packing-index');

        $salesActive = request()->routeIs('admin.orders.*')
            || request()->routeIs('admin.returns.customer.*')
            || request()->routeIs('admin.gifts.*')
            || request()->routeIs('admin.campaigns.*')
            || request()->routeIs('admin.deliveries.index')
            || request()->routeIs('admin.commissions.index')
            || request()->routeIs('admin.settlements.*');

        $accountingActive = request()->routeIs('admin.finance.*')
            || request()->routeIs('admin.expenses.*')
            || request()->routeIs('admin.accounts.*')
            || request()->routeIs('admin.salary-distributions.*')
            || request()->routeIs('admin.finance.reconciliation')
            || request()->routeIs('admin.reports.vat')
            || request()->routeIs('admin.reports.pl')
            || request()->routeIs('admin.reports.bs')
            || request()->routeIs('admin.reports.cashflow')
            || request()->routeIs('admin.reports.payroll');
    @endphp

    {{-- Dashboard (root) --}}
    <div class="sidebar-group">
        <div class="sidebar-group-header">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
        </div>
    </div>

    {{-- Control (Masters & Settings) --}}
    @if(in_array($role, ['admin', 'warehouse_manager', 'employee']))
    <div class="sidebar-group{{ $controlActive ? '' : ' collapsed' }}" data-collapsible>
        <div class="sidebar-group-header">
            <span class="sidebar-group-title">Control (Masters &amp; Settings)</span>
            <button type="button"
                    class="sidebar-toggle"
                    aria-label="Toggle Control"
                    aria-expanded="{{ $controlActive ? 'true' : 'false' }}"
                    data-collapse-toggle
                    data-open-icon="−"
                    data-closed-icon="+">
                <span class="sidebar-toggle-icon" aria-hidden="true"></span>
            </button>
        </div>
        <ul class="sidebar-submenu">
            {{-- Products subgroup --}}
            <li>
                <div class="sidebar-subgroup{{ $productsSubActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-subgroup-header">
                        <span class="sidebar-group-title">Products</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Products"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu sidebar-submenu-nested">
                        <li>
                            <a href="{{ route('admin.products.index') }}"
                               class="{{ (request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.prices.*')) ? 'active' : '' }}">
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.packaging.index') }}"
                               class="{{ request()->routeIs('admin.packaging.*') ? 'active' : '' }}">
                                Packaging types
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tax-classes.index') }}"
                               class="{{ request()->routeIs('admin.tax-classes.*') ? 'active' : '' }}">
                                Tax &amp; VAT classes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.prices.index') }}"
                               class="{{ request()->routeIs('admin.products.prices.*') ? 'active' : '' }}">
                                Price lists
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Agents subgroup --}}
            <li>
                <div class="sidebar-subgroup{{ $customersSubActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-subgroup-header">
                        <span class="sidebar-group-title">Agents</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Customers / Agents"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu sidebar-submenu-nested">
                        <li>
                            <a href="{{ route('admin.agents.index') }}"
                               class="{{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                                Agents
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.commissions.rules') }}"
                               class="{{ request()->routeIs('admin.commissions.rules') ? 'active' : '' }}">
                                Commission rules
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Warehouses subgroup --}}
            <li>
                <div class="sidebar-subgroup{{ $warehousesSubActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-subgroup-header">
                        <span class="sidebar-group-title">Warehouses</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Warehouses"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu sidebar-submenu-nested">
                        <li>
                            <a href="{{ route('admin.warehouses.index') }}"
                               class="{{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                                Warehouses
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.warehouse-locations.index') }}"
                               class="{{ request()->routeIs('admin.warehouse-locations.*') ? 'active' : '' }}">
                                Warehouse locations
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.delivery-routes.index') }}"
                               class="{{ request()->routeIs('admin.delivery-routes.*') ? 'active' : '' }}">
                                Delivery routes
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vehicles.index') }}"
                               class="{{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                                Fleet
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Employees subgroup --}}
            <li>
                <div class="sidebar-subgroup{{ $employeesSubActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-subgroup-header">
                        <span class="sidebar-group-title">Employees</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle Employees"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu sidebar-submenu-nested">
                        <li>
                            <a href="{{ route('admin.employees.index') }}"
                               class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                                Employees
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.contracts.index') }}"
                               class="{{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}">
                                Contracts
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.allowances.index') }}"
                               class="{{ request()->routeIs('admin.allowances.*') ? 'active' : '' }}">
                                Allowances
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.equipment.index') }}"
                               class="{{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}">
                                Equipment
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.leaves.index') }}"
                               class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                                Leaves
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.badges.index') }}"
                               class="{{ request()->routeIs('admin.badges.*') ? 'active' : '' }}">
                                Badges
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- System settings subgroup --}}
            <li>
                <div class="sidebar-subgroup{{ $systemSettingsSubActive ? '' : ' collapsed' }}" data-collapsible>
                    <div class="sidebar-subgroup-header">
                        <span class="sidebar-group-title">System settings</span>
                        <button type="button"
                                class="sidebar-toggle"
                                aria-label="Toggle System settings"
                                data-collapse-toggle
                                data-open-icon="−"
                                data-closed-icon="+">
                            <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    <ul class="sidebar-submenu sidebar-submenu-nested">
                        <li>
                            <a href="{{ route('admin.help') }}"
                               class="{{ request()->routeIs('admin.help') ? 'active' : '' }}">
                                Help &amp; configuration guide
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    @endif

    {{-- Manufacturing --}}
    @if(in_array($role, ['admin', 'warehouse_manager']))
    <div class="sidebar-group{{ $manufacturingActive ? '' : ' collapsed' }}" data-collapsible>
        <div class="sidebar-group-header">
            <span class="sidebar-group-title">Manufacturing</span>
            <button type="button"
                    class="sidebar-toggle"
                    aria-label="Toggle Manufacturing"
                    aria-expanded="{{ $manufacturingActive ? 'true' : 'false' }}"
                    data-collapse-toggle
                    data-open-icon="−"
                    data-closed-icon="+">
                <span class="sidebar-toggle-icon" aria-hidden="true"></span>
            </button>
        </div>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.boms.index') }}"
                   class="{{ request()->routeIs('admin.boms.*') ? 'active' : '' }}">
                    BOMs (Bill of Materials)
                </a>
            </li>
            <li>
                <a href="{{ route('admin.production.index') }}"
                   class="{{ request()->routeIs('admin.production.*') ? 'active' : '' }}">
                    Production orders
                </a>
            </li>
            <li>
                <a href="{{ route('admin.batches.index') }}"
                   class="{{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
                    Batches &amp; lots
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.production') }}"
                   class="{{ request()->routeIs('admin.reports.production') ? 'active' : '' }}">
                    Production analysis
                </a>
            </li>
        </ul>
    </div>
    @endif

    {{-- Inventory (Core Operations) --}}
    @if(in_array($role, ['admin', 'warehouse_manager']))
    <div class="sidebar-group{{ $inventoryActive ? '' : ' collapsed' }}" data-collapsible>
        <div class="sidebar-group-header">
            <span class="sidebar-group-title">Inventory (Core Operations)</span>
            <button type="button"
                    class="sidebar-toggle"
                    aria-label="Toggle Inventory"
                    aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}"
                    data-collapse-toggle
                    data-open-icon="−"
                    data-closed-icon="+">
                <span class="sidebar-toggle-icon" aria-hidden="true"></span>
            </button>
        </div>
        <ul class="sidebar-submenu">
            <li>
                <a href="{{ route('admin.inventory.index') }}"
                   class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                    Inventory dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.stock.transfers') }}"
                   class="{{ request()->routeIs('admin.stock.transfers') ? 'active' : '' }}">
                    Transfers
                </a>
            </li>
            <li>
                <a href="{{ route('admin.deliveries.pod-index') }}"
                   class="{{ request()->routeIs('admin.deliveries.pod-index') ? 'active' : '' }}">
                    Deliveries &amp; POD
                </a>
            </li>
            <li>
                <a href="{{ route('admin.vehicle-load.index') }}"
                   class="{{ request()->routeIs('admin.vehicle-load.*') ? 'active' : '' }}">
                    Vehicle loads
                </a>
            </li>
            <li>
                <a href="{{ route('admin.deliveries.packing-index') }}"
                   class="{{ request()->routeIs('admin.deliveries.packing-index') ? 'active' : '' }}">
                    Packing slips
                </a>
            </li>
            <li>
                <a href="{{ route('admin.stock.audit') }}"
                   class="{{ request()->routeIs('admin.stock.audit*') ? 'active' : '' }}">
                    Inventory adjustments
                </a>
            </li>
        </ul>
    </div>
    @endif

    {{-- Sales --}}
    @if(in_array($role, ['admin', 'employee']))
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
                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                    Sales orders
                </a>
            </li>
            <li>
                <a href="{{ route('admin.orders.picking-overview') }}"
                   class="{{ request()->routeIs('admin.orders.picking-overview') ? 'active' : '' }}">
                    Picking lists
                </a>
            </li>
            <li>
                <a href="{{ route('admin.deliveries.index') }}"
                   class="{{ request()->routeIs('admin.deliveries.index') ? 'active' : '' }}">
                    Deliveries
                </a>
            </li>
            <li>
                <a href="{{ route('admin.returns.customer.index') }}"
                   class="{{ request()->routeIs('admin.returns.customer.*') ? 'active' : '' }}">
                    Returns
                </a>
            </li>
            <li>
                <a href="{{ route('admin.gifts.index') }}"
                   class="{{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}">
                    Customer gifts
                </a>
            </li>
            <li>
                <a href="{{ route('admin.campaigns.index') }}"
                   class="{{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
                    Marketing campaigns
                </a>
            </li>
            <li>
                <a href="{{ route('admin.commissions.index') }}"
                   class="{{ request()->routeIs('admin.commissions.index') ? 'active' : '' }}">
                    Commission report
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settlements.index') }}"
                   class="{{ request()->routeIs('admin.settlements.*') ? 'active' : '' }}">
                    Commission settlements
                </a>
            </li>
        </ul>
    </div>
    @endif

    {{-- Accounting --}}
    @if($role === 'admin')
    <div class="sidebar-group{{ $accountingActive ? '' : ' collapsed' }}" data-collapsible>
        <div class="sidebar-group-header">
            <span class="sidebar-group-title">Accounting</span>
            <button type="button"
                    class="sidebar-toggle"
                    aria-label="Toggle Accounting"
                    aria-expanded="{{ $accountingActive ? 'true' : 'false' }}"
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
                    Customer invoices
                </a>
            </li>
            <li>
                <a href="{{ route('admin.expenses.index') }}"
                   class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                    Expenses
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.payroll') }}"
                   class="{{ request()->routeIs('admin.reports.payroll') ? 'active' : '' }}">
                    Payroll
                </a>
            </li>
            <li>
                <a href="{{ route('admin.salary-distributions.index') }}"
                   class="{{ request()->routeIs('admin.salary-distributions.*') ? 'active' : '' }}">
                    Salary distributions
                </a>
            </li>
            <li>
                <a href="{{ route('admin.accounts.index') }}"
                   class="{{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                    Chart of accounts
                </a>
            </li>
            <li>
                <a href="{{ route('admin.finance.reconciliation') }}"
                   class="{{ request()->routeIs('admin.finance.reconciliation') ? 'active' : '' }}">
                    Bank reconciliation
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.vat') }}"
                   class="{{ request()->routeIs('admin.reports.vat') ? 'active' : '' }}">
                    Tax report
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.pl') }}"
                   class="{{ request()->routeIs('admin.reports.pl') ? 'active' : '' }}">
                    Profit &amp; Loss
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.bs') }}"
                   class="{{ request()->routeIs('admin.reports.bs') ? 'active' : '' }}">
                    Balance sheet
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.cashflow') }}"
                   class="{{ request()->routeIs('admin.reports.cashflow') ? 'active' : '' }}">
                    Cashflow
                </a>
            </li>
        </ul>
    </div>
    @endif
</aside>
