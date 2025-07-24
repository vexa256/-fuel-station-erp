@extends('layouts.app')

@section('title', 'FIFO Inventory Layers - Tank ' . $tank->tank_number)

@section('breadcrumb')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="flex items-center space-x-1.5 text-xs text-muted-foreground">
        <li><a href="{{ route('dashboard') }}" class="hover:text-foreground transition-colors">Dashboard</a></li>
        <li><i class="fas fa-chevron-right text-xs opacity-50"></i></li>
        <li><a href="{{ route('tanks.select') }}" class="hover:text-foreground transition-colors">Tanks</a></li>
        <li><i class="fas fa-chevron-right text-xs opacity-50"></i></li>
        <li><a href="{{ route('tanks.index', $tank->station_id) }}" class="hover:text-foreground transition-colors truncate max-w-24">{{ $tank->station_name }}</a></li>
        <li><i class="fas fa-chevron-right text-xs opacity-50"></i></li>
        <li class="text-foreground font-medium truncate">Tank {{ $tank->tank_number }} Layers</li>
    </ol>
</nav>
@endsection

@section('page-header')
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
    <div class="flex items-center space-x-4 mb-4 lg:mb-0">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
            </div>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">FIFO Inventory Layers</h1>
            <p class="text-gray-600">Tank {{ $tank->tank_number }} - {{ $tank->product_name }} at {{ $tank->station_name }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('tanks.index', $tank->station_id) }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Tanks
        </a>

        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
        <a href="{{ route('tanks.edit', $tank->id) }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Tank
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="layersManager()">

    <!-- Executive Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Layers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Layers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_layers']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['active_layers'] }} active</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Current Stock -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Current Stock</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_volume'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Liters</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Value -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Value</p>
                    <p class="text-3xl font-bold text-gray-900">TSh {{ number_format($stats['total_value'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Current inventory value</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Weighted Avg Cost -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Cost/Liter</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['weighted_avg_cost'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Weighted average</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- FIFO Flow Visualization -->
    @if($layers->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">FIFO Consumption Flow</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Fresh (0-30 days)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                    <span>Aging (31-60 days)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span>Critical (60+ days)</span>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="flex items-center space-x-2 overflow-x-auto pb-4">
                @foreach($layers->where('is_depleted', 0)->take(10) as $layer)
                <div class="flex-shrink-0 w-24 text-center">
                    <div class="relative">
                        <!-- Layer Card -->
                        <div class="w-20 h-16 rounded-lg border-2 {{ $layer->age_days <= 30 ? 'border-green-500 bg-green-50' : ($layer->age_days <= 60 ? 'border-yellow-500 bg-yellow-50' : 'border-red-500 bg-red-50') }} mx-auto mb-2 flex flex-col justify-center items-center">
                            <div class="text-xs font-semibold text-gray-800">#{{ $layer->layer_sequence_number }}</div>
                            <div class="text-xs text-gray-600">{{ number_format($layer->current_quantity_liters, 0) }}L</div>
                        </div>

                        <!-- Consumption Progress -->
                        <div class="w-20 h-2 bg-gray-200 rounded-full mx-auto mb-1">
                            <div class="h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500"
                                 style="width: {{ $layer->consumption_percentage }}%"></div>
                        </div>

                        <!-- Age Indicator -->
                        <div class="text-xs text-gray-500">{{ $layer->age_days }}d</div>
                    </div>
                </div>

                @if(!$loop->last)
                <div class="flex-shrink-0 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                @endif
                @endforeach

                @if($layers->where('is_depleted', 0)->count() > 10)
                <div class="flex-shrink-0 w-24 text-center">
                    <div class="w-20 h-16 rounded-lg border-2 border-gray-300 bg-gray-50 mx-auto mb-2 flex items-center justify-center">
                        <span class="text-xs text-gray-500">+{{ $layers->where('is_depleted', 0)->count() - 10 }} more</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Layer Details Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Inventory Layer Details</h2>

                <!-- Filters -->
                <div class="flex items-center space-x-3">
                    <select x-model="statusFilter" @change="filterLayers()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Layers</option>
                        <option value="ACTIVE">Active Only</option>
                        <option value="CONSUMING">Consuming</option>
                        <option value="DEPLETED">Depleted</option>
                    </select>

                    <div class="relative">
                        <input type="text" x-model="searchQuery" @input="filterLayers()"
                               placeholder="Search layers..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($layers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layer Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($layers as $layer)
                    <tr class="hover:bg-gray-50 transition-colors"
                        x-show="layerVisible({{ json_encode($layer) }})"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100">

                        <!-- Layer Info -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg {{ $layer->age_days <= 30 ? 'bg-green-100 text-green-800' : ($layer->age_days <= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }} flex items-center justify-center">
                                        <span class="text-sm font-semibold">#{{ $layer->layer_sequence_number }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Layer {{ $layer->layer_sequence_number }}</div>
                                    <div class="text-sm text-gray-500">Batch: {{ $layer->delivery_batch_number }}</div>
                                    <div class="text-xs text-gray-400">Age: {{ $layer->age_days }} days</div>
                                </div>
                            </div>
                        </td>

                        <!-- Quantities -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Opening:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($layer->opening_quantity_liters, 0) }}L</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Current:</span>
                                    <span class="text-sm font-medium {{ $layer->current_quantity_liters > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ number_format($layer->current_quantity_liters, 0) }}L</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Consumed:</span>
                                    <span class="text-sm font-medium text-blue-600">{{ number_format($layer->consumed_quantity_liters, 0) }}L</span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-1.5 rounded-full transition-all duration-500"
                                         style="width: {{ $layer->consumption_percentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 text-center">{{ $layer->consumption_percentage }}% consumed</div>
                            </div>
                        </td>

                        <!-- Cost Details -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Unit Cost:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($layer->cost_per_liter, 4) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Total Cost:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($layer->total_layer_cost, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Remaining:</span>
                                    <span class="text-sm font-medium text-green-600">{{ number_format($layer->remaining_layer_value, 0) }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Delivery Info -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="text-sm font-medium text-gray-900">{{ $layer->supplier_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $layer->delivery_note_number ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $layer->delivery_date ? \Carbon\Carbon::parse($layer->delivery_date)->format('M d, Y') : 'N/A' }}
                                </div>
                                @if($layer->delivery_temperature_celsius)
                                <div class="text-xs text-gray-400">Temp: {{ $layer->delivery_temperature_celsius }}°C</div>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2">
                                @if($layer->is_depleted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Depleted
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $layer->layer_status }}
                                </span>
                                @endif

                                @if($layer->first_consumption_at)
                                <div class="text-xs text-gray-500">
                                    First consumed: {{ \Carbon\Carbon::parse($layer->first_consumption_at)->format('M d') }}
                                </div>
                                @endif

                                @if($layer->fully_depleted_at)
                                <div class="text-xs text-gray-500">
                                    Depleted: {{ \Carbon\Carbon::parse($layer->fully_depleted_at)->format('M d') }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button @click="showLayerDetails({{ json_encode($layer) }})"
                                        class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>

                                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                <button class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No inventory layers found</h3>
            <p class="mt-1 text-sm text-gray-500">This tank doesn't have any inventory layers yet. Layers are created when deliveries are received.</p>
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
            <div class="mt-6">
                <a href="#"
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Record Delivery
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Layer Detail Modal -->
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="'Layer ' + (selectedLayer?.layer_sequence_number || '') + ' Details'"></h3>

                            <div class="space-y-4" x-show="selectedLayer">
                                <!-- Layer Summary -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Layer Summary</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Batch Number:</span>
                                            <div class="font-medium" x-text="selectedLayer?.delivery_batch_number"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Layer Status:</span>
                                            <div class="font-medium" x-text="selectedLayer?.layer_status"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Age:</span>
                                            <div class="font-medium" x-text="(selectedLayer?.age_days || 0) + ' days'"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Consumption:</span>
                                            <div class="font-medium" x-text="(selectedLayer?.consumption_percentage || 0) + '%'"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Details -->
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Quantity Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Opening Quantity:</span>
                                            <span class="font-medium" x-text="(selectedLayer?.opening_quantity_liters || 0).toLocaleString() + ' L'"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Current Quantity:</span>
                                            <span class="font-medium" x-text="(selectedLayer?.current_quantity_liters || 0).toLocaleString() + ' L'"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Consumed Quantity:</span>
                                            <span class="font-medium" x-text="(selectedLayer?.consumed_quantity_liters || 0).toLocaleString() + ' L'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Information -->
                                <div class="bg-green-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Cost Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Cost per Liter:</span>
                                            <span class="font-medium" x-text="'TSh ' + (selectedLayer?.cost_per_liter || 0).toFixed(4)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Layer Cost:</span>
                                            <span class="font-medium" x-text="'TSh ' + (selectedLayer?.total_layer_cost || 0).toLocaleString()"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Remaining Value:</span>
                                            <span class="font-medium" x-text="'TSh ' + (selectedLayer?.remaining_layer_value || 0).toLocaleString()"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function layersManager() {
    return {
        statusFilter: '',
        searchQuery: '',
        showModal: false,
        selectedLayer: null,

        filterLayers() {
            // Filter functionality is handled by Alpine.js x-show directive
        },

        layerVisible(layer) {
            let visible = true;

            // Status filter
            if (this.statusFilter && layer.layer_status !== this.statusFilter) {
                if (this.statusFilter === 'ACTIVE' && layer.is_depleted) {
                    visible = false;
                } else if (this.statusFilter !== 'ACTIVE' && layer.layer_status !== this.statusFilter) {
                    visible = false;
                }
            }

            // Search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                const searchFields = [
                    layer.delivery_batch_number,
                    layer.supplier_name,
                    layer.delivery_note_number,
                    layer.layer_sequence_number?.toString()
                ].filter(Boolean);

                visible = visible && searchFields.some(field =>
                    field.toLowerCase().includes(query)
                );
            }

            return visible;
        },

        showLayerDetails(layer) {
            this.selectedLayer = layer;
            this.showModal = true;
        }
    }
}

// Auto-approval notification for CEO/SYSTEM_ADMIN
@if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
document.addEventListener('DOMContentLoaded', function() {
    // Show auto-approval notification
    setTimeout(function() {
        Swal.fire({
            icon: 'info',
            title: ' Auto-Approved Access',
            text: 'FIFO layer data accessed with {{ auth()->user()->role }} privileges',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }, 500);
});
@endif

// Success/Error notifications
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK'
    });
}
</script>
@endsection
