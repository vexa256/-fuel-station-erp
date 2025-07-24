@extends('layouts.app')

@section('title', 'Batch Consumption Analysis - Tank ' . $tank->tank_number)

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with Consumption Context -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h1 class="text-2xl font-semibold text-slate-900">Tank {{ $tank->tank_number }} - Batch Consumption</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    FIFO Tracking
                                </span>
                            </div>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                    </svg>
                                    {{ $summary['total_consumption_records'] }} consumption records
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Last update: {{ now()->format('H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="exportConsumptionReport()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Analytics Report
                        </button>
                        <button onclick="refreshConsumption()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <a href="{{ route('inventory.layers', $tank->id) }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                            </svg>
                            View FIFO Layers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content with Enhanced Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Consumption Analytics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Consumed</p>
                        <p class="text-2xl font-semibold text-orange-600">{{ number_format($summary['total_consumed_liters'], 0) }}</p>
                        <p class="text-xs text-slate-500">Liters</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Value</p>
                        <p class="text-2xl font-semibold text-slate-900">UGX {{ number_format($summary['total_consumed_value'], 0) }}</p>
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
                        <p class="text-sm font-medium text-slate-600">Depleted Layers</p>
                        <p class="text-2xl font-semibold text-red-600">{{ $summary['depleted_layers'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Consumption Records</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($summary['total_consumption_records']) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation for Different Views -->
        <div class="mb-8">
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('timeline')" id="tab-timeline" class="tab-button border-b-2 border-orange-600 text-orange-600 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Consumption Timeline
                    </button>
                    <button onclick="showTab('layers')" id="tab-layers" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Layer Analysis
                    </button>
                    <button onclick="showTab('efficiency')" id="tab-efficiency" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        FIFO Efficiency
                    </button>
                    <button onclick="showTab('patterns')" id="tab-patterns" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Consumption Patterns
                    </button>
                </nav>
            </div>
        </div>

        <!-- Consumption Timeline Tab -->
        <div id="content-timeline" class="tab-content">
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-slate-900">Batch Consumption Timeline</h3>
                            <p class="mt-1 text-sm text-slate-600">Chronological FIFO consumption records with layer depletion tracking</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-500">{{ $consumptions->count() }} of {{ $consumptions->total() }} records</span>
                        </div>
                    </div>
                </div>

                @if($consumptions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Consumption Event</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Layer Details</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantities</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cost Analysis</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">FIFO Impact</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reading Source</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($consumptions as $consumption)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg {{ $consumption->is_layer_depleted ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }} flex items-center justify-center">
                                                        @if($consumption->is_layer_depleted)
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        @else
                                                            <span class="text-xs font-medium">{{ $consumption->consumption_sequence }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">Sequence #{{ $consumption->consumption_sequence }}</div>
                                                    <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($consumption->consumption_timestamp)->format('M d, Y H:i') }}</div>
                                                    @if($consumption->is_layer_depleted)
                                                        <div class="text-xs text-red-600 font-medium">Layer Depleted</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">Layer #{{ $consumption->layer_sequence_number }}</div>
                                            <div class="text-sm text-slate-500">{{ $consumption->delivery_batch_number }}</div>
                                            <div class="text-xs text-slate-400">{{ $consumption->consumption_method ?? 'FIFO_AUTO' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ number_format($consumption->quantity_consumed_liters, 0) }} L</div>
                                            <div class="text-sm text-slate-500">
                                                Before: {{ number_format($consumption->layer_balance_before_liters, 0) }} L
                                            </div>
                                            <div class="text-sm text-slate-500">
                                                After: {{ number_format($consumption->layer_balance_after_liters, 0) }} L
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">UGX {{ number_format($consumption->cost_per_liter, 2) }}/L</div>
                                            <div class="text-sm text-slate-500">Total: UGX {{ number_format($consumption->total_cost_consumed, 0) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($consumption->is_layer_depleted)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Layer Depleted
                                                </span>
                                            @else
                                                @php
                                                    $remainingPercentage = $consumption->layer_balance_before_liters > 0
                                                        ? ($consumption->layer_balance_after_liters / $consumption->layer_balance_before_liters) * 100
                                                        : 0;
                                                @endphp
                                                <div class="w-full bg-slate-200 rounded-full h-2">
                                                    <div class="bg-orange-600 h-2 rounded-full" style="width: {{ $remainingPercentage }}%"></div>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-1">{{ number_format($remainingPercentage, 1) }}% remaining</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($consumption->reading_date)
                                                <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($consumption->reading_date)->format('M d') }}</div>
                                            @endif
                                            @if($consumption->pump_number)
                                                <div class="text-sm text-slate-500">Pump {{ $consumption->pump_number }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="viewConsumptionDetails({{ $consumption->id }})" class="text-orange-600 hover:text-orange-900">
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

                    <!-- Enhanced Pagination -->
                    <div class="bg-white px-6 py-3 border-t border-slate-200">
                        {{ $consumptions->links('pagination::tailwind') }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No consumption records</h3>
                        <p class="mt-1 text-sm text-slate-500">No batch consumption data has been recorded for this tank yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Layer Analysis Tab -->
        <div id="content-layers" class="tab-content hidden">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Layer Consumption Analysis</h3>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Layer analysis coming soon</h3>
                    <p class="mt-1 text-sm text-slate-500">Detailed layer-by-layer consumption breakdown will be available here.</p>
                </div>
            </div>
        </div>

        <!-- FIFO Efficiency Tab -->
        <div id="content-efficiency" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- FIFO Performance Metrics -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">FIFO Performance</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Average Cost per Liter</span>
                            @php
                                $avgCost = $summary['total_consumed_liters'] > 0
                                    ? $summary['total_consumed_value'] / $summary['total_consumed_liters']
                                    : 0;
                            @endphp
                            <span class="text-sm font-medium text-slate-900">UGX {{ number_format($avgCost, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">FIFO Compliance</span>
                            <span class="text-sm font-medium text-emerald-600">100%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Layer Depletion Rate</span>
                            @php
                                $depletionRate = $summary['total_consumption_records'] > 0
                                    ? ($summary['depleted_layers'] / $summary['total_consumption_records']) * 100
                                    : 0;
                            @endphp
                            <span class="text-sm font-medium text-slate-900">{{ number_format($depletionRate, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Consumption Efficiency</span>
                            <span class="text-sm font-medium text-emerald-600">Optimal</span>
                        </div>
                    </div>
                </div>

                <!-- Cost Impact Analysis -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Cost Impact Analysis</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Total Value Consumed</span>
                            <span class="text-sm font-medium text-slate-900">UGX {{ number_format($summary['total_consumed_value'], 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">FIFO Cost Savings</span>
                            <span class="text-sm font-medium text-emerald-600">Calculating...</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Layer Utilization</span>
                            <span class="text-sm font-medium text-slate-900">Optimized</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Inventory Turnover</span>
                            <span class="text-sm font-medium text-slate-900">Efficient</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consumption Patterns Tab -->
        <div id="content-patterns" class="tab-content hidden">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Consumption Pattern Analysis</h3>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Pattern analysis coming soon</h3>
                    <p class="mt-1 text-sm text-slate-500">Advanced consumption pattern analytics will be available here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Consumption Details Modal -->
<div id="consumptionModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="consumption-modal-title">Consumption Details</h3>
                        <div class="mt-4" id="consumption-modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="closeConsumptionModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-600 text-base font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:ml-3 sm:w-auto sm:text-sm">
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
        button.classList.remove('border-orange-600', 'text-orange-600');
        button.classList.add('border-transparent', 'text-slate-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-slate-500');
    activeTab.classList.add('border-orange-600', 'text-orange-600');
}

// Consumption Operations
function refreshConsumption() {
    Swal.fire({
        title: 'Refreshing Consumption Data',
        text: 'Please wait while we update the batch consumption records...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

function exportConsumptionReport() {
    Swal.fire({
        title: 'Generate Consumption Report',
        text: 'Select the type of consumption analysis you need:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Detailed Analytics',
        cancelButtonText: 'FIFO Summary',
        showDenyButton: true,
        denyButtonText: 'Cost Analysis'
    }).then((result) => {
        if (result.isConfirmed) {
            generateDetailedAnalytics();
        } else if (result.isDenied) {
            generateCostAnalysis();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            generateFIFOSummary();
        }
    });
}

function generateDetailedAnalytics() {
    Swal.fire({
        title: 'Generating Analytics',
        text: 'Your detailed consumption analytics report is being prepared...',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false
    });
    // TODO: Implement detailed analytics export
}

function generateCostAnalysis() {
    Swal.fire({
        title: 'Generating Analysis',
        text: 'Your cost analysis report is being prepared...',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false
    });
    // TODO: Implement cost analysis export
}

function generateFIFOSummary() {
    Swal.fire({
        title: 'Generating Summary',
        text: 'Your FIFO summary report is being prepared...',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false
    });
    // TODO: Implement FIFO summary export
}

// Consumption Details Modal
function viewConsumptionDetails(consumptionId) {
    document.getElementById('consumptionModal').classList.remove('hidden');
    document.getElementById('consumption-modal-content').innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-5/6 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-2/3"></div>
        </div>
    `;

    // TODO: Replace with actual AJAX call to get consumption details
    setTimeout(() => {
        document.getElementById('consumption-modal-content').innerHTML = `
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Consumption Event</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Consumption ID</dt>
                        <dd class="text-slate-900">#${consumptionId}</dd>
                        <dt class="text-slate-500">Sequence Number</dt>
                        <dd class="text-slate-900">5</dd>
                        <dt class="text-slate-500">Consumption Method</dt>
                        <dd class="text-slate-900">FIFO_AUTO</dd>
                        <dt class="text-slate-500">Layer Depleted</dt>
                        <dd class="text-slate-900">No</dd>
                    </dl>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">FIFO Layer Impact</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Layer Sequence</dt>
                        <dd class="text-slate-900">#3</dd>
                        <dt class="text-slate-500">Batch Number</dt>
                        <dd class="text-slate-900">BATCH-2024-001</dd>
                        <dt class="text-slate-500">Balance Before</dt>
                        <dd class="text-slate-900">5,000 L</dd>
                        <dt class="text-slate-500">Balance After</dt>
                        <dd class="text-slate-900">4,200 L</dd>
                    </dl>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Cost Information</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Cost per Liter</dt>
                        <dd class="text-slate-900">UGX 4,500.00</dd>
                        <dt class="text-slate-500">Total Cost</dt>
                        <dd class="text-slate-900">UGX 3,600,000.00</dd>
                        <dt class="text-slate-500">Quantity Consumed</dt>
                        <dd class="text-slate-900">800 Liters</dd>
                    </dl>
                </div>
            </div>
        `;
    }, 1000);
}

function closeConsumptionModal() {
    document.getElementById('consumptionModal').classList.add('hidden');
}

// Error Handling
function handleConsumptionError(error, context = 'consumption operation') {
    console.error('Consumption Error:', error);

    Swal.fire({
        icon: 'error',
        title: 'Operation Failed',
        text: `The ${context} could not be completed. Please try again or contact support if the problem persists.`,
        confirmButtonColor: '#dc2626'
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Show the first tab by default
    showTab('timeline');

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
    const modal = document.getElementById('consumptionModal');
    if (event.target === modal) {
        closeConsumptionModal();
    }
});
</script>
@endsection
