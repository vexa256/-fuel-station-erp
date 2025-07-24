@extends('layouts.app')

@section('title', 'FIFO Inventory Layers - Tank ' . $tank->tank_number)

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with Tank Context -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">Tank {{ $tank->tank_number }} - FIFO Layers</h1>
                            <div class="flex items-center space-x-4 text-sm text-slate-600 mt-1">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $tank->station_name }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    {{ $tank->product_name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="refreshFIFOData()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <a href="{{ route('inventory.movements', $tank->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                            </svg>
                            View Movements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content with Wizard-Style Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tab Navigation -->
        <div class="mb-8">
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('status')" id="tab-status" class="tab-button border-b-2 border-slate-900 text-slate-900 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        FIFO Status
                    </button>
                    <button onclick="showTab('layers')" id="tab-layers" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Active Layers
                    </button>
                    <button onclick="showTab('consumption')" id="tab-consumption" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Consumption History
                    </button>
                    <button onclick="showTab('analytics')" id="tab-analytics" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Analytics
                    </button>
                </nav>
            </div>
        </div>

        <!-- FIFO Status Tab -->
        <div id="content-status" class="tab-content">
            <!-- Statistics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Layers</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ $stats['total_layers'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Active Layers</p>
                            <p class="text-2xl font-semibold text-emerald-600">{{ $stats['active_layers'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Current Quantity</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ number_format($stats['total_quantity'], 0) }}</p>
                            <p class="text-xs text-slate-500">Liters</p>
                        </div>
                        <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Value</p>
                            <p class="text-2xl font-semibold text-slate-900">UGX {{ number_format($stats['total_value'], 0) }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FIFO Status Information -->
            @if(isset($fifoStatus['error']))
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">FIFO Status Error</h3>
                            <p class="mt-2 text-sm text-red-700">{{ $fifoStatus['error'] }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg border border-slate-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">FIFO System Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">System Status</dt>
                                    <dd class="text-sm text-slate-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Operational
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">Oldest Layer Age</dt>
                                    <dd class="text-sm text-slate-900">
                                        @if($stats['oldest_layer'])
                                            {{ \Carbon\Carbon::parse($stats['oldest_layer'])->diffForHumans() }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">Newest Layer</dt>
                                    <dd class="text-sm text-slate-900">
                                        @if($stats['newest_layer'])
                                            {{ \Carbon\Carbon::parse($stats['newest_layer'])->diffForHumans() }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">Depleted Layers</dt>
                                    <dd class="text-sm text-slate-900">{{ $stats['depleted_layers'] }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">Weighted Avg. Cost</dt>
                                    <dd class="text-sm text-slate-900">
                                        UGX {{ $stats['total_quantity'] > 0 ? number_format($stats['total_value'] / $stats['total_quantity'], 2) : '0.00' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-slate-600">Layer Efficiency</dt>
                                    <dd class="text-sm text-slate-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Optimal
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Active Layers Tab -->
        <div id="content-layers" class="tab-content hidden">
            <div class="bg-white rounded-lg border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">FIFO Inventory Layers</h3>
                    <p class="mt-1 text-sm text-slate-600">Current active inventory layers ordered by FIFO sequence</p>
                </div>

                @if($layers->count() > 0)
                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Layer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Batch Info</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantities</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cost Analysis</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($layers as $layer)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-slate-600">#{{ $layer->layer_sequence_number }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-slate-900">Layer {{ $layer->layer_sequence_number }}</div>
                                                        <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($layer->layer_created_at)->format('M d, Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-slate-900">{{ $layer->delivery_batch_number }}</div>
                                                @if($layer->delivery_note_number)
                                                    <div class="text-sm text-slate-500">{{ $layer->delivery_note_number }}</div>
                                                @endif
                                                @if($layer->company_name)
                                                    <div class="text-xs text-slate-400">{{ $layer->company_name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-slate-900">{{ number_format($layer->current_quantity_liters, 0) }} L</div>
                                                <div class="text-sm text-slate-500">
                                                    of {{ number_format($layer->opening_quantity_liters, 0) }} L
                                                </div>
                                                @if($layer->consumed_quantity_liters > 0)
                                                    <div class="text-xs text-red-600">
                                                        -{{ number_format($layer->consumed_quantity_liters, 0) }} L consumed
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-slate-900">UGX {{ number_format($layer->cost_per_liter, 2) }}/L</div>
                                                <div class="text-sm text-slate-500">
                                                    Value: UGX {{ number_format($layer->remaining_layer_value, 0) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($layer->is_depleted)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Depleted
                                                    </span>
                                                @elseif($layer->first_consumption_at)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                        Consuming
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="viewLayerDetails({{ $layer->id }})" class="text-slate-600 hover:text-slate-900 mr-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No FIFO layers found</h3>
                        <p class="mt-1 text-sm text-slate-500">This tank doesn't have any inventory layers yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Consumption History Tab -->
        <div id="content-consumption" class="tab-content hidden">
            <div class="bg-white rounded-lg border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Batch Consumption History</h3>
                    <p class="mt-1 text-sm text-slate-600">FIFO consumption records for all layers</p>
                </div>

                <div class="p-6">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Consumption tracking coming soon</h3>
                        <p class="mt-1 text-sm text-slate-500">Detailed consumption analytics will be available here.</p>
                        <div class="mt-6">
                            <a href="{{ route('inventory.batch-consumption', $tank->id) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
                                View Full Consumption Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="content-analytics" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Layer Distribution Chart -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Layer Distribution</h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">Chart visualization coming soon</p>
                        </div>
                    </div>
                </div>

                <!-- Cost Trend Analysis -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Cost Trend Analysis</h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">Cost analytics coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Layer Details Modal -->
<div id="layerModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Layer Details</h3>
                        <div class="mt-2" id="modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="closeLayerModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-600 text-base font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Tab Management System
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-slate-900', 'text-slate-900');
        button.classList.add('border-transparent', 'text-slate-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-slate-500');
    activeTab.classList.add('border-slate-900', 'text-slate-900');
}

// FIFO Data Management
function refreshFIFOData() {
    Swal.fire({
        title: 'Refreshing FIFO Data',
        text: 'Please wait while we update the inventory layers...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Simulate refresh (replace with actual AJAX call)
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

// Layer Details Modal
function viewLayerDetails(layerId) {
    document.getElementById('layerModal').classList.remove('hidden');
    document.getElementById('modal-content').innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2 mb-2"></div>
            <div class="h-4 bg-slate-200 rounded w-5/6"></div>
        </div>
    `;

    // TODO: Replace with actual AJAX call to get layer details
    setTimeout(() => {
        document.getElementById('modal-content').innerHTML = `
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-slate-500">Layer ID</dt>
                    <dd class="mt-1 text-sm text-slate-900">${layerId}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-slate-500">Status</dt>
                    <dd class="mt-1 text-sm text-slate-900">Active</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-slate-500">Detailed analytics for this layer will be available here.</dt>
                </div>
            </dl>
        `;
    }, 1000);
}

function closeLayerModal() {
    document.getElementById('layerModal').classList.add('hidden');
}

// Error Handling for All Controller Methods
function handleInventoryError(error, context = 'inventory operation') {
    console.error('Inventory Error:', error);

    Swal.fire({
        icon: 'error',
        title: 'Operation Failed',
        text: `The ${context} could not be completed. Please try again or contact support if the problem persists.`,
        confirmButtonColor: '#dc2626'
    });
}

// CSRF Token for AJAX requests
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Show the first tab by default
    showTab('status');

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

// Click outside modal to close
document.addEventListener('click', function(event) {
    const modal = document.getElementById('layerModal');
    if (event.target === modal) {
        closeLayerModal();
    }
});
</script>
@endsection
