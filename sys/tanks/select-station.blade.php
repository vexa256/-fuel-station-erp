@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg">
                            <i class="fas fa-oil-can text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-foreground">Tank Management</h1>
                            <p class="text-base text-muted-foreground mt-1">Select a station to manage fuel tanks and
                                inventory systems</p>
                        </div>
                    </div>
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                    <div class="flex items-center space-x-3">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-primary/10 text-primary border border-primary/30">
                            <i class="fas fa-crown w-4 h-4 mr-2"></i>
                            Full System Access
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    @if($stations->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 border-b border-border bg-muted/20">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-6">
            <!-- Search Input -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-muted-foreground text-sm"></i>
                    </div>
                    <input type="text" id="station-search"
                        class="block w-full pl-10 pr-3 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary text-sm transition-colors"
                        placeholder="Search stations by name or code...">
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Region Filter -->
                <div class="flex items-center space-x-2">
                    <label for="region-filter" class="text-sm font-medium text-foreground">Region:</label>
                    <select id="region-filter"
                        class="border border-border rounded-lg bg-background text-foreground px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                        <option value="">All Regions</option>
                        @foreach($stations->unique('region')->pluck('region')->sort() as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-foreground">Status:</span>
                    <div class="flex space-x-1">
                        <button type="button" data-status="all"
                            class="status-filter-btn active px-3 py-1.5 rounded-md text-sm font-medium transition-colors bg-primary text-primary-foreground">
                            All
                        </button>
                        <button type="button" data-status="operational"
                            class="status-filter-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors bg-muted text-muted-foreground hover:bg-green-100 hover:text-green-700">
                            Operational
                        </button>
                        <button type="button" data-status="partial"
                            class="status-filter-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors bg-muted text-muted-foreground hover:bg-amber-100 hover:text-amber-700">
                            Partial
                        </button>
                        <button type="button" data-status="setup"
                            class="status-filter-btn px-3 py-1.5 rounded-md text-sm font-medium transition-colors bg-muted text-muted-foreground hover:bg-red-100 hover:text-red-700">
                            Setup Required
                        </button>
                    </div>
                </div>

                <!-- Clear Filters -->
                <button type="button" id="clear-filters"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground border border-border rounded-lg hover:bg-muted transition-colors">
                    <i class="fas fa-times w-3 h-3 mr-2"></i>
                    Clear
                </button>
            </div>
        </div>

        <!-- Results Counter -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
                Showing <span id="filtered-count">{{ $stations->count() }}</span> of <span id="total-count">{{
                    $stations->count() }}</span> stations
            </div>
            <div id="active-filters" class="flex items-center space-x-2"></div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($stations->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden lg:block">
            <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border bg-muted/20">
                    <h3 class="text-lg font-semibold text-foreground">Available Stations</h3>
                    <p class="text-sm text-muted-foreground mt-1">Select a station to access tank management and
                        inventory controls</p>
                </div>

                <table class="w-full">
                    <thead class="bg-muted/30">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-foreground w-1/3">
                                Station Information
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-foreground w-1/4">
                                Location
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-sm font-semibold text-foreground w-1/6">
                                Tank Status
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-sm font-semibold text-foreground w-1/6">
                                Operations
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-sm font-semibold text-foreground w-1/12">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($stations as $station)
                        <tr class="hover:bg-muted/10 transition-colors group station-row"
                            data-station-name="{{ strtolower($station->station_name) }}"
                            data-station-code="{{ strtolower($station->station_code) }}"
                            data-region="{{ $station->region }}" data-district="{{ $station->district }}"
                            data-tank-count="{{ $station->tank_count }}"
                            data-active-tanks="{{ $station->active_tanks }}"
                            data-status="{{ $station->active_tanks === $station->tank_count && $station->tank_count > 0 ? 'operational' : ($station->active_tanks > 0 ? 'partial' : 'setup') }}">
                            <!-- Station Information -->
                            <td class="px-6 py-6">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-100 to-cyan-200 border border-cyan-300">
                                        <i class="fas fa-gas-pump text-cyan-700 text-lg"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-lg font-semibold text-foreground leading-tight">
                                            {{ $station->station_name }}
                                        </h4>
                                        <p class="text-sm text-muted-foreground mt-1 font-mono">
                                            {{ $station->station_code }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Location -->
                            <td class="px-6 py-6">
                                <div class="space-y-1">
                                    <div class="flex items-center text-sm text-foreground">
                                        <i class="fas fa-map-marker-alt w-4 h-4 mr-2 text-muted-foreground"></i>
                                        <span class="font-medium">{{ $station->region }}</span>
                                    </div>
                                    <div class="text-sm text-muted-foreground ml-6">
                                        {{ $station->district }}
                                    </div>
                                </div>
                            </td>

                            <!-- Tank Status -->
                            <td class="px-6 py-6 text-center">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-center space-x-6">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-foreground">{{ $station->tank_count }}
                                            </div>
                                            <div class="text-xs text-muted-foreground uppercase tracking-wide">Total
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600">{{ $station->active_tanks }}
                                            </div>
                                            <div class="text-xs text-muted-foreground uppercase tracking-wide">Active
                                            </div>
                                        </div>
                                    </div>

                                    @if($station->active_tanks === $station->tank_count && $station->tank_count > 0)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                        Fully Operational
                                    </span>
                                    @elseif($station->active_tanks > 0)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                                        <div class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5"></div>
                                        Partial Operations
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                        Setup Required
                                    </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Operations Status -->
                            <td class="px-6 py-6 text-center">
                                @if($station->tank_count > 0)
                                <div
                                    class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 border border-blue-200">
                                    <i class="fas fa-chart-line w-4 h-4 text-blue-600 mr-2"></i>
                                    <span class="text-sm font-medium text-blue-700">Ready</span>
                                </div>
                                @else
                                <div
                                    class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">
                                    <i class="fas fa-tools w-4 h-4 text-gray-600 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-700">Setup</span>
                                </div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-6 text-right">
                                <a href="{{ route('tanks.index', $station->id) }}"
                                    class="inline-flex items-center px-4 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-sm transition-all duration-200 shadow-sm group-hover:shadow-md">
                                    <i class="fas fa-arrow-right w-4 h-4 ml-2"></i>
                                    <span>Manage</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile/Tablet Card View -->
        <div class="lg:hidden space-y-4">
            @foreach($stations as $station)
            <div class="bg-card border border-border rounded-xl p-6 hover:border-primary/30 hover:shadow-lg transition-all duration-200 station-card"
                data-station-name="{{ strtolower($station->station_name) }}"
                data-station-code="{{ strtolower($station->station_code) }}" data-region="{{ $station->region }}"
                data-district="{{ $station->district }}" data-tank-count="{{ $station->tank_count }}"
                data-active-tanks="{{ $station->active_tanks }}"
                data-status="{{ $station->active_tanks === $station->tank_count && $station->tank_count > 0 ? 'operational' : ($station->active_tanks > 0 ? 'partial' : 'setup') }}">
                <!-- Station Header -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-100 to-cyan-200 border border-cyan-300 flex-shrink-0">
                            <i class="fas fa-gas-pump text-cyan-700 text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xl font-bold text-foreground leading-tight">
                                {{ $station->station_name }}
                            </h3>
                            <p class="text-sm text-muted-foreground mt-1 font-mono">
                                {{ $station->station_code }}
                            </p>
                        </div>
                    </div>

                    @if($station->active_tanks === $station->tank_count && $station->tank_count > 0)
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200 flex-shrink-0">
                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                        Operational
                    </span>
                    @elseif($station->active_tanks > 0)
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200 flex-shrink-0">
                        <div class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5"></div>
                        Partial
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200 flex-shrink-0">
                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                        Setup
                    </span>
                    @endif
                </div>

                <!-- Location Info -->
                <div class="mb-6">
                    <div class="flex items-center text-sm text-muted-foreground">
                        <i class="fas fa-map-marker-alt w-4 h-4 mr-3 opacity-60"></i>
                        <span>{{ $station->region }} • {{ $station->district }}</span>
                    </div>
                </div>

                <!-- Tank Metrics -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="text-center py-4 bg-muted/20 rounded-lg border border-border/50">
                        <div class="text-2xl font-bold text-foreground">{{ $station->tank_count }}</div>
                        <div class="text-sm text-muted-foreground mt-1">Total Tanks</div>
                    </div>
                    <div class="text-center py-4 bg-muted/20 rounded-lg border border-border/50">
                        <div class="text-2xl font-bold text-green-600">{{ $station->active_tanks }}</div>
                        <div class="text-sm text-muted-foreground mt-1">Active Tanks</div>
                    </div>
                </div>

                <!-- Action Button -->
                <a href="{{ route('tanks.index', $station->id) }}"
                    class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-base transition-all duration-200 shadow-sm">
                    <span>Manage Tank Operations</span>
                    <i class="fas fa-arrow-right w-4 h-4 ml-3"></i>
                </a>
            </div>
            @endforeach
        </div>

        <!-- System Overview for Administrators -->
        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
        <div class="mt-12 bg-gradient-to-r from-primary/5 to-primary/10 border border-primary/20 rounded-xl p-8">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-foreground">System Overview</h3>
                <p class="text-sm text-muted-foreground mt-1">Enterprise-wide tank management statistics</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-3xl font-bold text-foreground">{{ $stations->count() }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Active Stations</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600">{{ $stations->sum('tank_count') }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Total Tank Infrastructure</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stations->sum('active_tanks') }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Operational Tanks</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">
                        @if($stations->sum('tank_count') > 0)
                        {{ number_format(($stations->sum('active_tanks') / $stations->sum('tank_count')) * 100, 1) }}%
                        @else
                        0%
                        @endif
                    </div>
                    <div class="text-sm text-muted-foreground mt-1">System Availability</div>
                </div>
            </div>
        </div>
        @endif
        @else
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-muted/50 mb-6">
                <i class="fas fa-gas-pump text-3xl text-muted-foreground"></i>
            </div>
            <h3 class="text-2xl font-bold text-foreground mb-4">No Stations Available</h3>

            @if(isset($hasAccess) && $hasAccess === false)
            <p class="text-base text-muted-foreground max-w-lg mx-auto mb-8">
                You don't have access to any fuel stations. Contact your system administrator to assign stations to your
                account for tank management access.
            </p>
            <div class="inline-flex items-center px-4 py-2 bg-muted text-muted-foreground rounded-lg text-sm">
                <i class="fas fa-user-lock w-4 h-4 mr-2"></i>
                Access Restricted
            </div>
            @else
            <p class="text-base text-muted-foreground max-w-lg mx-auto mb-8">
                No fuel stations are currently configured in the system. Create your first station to begin tank
                management operations.
            </p>
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
            <div class="space-y-4">
                <a href="{{ route('stations.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-base transition-colors shadow-sm">
                    <i class="fas fa-plus w-4 h-4 mr-2"></i>
                    Create First Station
                </a>
                <p class="text-sm text-muted-foreground">
                    Set up station infrastructure before configuring tanks
                </p>
            </div>
            @endif
            @endif
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Filter elements
    const searchInput = document.getElementById('station-search');
    const regionFilter = document.getElementById('region-filter');
    const statusButtons = document.querySelectorAll('.status-filter-btn');
    const clearButton = document.getElementById('clear-filters');
    const filteredCountEl = document.getElementById('filtered-count');
    const totalCountEl = document.getElementById('total-count');
    const activeFiltersEl = document.getElementById('active-filters');

    // All station elements
    const stationRows = document.querySelectorAll('.station-row');
    const stationCards = document.querySelectorAll('.station-card');
    const allStations = [...stationRows, ...stationCards];

    let currentFilters = {
        search: '',
        region: '',
        status: 'all'
    };

    let searchTimeout;

    // Initialize
    updateResultCount();

    // Search input with debouncing
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentFilters.search = e.target.value.toLowerCase().trim();
                applyFilters();
                updateActiveFilters();
            }, 300);
        });
    }

    // Region filter
    if (regionFilter) {
        regionFilter.addEventListener('change', function(e) {
            currentFilters.region = e.target.value;
            applyFilters();
            updateActiveFilters();
        });
    }

    // Status filter buttons
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            statusButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-primary-foreground');
                btn.classList.add('bg-muted', 'text-muted-foreground');
            });

            // Add active class to clicked button
            this.classList.add('active', 'bg-primary', 'text-primary-foreground');
            this.classList.remove('bg-muted', 'text-muted-foreground');

            currentFilters.status = this.getAttribute('data-status');
            applyFilters();
            updateActiveFilters();
        });
    });

    // Clear filters
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            // Reset all filters
            currentFilters = {
                search: '',
                region: '',
                status: 'all'
            };

            // Reset UI elements
            if (searchInput) searchInput.value = '';
            if (regionFilter) regionFilter.value = '';

            // Reset status buttons
            statusButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-primary-foreground');
                btn.classList.add('bg-muted', 'text-muted-foreground');
                if (btn.getAttribute('data-status') === 'all') {
                    btn.classList.add('active', 'bg-primary', 'text-primary-foreground');
                    btn.classList.remove('bg-muted', 'text-muted-foreground');
                }
            });

            applyFilters();
            updateActiveFilters();
        });
    }

    function applyFilters() {
        let visibleCount = 0;

        allStations.forEach(station => {
            let isVisible = true;

            // Search filter
            if (currentFilters.search) {
                const stationName = station.getAttribute('data-station-name') || '';
                const stationCode = station.getAttribute('data-station-code') || '';
                const searchMatch = stationName.includes(currentFilters.search) ||
                                  stationCode.includes(currentFilters.search);
                if (!searchMatch) isVisible = false;
            }

            // Region filter
            if (currentFilters.region) {
                const stationRegion = station.getAttribute('data-region') || '';
                if (stationRegion !== currentFilters.region) isVisible = false;
            }

            // Status filter
            if (currentFilters.status !== 'all') {
                const stationStatus = station.getAttribute('data-status') || '';
                if (stationStatus !== currentFilters.status) isVisible = false;
            }

            // Show/hide station
            if (isVisible) {
                station.style.display = '';
                visibleCount++;
            } else {
                station.style.display = 'none';
            }
        });

        updateResultCount(visibleCount);

        // Show/hide empty state for filtered results
        const hasVisibleStations = visibleCount > 0;
        const emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = hasVisibleStations ? 'none' : 'block';
        }

        // Show filtered empty message if no results
        if (!hasVisibleStations && allStations.length > 0) {
            showFilteredEmptyState();
        } else {
            hideFilteredEmptyState();
        }
    }

    function updateResultCount(filteredCount = null) {
        const totalCount = allStations.length;
        const showing = filteredCount !== null ? filteredCount : totalCount;

        if (filteredCountEl) filteredCountEl.textContent = showing;
        if (totalCountEl) totalCountEl.textContent = totalCount;
    }

    function updateActiveFilters() {
        if (!activeFiltersEl) return;

        const activeFilters = [];

        if (currentFilters.search) {
            activeFilters.push({
                label: `Search: "${currentFilters.search}"`,
                type: 'search'
            });
        }

        if (currentFilters.region) {
            activeFilters.push({
                label: `Region: ${currentFilters.region}`,
                type: 'region'
            });
        }

        if (currentFilters.status !== 'all') {
            const statusLabels = {
                'operational': 'Operational',
                'partial': 'Partial',
                'setup': 'Setup Required'
            };
            activeFilters.push({
                label: `Status: ${statusLabels[currentFilters.status]}`,
                type: 'status'
            });
        }

        // Clear existing active filters
        activeFiltersEl.innerHTML = '';

        if (activeFilters.length > 0) {
            activeFilters.forEach(filter => {
                const filterTag = document.createElement('span');
                filterTag.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20';
                filterTag.innerHTML = `
                    ${filter.label}
                    <button type="button" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary/20 transition-colors" onclick="removeFilter('${filter.type}')">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                activeFiltersEl.appendChild(filterTag);
            });
        }
    }

    function showFilteredEmptyState() {
        let emptyStateEl = document.getElementById('filtered-empty-state');
        if (!emptyStateEl) {
            emptyStateEl = document.createElement('div');
            emptyStateEl.id = 'filtered-empty-state';
            emptyStateEl.className = 'text-center py-16';
            emptyStateEl.innerHTML = `
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 mb-4">
                    <i class="fas fa-search text-2xl text-muted-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground mb-2">No Stations Found</h3>
                <p class="text-sm text-muted-foreground max-w-md mx-auto">
                    No stations match your current filters. Try adjusting your search criteria or clearing filters.
                </p>
                <button type="button" onclick="clearAllFilters()" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-primary hover:text-primary/80 transition-colors">
                    <i class="fas fa-times w-4 h-4 mr-2"></i>
                    Clear All Filters
                </button>
            `;

            // Insert after the main content div
            const mainContent = document.querySelector('.max-w-7xl > div');
            if (mainContent) {
                mainContent.parentNode.insertBefore(emptyStateEl, mainContent.nextSibling);
            }
        }
        emptyStateEl.style.display = 'block';
    }

    function hideFilteredEmptyState() {
        const emptyStateEl = document.getElementById('filtered-empty-state');
        if (emptyStateEl) {
            emptyStateEl.style.display = 'none';
        }
    }

    // Global functions for inline event handlers
    window.removeFilter = function(filterType) {
        switch(filterType) {
            case 'search':
                currentFilters.search = '';
                if (searchInput) searchInput.value = '';
                break;
            case 'region':
                currentFilters.region = '';
                if (regionFilter) regionFilter.value = '';
                break;
            case 'status':
                currentFilters.status = 'all';
                statusButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-primary', 'text-primary-foreground');
                    btn.classList.add('bg-muted', 'text-muted-foreground');
                    if (btn.getAttribute('data-status') === 'all') {
                        btn.classList.add('active', 'bg-primary', 'text-primary-foreground');
                        btn.classList.remove('bg-muted', 'text-muted-foreground');
                    }
                });
                break;
        }
        applyFilters();
        updateActiveFilters();
    };

    window.clearAllFilters = function() {
        if (clearButton) {
            clearButton.click();
        }
    };
});
</script>
@endsection
