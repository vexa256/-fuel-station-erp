@extends('layouts.app')

@section('title', 'Inventory Management Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with System Overview -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-slate-700 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h1 class="text-2xl font-semibold text-slate-900">Inventory Management</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $currentUserRole }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-slate-600 mt-1">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m4 0V9a1 1 0 011-1h4a1 1 0 011 1v12m-6 0h6"/>
                                    </svg>
                                    {{ $systemMetrics['total_stations'] }} Stations
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    {{ $systemMetrics['total_tanks'] }} Tanks
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    UGX {{ number_format($systemMetrics['total_inventory_value'], 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('inventory.adjustments') }}" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Adjustments
                        </a>
                        <button onclick="refreshDashboard()" class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-md hover:bg-slate-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- System Overview Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Value</p>
                        <p class="text-2xl font-semibold text-emerald-600">UGX {{ number_format($systemMetrics['total_inventory_value'], 0) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Quantity</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($systemMetrics['total_inventory_quantity'], 0) }}</p>
                        <p class="text-xs text-slate-500">Liters</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Layers</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($systemMetrics['total_active_layers']) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Recent Movements</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($systemMetrics['total_recent_movements']) }}</p>
                        <p class="text-xs text-slate-500">Last 7 days</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Adjustments</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($systemMetrics['total_recent_adjustments']) }}</p>
                        <p class="text-xs text-slate-500">Last 30 days</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Station and Tank Selection -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-slate-900">Station & Tank Operations</h3>
                                <p class="mt-1 text-sm text-slate-600">Select a station and tank to access inventory operations</p>
                            </div>
                        </div>
                    </div>

                    @if($stations->count() > 0)
                        <div class="divide-y divide-slate-200">
                            @foreach($stations as $station)
                                <div class="p-6">
                                    <!-- Station Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m4 0V9a1 1 0 011-1h4a1 1 0 011 1v12m-6 0h6"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-medium text-slate-900">{{ $station['station_name'] }}</h4>
                                                <p class="text-sm text-slate-500">{{ $station['station_code'] }} • {{ $station['tank_count'] }} tanks</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-slate-900">UGX {{ number_format($station['current_value'], 0) }}</div>
                                            <div class="text-sm text-slate-500">{{ number_format($station['current_quantity'], 0) }} L</div>
                                        </div>
                                    </div>

                                    <!-- Station Operations -->
                                    <div class="flex items-center space-x-2 mb-4">
                                        <a href="{{ route('inventory.valuation', $station['station_id']) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-emerald-100 text-emerald-700 text-sm font-medium rounded-md hover:bg-emerald-200 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            Station Valuation
                                        </a>
                                    </div>

                                    <!-- Tank Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($station['tanks'] as $tank)
                                            <div class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 transition-colors">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                                                            <span class="text-xs font-medium text-slate-600">{{ $tank['tank_number'] }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-slate-900">Tank {{ $tank['tank_number'] }}</div>
                                                            <div class="text-xs text-slate-500">{{ $tank['product_name'] }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm font-medium text-slate-900">{{ $tank['fill_percentage'] }}%</div>
                                                        <div class="text-xs text-slate-500">{{ number_format($tank['current_quantity'], 0) }}L</div>
                                                    </div>
                                                </div>

                                                <!-- Fill Level Progress -->
                                                <div class="w-full bg-slate-200 rounded-full h-2 mb-3">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($tank['fill_percentage'], 100) }}%"></div>
                                                </div>

                                                <!-- Tank Operations -->
                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ route('inventory.layers', $tank['tank_id']) }}"
                                                       class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                                                        </svg>
                                                        FIFO Layers
                                                    </a>
                                                    <a href="{{ route('inventory.movements', $tank['tank_id']) }}"
                                                       class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded hover:bg-purple-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                                        </svg>
                                                        Movements
                                                    </a>
                                                    <a href="{{ route('inventory.batch-consumption', $tank['tank_id']) }}"
                                                       class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded hover:bg-orange-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                        </svg>
                                                        Consumption
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">No stations available</h3>
                            <p class="mt-1 text-sm text-slate-500">No stations or tanks are currently accessible for inventory operations.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activities Sidebar -->
            <div class="space-y-6">
                <!-- Recent Activities -->
                <div class="bg-white rounded-lg border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-medium text-slate-900">Recent Activities</h3>
                    </div>
                    <div class="p-6">
                        @if($recentActivities->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentActivities as $activity)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-lg {{
                                                str_contains($activity->movement_type, 'IN') ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'
                                            }} flex items-center justify-center">
                                                @if(str_contains($activity->movement_type, 'IN'))
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm text-slate-900">
                                                {{ str_replace('_', ' ', $activity->movement_type) }}
                                            </div>
                                            <div class="text-sm text-slate-500">
                                                Tank {{ $activity->tank_number }} • {{ $activity->station_name }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                {{ \Carbon\Carbon::parse($activity->movement_timestamp)->diffForHumans() }}
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium {{
                                            str_contains($activity->movement_type, 'IN') ? 'text-emerald-600' : 'text-red-600'
                                        }}">
                                            {{ str_contains($activity->movement_type, 'IN') ? '+' : '-' }}{{ number_format($activity->quantity_liters, 0) }}L
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-slate-500">No recent activities</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Valuations -->
                <div class="bg-white rounded-lg border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-medium text-slate-900">Recent Valuations</h3>
                    </div>
                    <div class="p-6">
                        @if($recentValuations->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentValuations as $valuation)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $valuation->station_name }}</div>
                                            <div class="text-sm text-slate-500">
                                                {{ \Carbon\Carbon::parse($valuation->valuation_date)->format('M d, Y') }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-slate-900">UGX {{ number_format($valuation->total_fuel_value, 0) }}</div>
                                            <div class="text-xs text-slate-500">{{ $valuation->valuation_type }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-slate-500">No recent valuations</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    Swal.fire({
        title: 'Refreshing Dashboard',
        text: 'Please wait while we update the inventory data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Handle error messages from controller
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc2626'
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#059669'
        });
    @endif
});
</script>
@endsection
