<aside class="admin-sidebar">
    <p class="sidebar-title">ERP System</p>

    @php
        $role = auth()->user()->role ?? 'admin';

        $controlActive = request()->routeIs(
            'admin.products.*',
            'admin.materials.*',
            'admin.packaging.*',
            'admin.tax-classes.*',
            'admin.agents.*',
            'admin.suppliers.*',
            'admin.bills.*',
            'admin.commissions.*',
            'admin.warehouses.*',
            'admin.warehouse-locations.*',
            'admin.delivery-routes.*',
            'admin.vehicles.*',
            'admin.employees.*',
            'admin.contracts.*',
            'admin.allowances.*',
            'admin.equipment.*',
            'admin.leaves.*',
            'admin.badges.*',
            'admin.help'
        );

        // Control sub‑group active states (for default expand / collapse)
        $productsSubActive = request()->routeIs(
            'admin.products.*',
            'admin.materials.*',
            'admin.packaging.*',
            'admin.tax-classes.*',
            'admin.products.prices.*'
        );

        $agentsSubActive = request()->routeIs(
            'admin.agents.*',
            'admin.commissions.rules'
        );

        $suppliersSubActive = request()->routeIs(
            'admin.suppliers.*',
            'admin.bills.*'
        );

        $warehousesSubActive = request()->routeIs(
            'admin.warehouses.*',
            'admin.warehouse-locations.*',
            'admin.delivery-routes.*',
            'admin.vehicles.*'
        );

        $employeesSubActive = request()->routeIs(
            'admin.employees.*',
            'admin.contracts.*',
            'admin.allowances.*',
            'admin.equipment.*',
            'admin.leaves.*',
            'admin.badges.*'
        );

        $systemSubActive = request()->routeIs('admin.help');

        $manufacturingActive = request()->routeIs(
            'admin.boms.*',
            'admin.production.*',
            'admin.batches.*',
            'admin.reports.production'
        );

        $inventoryActive = request()->routeIs(
            'admin.inventory.*',
            'admin.stock.*',
            'admin.vehicle-load.*',
            'admin.deliveries.pod-index',
            'admin.deliveries.packing-index',
            'admin.orders.picking-overview',
            'admin.orders.picking-list'
        );

        $salesActive = request()->routeIs(
            'admin.orders.index',
            'admin.orders.create',
            'admin.orders.show',
            'admin.orders.edit',
            'admin.returns.customer.*',
            'admin.gifts.*',
            'admin.campaigns.*',
            'admin.deliveries.index',
            'admin.commissions.index',
            'admin.settlements.*'
        );

        $accountingActive = request()->routeIs(
            'admin.finance.*',
            'admin.expenses.*',
            'admin.accounts.*',
            'admin.salary-distributions.*',
            'admin.finance.reconciliation',
            'admin.reports.vat',
            'admin.reports.pl',
            'admin.reports.bs',
            'admin.reports.cashflow',
            'admin.reports.payroll',
            'admin.reports.agents'
        );
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

    {{-- 1. Control (Masters & Settings) --}}
    @if(in_array($role, ['admin', 'employee']))
        <div class="sidebar-group{{ $controlActive ? '' : ' collapsed' }}" data-collapsible>
            <div class="sidebar-group-header">
                <span class="sidebar-group-title">Control (Masters &amp; Settings)</span>
                <button type="button" class="sidebar-toggle" data-collapse-toggle>
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>

            <ul class="sidebar-submenu">
                {{-- Products subgroup --}}
                <li>
                    <div class="sidebar-subgroup{{ $productsSubActive ? '' : ' collapsed' }}" data-collapsible>
                        <div class="sidebar-subgroup-header">
                            <span class="sidebar-group-title">Products</span>
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
                                <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                        <ul class="sidebar-submenu sidebar-submenu-nested">
                            <li>
                                <a href="{{ route('admin.products.index') }}"
                                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                                    Products
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.materials.index') }}"
                                   class="{{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
                                    Materials (raw / service)
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
                    <div class="sidebar-subgroup{{ $agentsSubActive ? '' : ' collapsed' }}" data-collapsible>
                        <div class="sidebar-subgroup-header">
                            <span class="sidebar-group-title">Agents</span>
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
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

                {{-- Suppliers subgroup --}}
                <li>
                    <div class="sidebar-subgroup{{ $suppliersSubActive ? '' : ' collapsed' }}" data-collapsible>
                        <div class="sidebar-subgroup-header">
                            <span class="sidebar-group-title">Suppliers</span>
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
                                <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                        <ul class="sidebar-submenu sidebar-submenu-nested">
                            <li>
                                <a href="{{ route('admin.suppliers.index') }}"
                                   class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                                    Suppliers
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.bills.index') }}"
                                   class="{{ request()->routeIs('admin.bills.*') ? 'active' : '' }}">
                                    Purchase bills
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
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
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
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
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
                    <div class="sidebar-subgroup{{ $systemSubActive ? '' : ' collapsed' }}" data-collapsible>
                        <div class="sidebar-subgroup-header">
                            <span class="sidebar-group-title">System settings</span>
                            <button type="button" class="sidebar-toggle" data-collapse-toggle>
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

    {{-- 2. Manufacturing --}}
    @if(in_array($role, ['admin', 'warehouse_manager', 'qc_officer', 'production_manager']))
        <div class="sidebar-group{{ $manufacturingActive ? '' : ' collapsed' }}" data-collapsible>
            <div class="sidebar-group-header">
                <span class="sidebar-group-title">Manufacturing</span>
                <button type="button" class="sidebar-toggle" data-collapse-toggle>
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
                        Production orders &amp; runs
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.production.pending-receipts') }}"
                       class="{{ request()->routeIs('admin.production.pending-receipts') ? 'active' : '' }}">
                        Pending receipts
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

    {{-- 3. Inventory (Core Operations) --}}
    @if(in_array($role, ['admin', 'warehouse_manager', 'qc_officer', 'production_manager']))
        <div class="sidebar-group{{ $inventoryActive ? '' : ' collapsed' }}" data-collapsible>
            <div class="sidebar-group-header">
                <span class="sidebar-group-title">Inventory (Core Operations)</span>
                <button type="button" class="sidebar-toggle" data-collapse-toggle>
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('admin.inventory.index') }}"
                       class="{{ request()->routeIs('admin.inventory.index') ? 'active' : '' }}">
                        Inventory dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.inventory.materials') }}"
                       class="{{ request()->routeIs('admin.inventory.materials') ? 'active' : '' }}">
                        Material stock
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
                    <a href="{{ route('admin.orders.picking-overview') }}"
                       class="{{ request()->routeIs('admin.orders.picking-overview') || request()->routeIs('admin.orders.picking-list') ? 'active' : '' }}">
                        Picking lists
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

    {{-- 4. Sales --}}
    @if(in_array($role, ['admin', 'employee']))
        <div class="sidebar-group{{ $salesActive ? '' : ' collapsed' }}" data-collapsible>
            <div class="sidebar-group-header">
                <span class="sidebar-group-title">Sales</span>
                <button type="button" class="sidebar-toggle" data-collapse-toggle>
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

    {{-- 5. Accounting --}}
    @if($role === 'admin')
        <div class="sidebar-group{{ $accountingActive ? '' : ' collapsed' }}" data-collapsible>
            <div class="sidebar-group-header">
                <span class="sidebar-group-title">Accounting</span>
                <button type="button" class="sidebar-toggle" data-collapse-toggle>
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
                    <a href="{{ route('admin.reports.agents') }}"
                       class="{{ request()->routeIs('admin.reports.agents') ? 'active' : '' }}">
                        Agent performance
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
