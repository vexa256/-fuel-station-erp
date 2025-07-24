@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card/50 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('stations.index') }}"
                        class="btn btn-ghost p-2 hover:bg-accent rounded-lg transition-colors">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="text-2xl font-bold text-foreground">{{ $station->station_name }}</h1>
                            <span class="badge badge-outline text-xs">{{ $station->station_code }}</span>
                            <div id="stationStatus" class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" id="statusIndicator"></div>
                                <span class="text-sm font-medium text-green-700" id="statusText">Loading...</span>

                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                            <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $station->district }}, {{
                                $station->region }}</span>
                            <span><i class="fas fa-clock mr-1"></i>{{ date('H:i',
                                strtotime($station->operating_hours_open)) }} - {{ date('H:i',
                                strtotime($station->operating_hours_close)) }}</span>
                            <span id="lastUpdated" class="text-xs">Last updated: <span
                                    id="lastUpdatedTime">Loading...</span></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <button onclick="refreshDashboard()"
                            class="btn btn-ghost btn-sm p-2 hover:bg-accent rounded-lg transition-colors"
                            id="refreshBtn">
                            <i class="fas fa-sync-alt text-sm" id="refreshIcon"></i>
                        </button>
                        <button onclick="toggleAutoRefresh()" class="btn btn-ghost btn-sm px-3 py-2 text-xs"
                            id="autoRefreshBtn">
                            <i class="fas fa-play mr-1"></i>Auto
                        </button>
                    </div>

                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) ||
                    DB::table('user_stations')->where('user_id', auth()->id())->where('station_id',
                    $station->id)->exists())
                    <div class="flex items-center gap-2">
                        <a href="{{ route('stations.edit', $station->id) }}"
                            class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                            <i class="fas fa-edit text-sm"></i>
                            <span>Edit Station</span>
                        </a>

                        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                        <div class="dropdown dropdown-end">
                            <button class="btn btn-primary btn-sm inline-flex items-center gap-2"
                                onclick="toggleDropdown('actionsDropdown')">
                                <i class="fas fa-cogs text-sm"></i>
                                <span>Manage</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="actionsDropdown"
                                class="dropdown-content hidden absolute right-0 mt-2 w-48 bg-card border border-border rounded-lg shadow-lg p-2 z-50">
                                <a href="#" class="block px-3 py-2 text-sm hover:bg-accent rounded-md">
                                    <i class="fas fa-oil-can w-4 mr-2"></i>Manage Tanks
                                </a>
                                <a href="#" class="block px-3 py-2 text-sm hover:bg-accent rounded-md">
                                    <i class="fas fa-tachometer-alt w-4 mr-2"></i>Manage Pumps
                                </a>
                                <a href="#" class="block px-3 py-2 text-sm hover:bg-accent rounded-md">
                                    <i class="fas fa-users w-4 mr-2"></i>Manage Users
                                </a>
                                <div class="border-t border-border my-1"></div>
                                <a href="#" class="block px-3 py-2 text-sm hover:bg-accent rounded-md">
                                    <i class="fas fa-chart-bar w-4 mr-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Panel -->
    <div id="alertsPanel" class="container mx-auto px-4 py-4" style="display: none;">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                    <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-800 mb-1">Station Alerts</h3>
                    <ul id="alertsList" class="text-sm text-red-700 space-y-1">
                        <!-- Alerts will be populated here -->
                    </ul>
                </div>
                <button onclick="dismissAlerts()" class="text-red-400 hover:text-red-600 p-1">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Fuel Stock -->
            <div class="card bg-card border border-border rounded-lg p-6 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-oil-can text-blue-600 text-lg"></i>
                    </div>
                    <button onclick="refreshMetric('fuel_stock')"
                        class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-foreground" id="fuelStock">{{
                            number_format($metrics['total_fuel_stock'] ?? 0, 0) }}</span>
                        <span class="text-sm text-muted-foreground mb-1">liters</span>
                    </div>
                    <div class="text-sm font-medium text-foreground">Total Fuel Stock</div>
                    <div class="text-xs text-muted-foreground">
                        Across {{ $metrics['tank_count'] ?? 0 }} tank{{ ($metrics['tank_count'] ?? 0) != 1 ? 's' : '' }}
                    </div>
                </div>
            </div>

            <!-- Today's Readings -->
            <div class="card bg-card border border-border rounded-lg p-6 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-green-600 text-lg"></i>
                    </div>
                    <button onclick="refreshMetric('readings')" class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-foreground" id="todayReadings">{{
                            $metrics['today_readings_count'] ?? 0 }}</span>
                        <span class="text-sm text-muted-foreground mb-1">entries</span>
                    </div>
                    <div class="text-sm font-medium text-foreground">Today's Readings</div>
                    <div class="text-xs text-muted-foreground">
                        Expected: {{ ($metrics['tank_count'] ?? 0) * 2 }} (morning + evening)
                    </div>
                </div>
            </div>

            <!-- Equipment Status -->
            <div class="card bg-card border border-border rounded-lg p-6 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cogs text-purple-600 text-lg"></i>
                    </div>
                    <button onclick="refreshMetric('equipment')"
                        class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-foreground" id="operationalPumps">{{
                            $metrics['operational_pumps'] ?? 0 }}</span>
                        <span class="text-sm text-muted-foreground mb-1">/ {{ $metrics['pump_count'] ?? 0 }}</span>
                    </div>
                    <div class="text-sm font-medium text-foreground">Operational Pumps</div>
                    <div class="text-xs" id="pumpStatusColor">
                        @if(($metrics['operational_pumps'] ?? 0) == ($metrics['pump_count'] ?? 0))
                        <span class="text-green-600">All systems operational</span>
                        @elseif(($metrics['operational_pumps'] ?? 0) > 0)
                        <span class="text-amber-600">Some pumps offline</span>
                        @else
                        <span class="text-red-600">No pumps operational</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pending Variances -->
            <div class="card bg-card border border-border rounded-lg p-6 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i>
                    </div>
                    <button onclick="refreshMetric('variances')"
                        class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-foreground" id="pendingVariances">{{
                            $metrics['pending_variances'] ?? 0 }}</span>
                        <span class="text-sm text-muted-foreground mb-1">pending</span>
                    </div>
                    <div class="text-sm font-medium text-foreground">Variances</div>
                    <div class="text-xs" id="varianceStatusColor">
                        @if(($metrics['pending_variances'] ?? 0) == 0)
                        <span class="text-green-600">No pending variances</span>
                        @elseif(($metrics['pending_variances'] ?? 0) <= 2) <span class="text-amber-600">Minor attention
                            needed</span>
                            @else
                            <span class="text-red-600">Urgent review required</span>
                            @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="card bg-card border border-border rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-foreground">Recent Activity</h3>
                    <button onclick="refreshActivity()" class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>

                <div class="space-y-4" id="recentActivity">
                    <!-- Activity items will be loaded here -->
                    <div class="flex items-start gap-3 p-3 bg-muted/30 rounded-lg">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-truck text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-foreground">Today's Deliveries</div>
                            <div class="text-xs text-muted-foreground">
                                {{ number_format($metrics['today_deliveries'] ?? 0, 0) }} liters received today
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                                {{ now()->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-muted/30 rounded-lg">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-green-600 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-foreground">Active Users</div>
                            <div class="text-xs text-muted-foreground">
                                {{ $metrics['active_users'] ?? 0 }} user{{ ($metrics['active_users'] ?? 0) != 1 ? 's' :
                                '' }} assigned to this station
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                                Current assignments
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-border">
                    <a href="#" class="text-sm text-primary hover:text-primary/80 font-medium">
                        View all activity →
                    </a>
                </div>
            </div>

            <!-- Equipment Overview -->
            <div class="card bg-card border border-border rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-foreground">Equipment Overview</h3>
                    <button onclick="refreshEquipment()" class="text-muted-foreground hover:text-foreground p-1">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>

                <div class="space-y-6" id="equipmentOverview">
                    <!-- Tanks Section -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-foreground">Fuel Tanks</h4>
                            <span class="text-xs text-muted-foreground">{{ $metrics['tank_count'] ?? 0 }} total</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            @for($i = 1; $i <= ($metrics['tank_count'] ?? 0); $i++) <div
                                class="flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-xs font-medium text-green-800">Tank {{ $i }}</span>
                                <span class="text-xs text-green-600 ml-auto">Active</span>
                        </div>
                        @endfor
                        @if(($metrics['tank_count'] ?? 0) == 0)
                        <div class="col-span-2 text-center py-4 text-sm text-muted-foreground">
                            No tanks configured
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Pumps Section -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-foreground">Fuel Pumps</h4>
                        <span class="text-xs text-muted-foreground">{{ $metrics['pump_count'] ?? 0 }} total</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        @for($i = 1; $i <= ($metrics['operational_pumps'] ?? 0); $i++) <div
                            class="flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-md">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-xs font-medium text-green-800">Pump {{ $i }}</span>
                            <span class="text-xs text-green-600 ml-auto">Online</span>
                    </div>
                    @endfor
                    @for($i = ($metrics['operational_pumps'] ?? 0) + 1; $i <= ($metrics['pump_count'] ?? 0); $i++) <div
                        class="flex items-center gap-2 p-2 bg-red-50 border border-red-200 rounded-md">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-xs font-medium text-red-800">Pump {{ $i }}</span>
                        <span class="text-xs text-red-600 ml-auto">Offline</span>
                </div>
                @endfor
                @if(($metrics['pump_count'] ?? 0) == 0)
                <div class="col-span-2 text-center py-4 text-sm text-muted-foreground">
                    No pumps configured
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-border">
        <a href="#" class="text-sm text-primary hover:text-primary/80 font-medium">
            Manage equipment →
        </a>
    </div>
</div>
</div>

<!-- Quick Actions Bar -->
<div class="mt-8 card bg-card border border-border rounded-lg p-6">
    <h3 class="text-lg font-semibold text-foreground mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="#"
            class="flex flex-col items-center gap-2 p-4 bg-muted/30 hover:bg-muted/50 rounded-lg transition-colors group">
            <div
                class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors">
                <i class="fas fa-plus text-blue-600 text-lg"></i>
            </div>
            <span class="text-sm font-medium text-foreground">New Reading</span>
        </a>

        <a href="#"
            class="flex flex-col items-center gap-2 p-4 bg-muted/30 hover:bg-muted/50 rounded-lg transition-colors group">
            <div
                class="w-12 h-12 bg-green-100 group-hover:bg-green-200 rounded-xl flex items-center justify-center transition-colors">
                <i class="fas fa-truck text-green-600 text-lg"></i>
            </div>
            <span class="text-sm font-medium text-foreground">Record Delivery</span>
        </a>

        <a href="#"
            class="flex flex-col items-center gap-2 p-4 bg-muted/30 hover:bg-muted/50 rounded-lg transition-colors group">
            <div
                class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center transition-colors">
                <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
            </div>
            <span class="text-sm font-medium text-foreground">View Reports</span>
        </a>

        <a href="#"
            class="flex flex-col items-center gap-2 p-4 bg-muted/30 hover:bg-muted/50 rounded-lg transition-colors group">
            <div
                class="w-12 h-12 bg-amber-100 group-hover:bg-amber-200 rounded-xl flex items-center justify-center transition-colors">
                <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i>
            </div>
            <span class="text-sm font-medium text-foreground">Review Variances</span>
        </a>
    </div>
</div>
</div>
</div>

<!-- Real-time Dashboard JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    let autoRefreshInterval;
    let isAutoRefreshEnabled = false;

    // Initial data load
    refreshDashboard();

    // Auto-refresh toggle
    window.toggleAutoRefresh = function() {
        const btn = document.getElementById('autoRefreshBtn');

        if (isAutoRefreshEnabled) {
            clearInterval(autoRefreshInterval);
            isAutoRefreshEnabled = false;
            btn.innerHTML = '<i class="fas fa-play mr-1"></i>Auto';
            btn.classList.remove('bg-primary', 'text-primary-foreground');
        } else {
            autoRefreshInterval = setInterval(refreshDashboard, 30000); // 30 seconds
            isAutoRefreshEnabled = true;
            btn.innerHTML = '<i class="fas fa-pause mr-1"></i>Auto';
            btn.classList.add('bg-primary', 'text-primary-foreground');
        }
    };

    // Manual refresh
    window.refreshDashboard = function() {
        const refreshIcon = document.getElementById('refreshIcon');
        refreshIcon.classList.add('fa-spin');

        fetch(`/api/stations/{{ $station->id }}/status`)
            .then(response => response.json())
            .then(data => {
                updateStationStatus(data);
                updateMetrics(data.metrics);
                updateLastUpdated();

                // Show alerts if any
                if (data.alerts && data.alerts.length > 0) {
                    showAlerts(data.alerts);
                } else {
                    hideAlerts();
                }
            })
            .catch(error => {
                console.error('Error fetching station status:', error);
                showError('Failed to update dashboard data');
            })
            .finally(() => {
                refreshIcon.classList.remove('fa-spin');
            });
    };

    // Update station status indicator
    function updateStationStatus(data) {
        const indicator = document.getElementById('statusIndicator');
        const statusText = document.getElementById('statusText');

        indicator.className = 'w-3 h-3 rounded-full animate-pulse';

        switch(data.overall_status) {
            case 'OPERATIONAL':
                indicator.classList.add('bg-green-500');
                statusText.textContent = 'Operational';
                statusText.className = 'text-sm font-medium text-green-700';
                break;
            case 'DEGRADED':
                indicator.classList.add('bg-amber-500');
                statusText.textContent = 'Degraded';
                statusText.className = 'text-sm font-medium text-amber-700';
                break;
            case 'INACTIVE':
                indicator.classList.add('bg-red-500');
                statusText.textContent = 'Inactive';
                statusText.className = 'text-sm font-medium text-red-700';
                break;
            default:
                indicator.classList.add('bg-gray-500');
                statusText.textContent = 'Unknown';
                statusText.className = 'text-sm font-medium text-gray-700';
        }
    }

    // Update metrics
    function updateMetrics(metrics) {
        document.getElementById('fuelStock').textContent = metrics.total_fuel_stock ?
            new Intl.NumberFormat().format(metrics.total_fuel_stock) : '0';
        document.getElementById('todayReadings').textContent = metrics.today_readings_count || '0';
        document.getElementById('operationalPumps').textContent = metrics.operational_pumps || '0';
        document.getElementById('pendingVariances').textContent = metrics.pending_variances || '0';

        // Update pump status color
        const pumpStatusElement = document.getElementById('pumpStatusColor');
        const operational = metrics.operational_pumps || 0;
        const total = metrics.pump_count || 0;

        if (operational === total && total > 0) {
            pumpStatusElement.innerHTML = '<span class="text-green-600">All systems operational</span>';
        } else if (operational > 0) {
            pumpStatusElement.innerHTML = '<span class="text-amber-600">Some pumps offline</span>';
        } else {
            pumpStatusElement.innerHTML = '<span class="text-red-600">No pumps operational</span>';
        }

        // Update variance status color
        const varianceStatusElement = document.getElementById('varianceStatusColor');
        const variances = metrics.pending_variances || 0;

        if (variances === 0) {
            varianceStatusElement.innerHTML = '<span class="text-green-600">No pending variances</span>';
        } else if (variances <= 2) {
            varianceStatusElement.innerHTML = '<span class="text-amber-600">Minor attention needed</span>';
        } else {
            varianceStatusElement.innerHTML = '<span class="text-red-600">Urgent review required</span>';
        }
    }

    // Show alerts
    function showAlerts(alerts) {
        const alertsPanel = document.getElementById('alertsPanel');
        const alertsList = document.getElementById('alertsList');

        alertsList.innerHTML = '';
        alerts.forEach(alert => {
            const li = document.createElement('li');
            li.textContent = alert;
            alertsList.appendChild(li);
        });

        alertsPanel.style.display = 'block';
    }

    // Hide alerts
    function hideAlerts() {
        document.getElementById('alertsPanel').style.display = 'none';
    }

    // Update last updated time
    function updateLastUpdated() {
        document.getElementById('lastUpdatedTime').textContent = new Date().toLocaleTimeString();
    }

    // Individual metric refresh
    window.refreshMetric = function(metric) {
        // This would call specific endpoints for individual metrics
        refreshDashboard(); // For now, refresh everything
    };

    // Activity refresh
    window.refreshActivity = function() {
        // This would refresh just the activity section
        console.log('Refreshing activity...');
    };

    // Equipment refresh
    window.refreshEquipment = function() {
        // This would refresh just the equipment section
        console.log('Refreshing equipment...');
    };

    // Dismiss alerts
    window.dismissAlerts = function() {
        hideAlerts();
    };

    // Toggle dropdown
    window.toggleDropdown = function(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        dropdown.classList.toggle('hidden');

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest(`#${dropdownId}`) && !event.target.closest('[onclick*="toggleDropdown"]')) {
                dropdown.classList.add('hidden');
            }
        });
    };

    // Show error message
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Dashboard Error',
            text: message,
            confirmButtonColor: 'hsl(var(--primary))'
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'r':
                    e.preventDefault();
                    refreshDashboard();
                    break;
                case ' ':
                    e.preventDefault();
                    toggleAutoRefresh();
                    break;
            }
        }
    });
});
</script>
@endsection
