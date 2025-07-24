<!-- Sidebar -->
<div id="sidebar"
    class="sidebar fixed top-0 left-0 z-50 h-screen w-64 bg-card/95 backdrop-blur-sm border-r border-border/50 lg:translate-x-0 flex flex-col shadow-xl">
    <!-- Header -->
    <div
        class="flex h-16 items-center border-b border-border/30 px-4 flex-shrink-0 bg-gradient-to-r from-primary/5 to-primary/10">
        <div class="flex items-center space-x-3">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground shadow-lg">
                <i class="fas fa-gas-pump text-sm"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold text-foreground">FuelStation ERP</span>
                <span class="text-xs text-muted-foreground font-medium">Enterprise</span>
            </div>
        </div>
        <button id="sidebar-close"
            class="ml-auto button button-ghost h-8 w-8 lg:hidden hover:bg-accent/50 transition-all">
            <i class="fas fa-x text-xs"></i>
        </button>
    </div>

    <!-- Navigation - Scrollable Content -->
    <div class="sidebar-content flex-1 p-3">
        <nav class="space-y-2">
            <!-- 1. OVERVIEW - All Users -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-blue-500 to-blue-600">Core</span>
                        Overview
                    </span>
                </div>
                <a href="#"
                    class="menu-section-header active bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-primary/10 rounded-lg p-1.5">
                            <i class="fas fa-chart-line text-primary"></i>
                        </div>
                        <span class="font-medium">Dashboard</span>
                    </div>
                    <span class="badge-status bg-green-100 text-green-700 border border-green-200">Live</span>
                </a>
            </div>
    <!-- 9. REPORTS -->
            <div class="menu-section">
                {{-- <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-rose-500 to-rose-600">Analytics</span>
                        Reports & Analytics
                    </span>
                </div> --}}

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('reports-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-rose-50 rounded-lg p-1.5">
                            <i class="fas fa-chart-bar text-rose-600"></i>
                        </div>
                        <span class="font-medium">Operational Reports</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="reports-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="reports-dropdown">
                    <a href="{{ route('reports.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-calendar-day w-3 h-3 mr-2 opacity-60"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exclamation-triangle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Analysis</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-boxes w-3 h-3 mr-2 opacity-60"></i>
                        <span>Inventory Status</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-dollar-sign w-3 h-3 mr-2 opacity-60"></i>
                        <span>Financial Performance</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-clipboard-check w-3 h-3 mr-2 opacity-60"></i>
                        <span>Compliance Reports</span>
                    </a>

                    <!-- ADD as LAST item in existing inventory-tracking-dropdown -->
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-layer-group w-3 h-3 mr-2 opacity-60"></i>
                        <span>Batch Consumption Tracking</span>
                    </a>
                </div> --}}

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('dashboards-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-amber-50 rounded-lg p-1.5">
                            <i class="fas fa-chart-pie text-amber-600"></i>
                        </div>
                        <span class="font-medium">Executive Dashboards</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="dashboards-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="dashboards-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-cogs w-3 h-3 mr-2 opacity-60"></i>
                        <span>Operational Dashboard</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-line w-3 h-3 mr-2 opacity-60"></i>
                        <span>Financial Dashboard</span>
                    </a>
                </div> --}}
            </div>
            <!-- 2. DAILY OPERATIONS - All Users -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-emerald-500 to-emerald-600">Ops</span>
                        Daily Operations
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('daily-operations-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-emerald-50 rounded-lg p-1.5">
                            <i class="fas fa-calendar-day text-emerald-600"></i>
                        </div>
                        <span class="font-medium">Daily Readings</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-status bg-blue-100 text-blue-700 border border-blue-200">Active</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="daily-operations-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="daily-operations-dropdown">
                    <a href="{{ route('morning.readings.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-emerald-600"></i>
                        <span>Morning dip Readings</span>
                    </a>
                    <a href="{{ route('evening.readings.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-ruler w-3 h-3 mr-2 opacity-60"></i>
                      <span>Evening dip Readings</span>
                    </a>
                    <a href="{{ route('continuous-meter.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Meter Readings</span>
                    </a>
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-check-circle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Reading Validation</span>
                    </a> --}}
                    {{-- <a href="{{ route('reconciliation.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Daily Reconciliation</span>
                    </a> --}}
                </div>

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('reconciliation-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-amber-50 rounded-lg p-1.5">
                            <i class="fas fa-balance-scale text-amber-600"></i>
                        </div>
                        <span class="font-medium">Reconciliation</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-count bg-amber-100 text-amber-700 border border-amber-200">2</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="reconciliation-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="reconciliation-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-calculator w-3 h-3 mr-2 opacity-60"></i>
                        <span>Daily Reconciliation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-cog w-3 h-3 mr-2 opacity-60"></i>
                        <span>Generate Reconciliation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-check w-3 h-3 mr-2 opacity-60"></i>
                        <span>Approve Reconciliation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>Reconciliation History</span>
                    </a>

                    <!-- ADD these items to existing reconciliation-dropdown -->
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-calendar-week w-3 h-3 mr-2 opacity-60"></i>
                        <span>Weekly Reconciliation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-calendar-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Monthly Reconciliation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-search w-3 h-3 mr-2 opacity-60"></i>
                        <span>Audit Reconciliation</span>
                    </a>
                </div> --}}

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('shop-operations-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-purple-50 rounded-lg p-1.5">
                            <i class="fas fa-store text-purple-600"></i>
                        </div>
                        <span class="font-medium">Shop Operations</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="shop-operations-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="shop-operations-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-clipboard-list w-3 h-3 mr-2 opacity-60"></i>
                        <span>Shop Inventory Count</span>
                    </a>
                    <!-- REPLACE existing incomplete shop-operations-dropdown with: -->
                    <div class="menu-dropdown bg-accent/10 rounded-lg" id="shop-operations-dropdown">
                        <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                            <i class="fas fa-warehouse w-3 h-3 mr-2 opacity-60"></i>
                            <span>Shop Inventory Management</span>
                        </a>
                        <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                            <i class="fas fa-clipboard-list w-3 h-3 mr-2 opacity-60"></i>
                            <span>Daily Inventory Count</span>
                        </a>
                        <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                            <i class="fas fa-exclamation-triangle w-3 h-3 mr-2 opacity-60"></i>
                            <span>Shop Variance Analysis</span>
                        </a>
                    </div>
                </div> --}}
            </div>

            <!-- 3. INVENTORY MANAGEMENT -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-cyan-500 to-cyan-600">Inventory</span>
                        Inventory Management
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('deliveries-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-cyan-50 rounded-lg p-1.5">
                            <i class="fas fa-truck text-cyan-600"></i>
                        </div>
                        <span class="font-medium">Deliveries</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-innovative bg-red-100 text-red-700 border border-red-200">3</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="deliveries-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="deliveries-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-cyan-600"></i>
                        <span>New Delivery Entry</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-list w-3 h-3 mr-2 opacity-60"></i>
                        <span>Deliveries</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-clipboard-check w-3 h-3 mr-2 opacity-60"></i>
                        <span>Delivery Receiving</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-receipt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Delivery Receipts</span>
                    </a>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('inventory-tracking-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-indigo-50 rounded-lg p-1.5">
                            <i class="fas fa-boxes text-indigo-600"></i>
                        </div>
                        <span class="font-medium">Inventory Tracking</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="inventory-tracking-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="inventory-tracking-dropdown">
                    <a href="{{ route('inventory.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-layer-group w-3 h-3 mr-2 opacity-60"></i>
                        <span>Inventory </span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exchange-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Inventory Movements</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-calculator w-3 h-3 mr-2 opacity-60"></i>
                        <span>Inventory Valuation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-pie w-3 h-3 mr-2 opacity-60"></i>
                        <span>Consumption Analysis</span>
                    </a>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('shop-inventory-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-pink-50 rounded-lg p-1.5">
                            <i class="fas fa-shopping-cart text-pink-600"></i>
                        </div>
                        <span class="font-medium">Shop Inventory</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="shop-inventory-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="shop-inventory-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-warehouse w-3 h-3 mr-2 opacity-60"></i>
                        <span>Shop Inventory</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exclamation-triangle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Shop Variance</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-line w-3 h-3 mr-2 opacity-60"></i>
                        <span>Shop Performance</span>
                    </a>
                </div>
            </div> --}}

            <!-- 4. VARIANCE CONTROL -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-orange-500 to-orange-600">Control</span>
                        Variance Control
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('variances-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-orange-50 rounded-lg p-1.5">
                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        </div>
                        <span class="font-medium">Variance Management</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-innovative bg-red-100 text-red-700 border border-red-200">2</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="variances-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="variances-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Dashboard</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-search w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Analysis</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-microscope w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Investigation</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-area w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Patterns</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-comment-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Manager Explanations</span>
                    </a>
                </div>
            </div> --}}

            {{-- <!-- 5. APPROVALS -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-green-500 to-green-600">Approval</span>
                        Approval Workflow
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('approvals-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-green-50 rounded-lg p-1.5">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <span class="font-medium">Approvals</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-innovative bg-red-100 text-red-700 border border-red-200">5</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="approvals-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="approvals-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-list-ul w-3 h-3 mr-2 opacity-60"></i>
                        <span>Approval Queue</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-thumbs-up w-3 h-3 mr-2 opacity-60"></i>
                        <span>Approve Variances</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-thumbs-down w-3 h-3 mr-2 opacity-60"></i>
                        <span>Reject Variances</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-user-friends w-3 h-3 mr-2 opacity-60"></i>
                        <span>Delegate Approval</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>Approval History</span>
                    </a>
                </div>
            </div> --}}

            <!-- 6. PRICE MANAGEMENT -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-violet-500 to-violet-600">Pricing</span>
                        Price Management
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('pricing-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-violet-50 rounded-lg p-1.5">
                            <i class="fas fa-tags text-violet-600"></i>
                        </div>
                        <span class="font-medium">Pricing Control</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="pricing-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="pricing-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Price Dashboard</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Price Changes</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>Price History</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-line w-3 h-3 mr-2 opacity-60"></i>
                        <span>Margin Analysis</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-balance-scale w-3 h-3 mr-2 opacity-60"></i>
                        <span>Market Comparison</span>
                    </a>
                </div>
            </div> --}}

            <!-- 7. PROCUREMENT -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-teal-500 to-teal-600">Supply</span>
                        Procurement
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('suppliers-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-teal-50 rounded-lg p-1.5">
                            <i class="fas fa-handshake text-teal-600"></i>
                        </div>
                        <span class="font-medium">Supplier Management</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="suppliers-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="suppliers-dropdown">
                    <a href="{{ route('suppliers.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-teal-600"></i>
                        <span> Suppliers</span>
                    </a>
                    <a href="{{ route('contracts.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-building w-3 h-3 mr-2 opacity-60"></i>
                        <span>Suppliers Contracts</span>
                    </a>

                      <a href="{{ route('purchase-orders.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-building w-3 h-3 mr-2 opacity-60"></i>
                        <span>Purchase Orders</span>
                    </a>

                    <a href="{{ route('deliveries.index') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-building w-3 h-3 mr-2 opacity-60"></i>
                        <span>Delievries </span>
                    </a>
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Edit Suppliers</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-file-contract w-3 h-3 mr-2 opacity-60"></i>
                        <span>Supplier Contracts</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-bar w-3 h-3 mr-2 opacity-60"></i>
                        <span>Supplier Performance</span>
                    </a>

                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-chart-bar w-3 h-3 mr-2 opacity-60"></i>
                        <span>Purchase orders</span>
                    </a> --}}
                </div>

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('procurement-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-blue-50 rounded-lg p-1.5">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                        <span class="font-medium">Purchase Orders</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-count bg-blue-100 text-blue-700 border border-blue-200">3</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="procurement-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="procurement-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-file-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Purchase Orders</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-check w-3 h-3 mr-2 opacity-60"></i>
                        <span>PO Approvals</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-map-marker-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Delivery Tracking</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-file-invoice w-3 h-3 mr-2 opacity-60"></i>
                        <span>Invoices</span>
                    </a>
                </div> --}}
            </div>
            <!-- ADD AFTER System Administration section -->
            <!-- 12. DATA INTEGRITY & FORENSICS -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-red-500 to-red-600">Security</span>
                        Data Integrity & Forensics
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('data-integrity-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-red-50 rounded-lg p-1.5">
                            <i class="fas fa-shield-alt text-red-600"></i>
                        </div>
                        <span class="font-medium">Data Integrity</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="data-integrity-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="data-integrity-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-link w-3 h-3 mr-2 opacity-60"></i>
                        <span>Hash Chain Verification</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exclamation-triangle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Tamper Detection</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-search w-3 h-3 mr-2 opacity-60"></i>
                        <span>Data Integrity Audit</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>Hash Chain History</span>
                    </a>
                </div>
            </div> --}}
            <!-- ADD AFTER Data Integrity section -->
            <!-- 13. NOTIFICATION CENTER -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-yellow-500 to-yellow-600">Alerts</span>
                        Notification Center
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('notifications-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-yellow-50 rounded-lg p-1.5">
                            <i class="fas fa-bell text-yellow-600"></i>
                        </div>
                        <span class="font-medium">Notifications</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-innovative bg-red-100 text-red-700 border border-red-200">8</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="notifications-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="notifications-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-inbox w-3 h-3 mr-2 opacity-60"></i>
                        <span>Notification Inbox</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exclamation-circle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Alert Management</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-cog w-3 h-3 mr-2 opacity-60"></i>
                        <span>Notification Preferences</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>Notification History</span>
                    </a>
                </div>
            </div> --}}
            <!-- 8. STATION MANAGEMENT -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-slate-500 to-slate-600">Setup</span>
                        Station Management
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('stations-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-slate-50 rounded-lg p-1.5">
                            <i class="fas fa-gas-pump text-slate-600"></i>
                        </div>
                        <span class="font-medium">Station Operations</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="stations-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="stations-dropdown">
                    <a href="{{ route('stations.index') }}"
                        class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-slate-600"></i>
                        <span>Add New Station</span>
                    </a>
                    {{-- <a href="{{ url('/stations') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-list w-3 h-3 mr-2 opacity-60"></i>
                        <span>Stations</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Edit Stations</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Station Dashboard</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-heartbeat w-3 h-3 mr-2 opacity-60"></i>
                        <span>Station Status</span>
                    </a> --}}
                </div>

                {{-- <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('equipment-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-gray-50 rounded-lg p-1.5">
                            <i class="fas fa-cogs text-gray-600"></i>
                        </div>
                        <span class="font-medium">Equipment Management</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="equipment-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="equipment-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-gray-600"></i>
                        <span>Add New Tank</span>
                    </a>
                    <a href="{{ route('tanks.select') }}"
                        class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-oil-can w-3 h-3 mr-2 opacity-60"></i>
                        <span>Tanks</span>
                    </a>


                    <a href="{{ route('pumps.select') }}"
                        class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Pumps</span>
                    </a>

                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-wrench w-3 h-3 mr-2 opacity-60"></i>
                        <span>Pump Maintenance</span>
                    </a>
                </div> --}}
            </div>



            <!-- 10. USER MANAGEMENT -->
            <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-indigo-500 to-indigo-600">Admin</span>
                        User Management
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('users-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-indigo-50 rounded-lg p-1.5">
                            <i class="fas fa-users text-indigo-600"></i>
                        </div>
                        <span class="font-medium">User Administration</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge-count bg-indigo-100 text-indigo-700 border border-indigo-200">12</span>
                        <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                            id="users-dropdown-arrow"></i>
                    </div>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="users-dropdown">
                    <a href="{{ route('users.index') }}"
                        class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-plus w-3 h-3 mr-2 opacity-60 text-indigo-600"></i>
                        <span>Manage Users</span>
                    </a>
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-user-friends w-3 h-3 mr-2 opacity-60"></i>
                        <span>Users</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Edit Users</span>
                    </a> --}}
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-key w-3 h-3 mr-2 opacity-60"></i>
                        <span>User Permissions</span>
                    </a> --}}
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-map-marker-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Station Assignments</span>
                    </a> --}}
                    {{-- <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-history w-3 h-3 mr-2 opacity-60"></i>
                        <span>User Activity</span>
                    </a>

                    <!-- ADD these items to existing users-dropdown -->
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-lock w-3 h-3 mr-2 opacity-60"></i>
                        <span>Account Lockout Management</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-key w-3 h-3 mr-2 opacity-60"></i>
                        <span>Password Reset Workflow</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-desktop w-3 h-3 mr-2 opacity-60"></i>
                        <span>Session Management</span>
                    </a> --}}
                </div>
            </div>

            <!-- 11. SYSTEM ADMINISTRATION -->
            {{-- <div class="menu-section">
                <div class="mb-3 px-2">
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center">
                        <span class="phase-badge mr-2 bg-gradient-to-r from-gray-500 to-gray-600">System</span>
                        System Administration
                    </span>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('system-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-gray-50 rounded-lg p-1.5">
                            <i class="fas fa-cog text-gray-600"></i>
                        </div>
                        <span class="font-medium">System Management</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="system-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="system-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tachometer-alt w-3 h-3 mr-2 opacity-60"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    <a href="{{ url('/audit') }}" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-search w-3 h-3 mr-2 opacity-60"></i>
                        <span>System Audit</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-database w-3 h-3 mr-2 opacity-60"></i>
                        <span>Backup Management</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-tools w-3 h-3 mr-2 opacity-60"></i>
                        <span>System Maintenance</span>
                    </a>
                </div>

                <div class="menu-section-header hover:bg-accent/50 transition-all"
                    onclick="toggleInnovativeDropdown('config-dropdown')">
                    <div class="flex items-center space-x-3">
                        <div class="menu-icon bg-blue-50 rounded-lg p-1.5">
                            <i class="fas fa-sliders-h text-blue-600"></i>
                        </div>
                        <span class="font-medium">System Configuration</span>
                    </div>
                    <i class="fas fa-chevron-right menu-arrow transition-transform duration-200"
                        id="config-dropdown-arrow"></i>
                </div>
                <div class="menu-dropdown bg-accent/10 rounded-lg" id="config-dropdown">
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-business-time w-3 h-3 mr-2 opacity-60"></i>
                        <span>Business Rules</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-exclamation-triangle w-3 h-3 mr-2 opacity-60"></i>
                        <span>Variance Thresholds</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-bell w-3 h-3 mr-2 opacity-60"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-clock w-3 h-3 mr-2 opacity-60"></i>
                        <span>Time Windows</span>
                    </a>
                    <a href="#" class="menu-dropdown-item hover:bg-accent/30 transition-colors">
                        <i class="fas fa-edit w-3 h-3 mr-2 opacity-60"></i>
                        <span>Data Corrections</span>
                    </a>
                </div>
            </div> --}}
        </nav>
    </div>
</div>
