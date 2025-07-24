@extends('layouts.app')

@section('title', 'Select Station - Pump Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pump Management</h1>
                    <p class="mt-2 text-gray-600">Select a station to manage pumps and equipment</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                        <span class="text-sm text-gray-500">Total Stations</span>
                        <div class="text-2xl font-bold text-blue-600">{{ $stations->count() }}</div>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                        <span class="text-sm text-gray-500">Total Pumps</span>
                        <div class="text-2xl font-bold text-green-600">{{ $stations->sum('pump_count') }}</div>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                        <span class="text-sm text-gray-500">Operational</span>
                        <div class="text-2xl font-bold text-emerald-600">{{ $stations->sum('operational_pumps') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="station-search" class="sr-only">Search stations</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="station-search"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Search stations by name, code, or location...">
                        </div>
                    </div>
                    <div class="sm:w-48">
                        <select id="region-filter"
                            class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Regions</option>
                            @foreach($stations->pluck('region')->unique()->sort() as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:w-48">
                        <select id="status-filter"
                            class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="operational">Fully Operational</option>
                            <option value="partial">Partially Operational</option>
                            <option value="maintenance">Needs Attention</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stations Grid -->
        <div id="stations-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($stations as $station)
            @php
            $operationalPercentage = $station->pump_count > 0 ? round(($station->operational_pumps /
            $station->pump_count) * 100) : 0;
            $statusClass = $operationalPercentage == 100 ? 'border-green-200 bg-green-50' : ($operationalPercentage >=
            80 ? 'border-yellow-200 bg-yellow-50' : 'border-red-200 bg-red-50');
            $statusIcon = $operationalPercentage == 100 ? 'text-green-500' : ($operationalPercentage >= 80 ?
            'text-yellow-500' : 'text-red-500');
            @endphp
            <div class="station-card bg-white rounded-lg shadow-sm border-2 {{ $statusClass }} hover:shadow-lg transition-all duration-200 cursor-pointer transform hover:-translate-y-1"
                data-station-name="{{ strtolower($station->station_name) }}"
                data-station-code="{{ strtolower($station->station_code) }}"
                data-region="{{ strtolower($station->region) }}" data-district="{{ strtolower($station->district) }}"
                data-operational-percentage="{{ $operationalPercentage }}"
                onclick="window.location.href='{{ route('pumps.index', $station->id) }}'">

                <!-- Station Header -->
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $station->station_name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $station->station_code }}</p>
                        </div>
                        <div class="flex-shrink-0 ml-3">
                            <div
                                class="w-3 h-3 rounded-full {{ $statusIcon == 'text-green-500' ? 'bg-green-400' : ($statusIcon == 'text-yellow-500' ? 'bg-yellow-400' : 'bg-red-400') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mt-3 flex items-center text-sm text-gray-600">
                        <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $station->district }}, {{ $station->region }}</span>
                    </div>
                </div>

                <!-- Pump Statistics -->
                <div class="px-6 pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $station->pump_count }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Total Pumps</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold {{ $statusIcon }}">{{ $station->operational_pumps }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Operational</div>
                        </div>
                    </div>
                </div>

                <!-- Status Bar -->
                <div class="px-6 pb-6">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $operationalPercentage == 100 ? 'bg-green-500' : ($operationalPercentage >= 80 ? 'bg-yellow-500' : 'bg-red-500') }}"
                            style="width: {{ $operationalPercentage }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                        <span>{{ $operationalPercentage }}% Operational</span>
                        @if($operationalPercentage < 100) <span class="text-red-600">{{ $station->pump_count -
                            $station->operational_pumps }} Down</span>
                            @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="border-t border-gray-100 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Manage Pumps</span>
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No stations available</h3>
                    <p class="mt-2 text-gray-500">Contact your administrator to set up station access.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- No Results Message -->
        <div id="no-results" class="hidden col-span-full">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No stations found</h3>
                <p class="mt-2 text-gray-500">Try adjusting your search criteria or filters.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('station-search');
    const regionFilter = document.getElementById('region-filter');
    const statusFilter = document.getElementById('status-filter');
    const stationCards = document.querySelectorAll('.station-card');
    const noResults = document.getElementById('no-results');
    const stationsGrid = document.getElementById('stations-grid');

    function filterStations() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRegion = regionFilter.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        stationCards.forEach(card => {
            const stationName = card.dataset.stationName;
            const stationCode = card.dataset.stationCode;
            const region = card.dataset.region;
            const district = card.dataset.district;
            const operationalPercentage = parseInt(card.dataset.operationalPercentage);

            // Search filter
            const matchesSearch = !searchTerm ||
                stationName.includes(searchTerm) ||
                stationCode.includes(searchTerm) ||
                region.includes(searchTerm) ||
                district.includes(searchTerm);

            // Region filter
            const matchesRegion = !selectedRegion || region === selectedRegion;

            // Status filter
            let matchesStatus = true;
            if (selectedStatus === 'operational') {
                matchesStatus = operationalPercentage === 100;
            } else if (selectedStatus === 'partial') {
                matchesStatus = operationalPercentage >= 80 && operationalPercentage < 100;
            } else if (selectedStatus === 'maintenance') {
                matchesStatus = operationalPercentage < 80;
            }

            const shouldShow = matchesSearch && matchesRegion && matchesStatus;

            if (shouldShow) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            noResults.style.display = 'block';
        } else {
            noResults.classList.add('hidden');
            noResults.style.display = 'none';
        }
    }

    // Add event listeners
    searchInput.addEventListener('input', filterStations);
    regionFilter.addEventListener('change', filterStations);
    statusFilter.addEventListener('change', filterStations);

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstVisibleCard = document.querySelector('.station-card[style*="block"], .station-card:not([style*="none"])');
            if (firstVisibleCard) {
                firstVisibleCard.click();
            }
        }
    });

    // Focus search on page load
    searchInput.focus();
});
</script>
@endpush
@endsection
