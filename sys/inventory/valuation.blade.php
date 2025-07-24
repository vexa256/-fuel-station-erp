@extends('layouts.app')

@section('title', 'Inventory Valuation - ' . $station->station_name)

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with Valuation Context -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h1 class="text-2xl font-semibold text-slate-900">{{ $station->station_name }} - Inventory Valuation</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    FIFO Method
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-slate-600 mt-1">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    {{ $tankValuations->count() }} Active Tanks
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Last updated: {{ now()->format('M d, Y H:i') }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                    </svg>
                                    Real-time FIFO
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="generateValuationReport()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Generate Report
                        </button>
                        <button onclick="refreshValuation()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button onclick="exportValuation()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Valuation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content with Enhanced Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Valuation Summary Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Inventory Value</p>
                        <p class="text-2xl font-semibold text-emerald-600">UGX {{ number_format($totals['total_value'], 0) }}</p>
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
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($totals['total_quantity'], 0) }}</p>
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
                        <p class="text-sm font-medium text-slate-600">Weighted Avg. Cost</p>
                        <p class="text-2xl font-semibold text-slate-900">UGX {{ number_format($totals['weighted_average_cost'], 2) }}</p>
                        <p class="text-xs text-slate-500">Per Liter</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Valuation Method</p>
                        <p class="text-lg font-semibold text-slate-900">FIFO</p>
                        <p class="text-xs text-slate-500">Real-time tracking</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation for Different Views -->
        <div class="mb-8">
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('current')" id="tab-current" class="tab-button border-b-2 border-emerald-600 text-emerald-600 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Current Valuation
                    </button>
                    <button onclick="showTab('history')" id="tab-history" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Historical Valuations
                    </button>
                    <button onclick="showTab('analytics')" id="tab-analytics" class="tab-button border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 text-sm font-medium">
                        Value Analytics
                    </button>
                </nav>
            </div>
        </div>

        <!-- Current Valuation Tab -->
        <div id="content-current" class="tab-content">
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-slate-900">Tank Inventory Valuation</h3>
                            <p class="mt-1 text-sm text-slate-600">Real-time FIFO valuation by tank</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-500">{{ $tankValuations->count() }} tanks active</span>
                        </div>
                    </div>
                </div>

                @if($tankValuations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tank</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Current Stock</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">FIFO Cost Analysis</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total Value</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Value %</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($tankValuations as $tank)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-slate-600">{{ $tank->tank_number }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">Tank {{ $tank->tank_number }}</div>
                                                    <div class="text-sm text-slate-500">ID: {{ $tank->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if(str_contains($tank->product_name, 'Petrol')) bg-blue-100 text-blue-800
                                                @elseif(str_contains($tank->product_name, 'Diesel')) bg-green-100 text-green-800
                                                @elseif(str_contains($tank->product_name, 'Kerosene')) bg-purple-100 text-purple-800
                                                @else bg-slate-100 text-slate-800
                                                @endif">
                                                {{ $tank->product_name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ number_format($tank->current_quantity, 0) }} L</div>
                                            @if($tank->current_quantity > 0)
                                                @php
                                                    $percentageFull = ($tank->current_quantity / 45000) * 100; // Assuming 45,000L tank capacity
                                                @endphp
                                                <div class="w-full bg-slate-200 rounded-full h-2 mt-1">
                                                    <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min($percentageFull, 100) }}%"></div>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-1">{{ number_format($percentageFull, 1) }}% full</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">UGX {{ number_format($tank->weighted_cost, 2) }}/L</div>
                                            <div class="text-xs text-slate-500">Weighted Average</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900">UGX {{ number_format($tank->current_value, 0) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $valuePercentage = $totals['total_value'] > 0 ? ($tank->current_value / $totals['total_value']) * 100 : 0;
                                            @endphp
                                            <div class="text-sm text-slate-900">{{ number_format($valuePercentage, 1) }}%</div>
                                            <div class="w-full bg-slate-200 rounded-full h-1.5 mt-1">
                                                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $valuePercentage }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('inventory.layers', $tank->id) }}" class="text-emerald-600 hover:text-emerald-900">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('inventory.movements', $tank->id) }}" class="text-slate-600 hover:text-slate-900">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Valuation Summary Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-600">
                                Total Station Inventory Value: <span class="font-medium text-slate-900">UGX {{ number_format($totals['total_value'], 0) }}</span>
                            </div>
                            <div class="text-sm text-slate-600">
                                Valuation as of: <span class="font-medium text-slate-900">{{ now()->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No active tanks</h3>
                        <p class="mt-1 text-sm text-slate-500">No active tanks found for valuation at this station.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Historical Valuations Tab -->
        <div id="content-history" class="tab-content hidden">
            <div class="bg-white rounded-lg border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Historical Valuations</h3>
                    <p class="mt-1 text-sm text-slate-600">Recent inventory valuation snapshots</p>
                </div>

                @if($recentValuations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Valuation Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total Value</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Variance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($recentValuations as $valuation)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($valuation->valuation_date)->format('M d, Y') }}</div>
                                            <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($valuation->valuation_time)->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($valuation->valuation_type == 'DAILY_CLOSE') bg-blue-100 text-blue-800
                                                @elseif($valuation->valuation_type == 'MONTHLY_END') bg-purple-100 text-purple-800
                                                @elseif($valuation->valuation_type == 'AUDIT') bg-red-100 text-red-800
                                                @else bg-slate-100 text-slate-800
                                                @endif">
                                                {{ str_replace('_', ' ', $valuation->valuation_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ number_format($valuation->total_fuel_quantity_liters, 0) }} L</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">UGX {{ number_format($valuation->total_fuel_value, 0) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ $valuation->valuation_method }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($valuation->valuation_variance_percentage)
                                                <div class="text-sm {{ abs($valuation->valuation_variance_percentage) > 1 ? 'text-red-600' : 'text-emerald-600' }}">
                                                    {{ number_format($valuation->valuation_variance_percentage, 2) }}%
                                                </div>
                                            @else
                                                <div class="text-sm text-slate-400">-</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No historical data</h3>
                        <p class="mt-1 text-sm text-slate-500">No historical valuations recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Value Analytics Tab -->
        <div id="content-analytics" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Value Distribution Chart -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Value Distribution by Product</h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">Chart visualization coming soon</p>
                        </div>
                    </div>
                </div>

                <!-- Value Trend Analysis -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Value Trend Analysis</h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">Trend analysis coming soon</p>
                        </div>
                    </div>
                </div>

                <!-- FIFO Cost Analytics -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">FIFO Cost Efficiency</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Average Cost per Liter</span>
                            <span class="text-sm font-medium text-slate-900">UGX {{ number_format($totals['weighted_average_cost'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Total Inventory Turnover</span>
                            <span class="text-sm font-medium text-slate-900">Calculating...</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">FIFO Layer Efficiency</span>
                            <span class="text-sm font-medium text-emerald-600">Optimal</span>
                        </div>
                    </div>
                </div>

                <!-- Market Comparison -->
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Market Value Comparison</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Book Value</span>
                            <span class="text-sm font-medium text-slate-900">UGX {{ number_format($totals['total_value'], 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Market Value (Est.)</span>
                            <span class="text-sm font-medium text-slate-900">Calculating...</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Variance</span>
                            <span class="text-sm font-medium text-slate-500">TBD</span>
                        </div>
                    </div>
                </div>
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
        button.classList.remove('border-emerald-600', 'text-emerald-600');
        button.classList.add('border-transparent', 'text-slate-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-slate-500');
    activeTab.classList.add('border-emerald-600', 'text-emerald-600');
}

// Valuation Operations
function refreshValuation() {
    Swal.fire({
        title: 'Refreshing Valuation',
        text: 'Please wait while we recalculate inventory values...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

function generateValuationReport() {
    Swal.fire({
        title: 'Generate Valuation Report',
        text: 'Select the type of valuation report you need:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Detailed Report',
        cancelButtonText: 'Cancel',
        showDenyButton: true,
        denyButtonText: 'Summary Report'
    }).then((result) => {
        if (result.isConfirmed) {
            generateDetailedReport();
        } else if (result.isDenied) {
            generateSummaryReport();
        }
    });
}

function generateDetailedReport() {
    Swal.fire({
        title: 'Generating Report',
        text: 'Your detailed valuation report is being prepared...',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false
    });
    // TODO: Implement detailed report generation
}

function generateSummaryReport() {
    Swal.fire({
        title: 'Generating Report',
        text: 'Your summary valuation report is being prepared...',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false
    });
    // TODO: Implement summary report generation
}

function exportValuation() {
    Swal.fire({
        title: 'Export Valuation',
        text: 'Select your preferred export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        if (result.isConfirmed) {
            exportToExcel();
        } else if (result.isDenied) {
            exportToCSV();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            exportToPDF();
        }
    });
}

function exportToExcel() {
    Swal.fire('Export Started', 'Your Excel export is being prepared...', 'success');
    // TODO: Implement Excel export
}

function exportToCSV() {
    Swal.fire('Export Started', 'Your CSV export is being prepared...', 'success');
    // TODO: Implement CSV export
}

function exportToPDF() {
    Swal.fire('Export Started', 'Your PDF export is being prepared...', 'success');
    // TODO: Implement PDF export
}

// Error Handling
function handleValuationError(error, context = 'valuation operation') {
    console.error('Valuation Error:', error);

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
    showTab('current');

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
