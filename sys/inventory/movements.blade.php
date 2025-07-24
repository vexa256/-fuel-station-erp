@extends('layouts.app')

@section('title', 'Inventory Movements - Tank ' . $tank->tank_number)

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with Movement Context -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h1 class="text-2xl font-semibold text-slate-900">Tank {{ $tank->tank_number }} Movements</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Real-time
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    {{ $summary['total_movements'] }} movements
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
                        <button onclick="exportMovements()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </button>
                        <button onclick="refreshMovements()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <a href="{{ route('inventory.layers', $tank->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition-colors text-sm font-medium">
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
        <!-- Movement Summary Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Movements</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($summary['total_movements']) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Deliveries In</p>
                        <p class="text-2xl font-semibold text-emerald-600">{{ number_format($summary['deliveries_in'], 0) }}</p>
                        <p class="text-xs text-slate-500">Liters</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Sales Out</p>
                        <p class="text-2xl font-semibold text-red-600">{{ number_format($summary['sales_out'], 0) }}</p>
                        <p class="text-xs text-slate-500">Liters</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Current Balance</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($summary['current_balance'], 0) }}</p>
                        <p class="text-xs text-slate-500">Liters</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-3m-3 3l-3-3"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filtering Interface -->
        <div class="bg-white rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Movement Filters</h3>
                    <button onclick="toggleFilters()" id="filterToggle" class="text-sm text-slate-600 hover:text-slate-900">
                        <span id="filterToggleText">Show Filters</span>
                        <svg id="filterToggleIcon" class="w-4 h-4 ml-1 inline transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="filterPanel" class="hidden px-6 py-6">
                <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Movement Type</label>
                        <select name="movement_type" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">All Types</option>
                            <option value="DELIVERY_IN">Delivery In</option>
                            <option value="SALE_OUT">Sale Out</option>
                            <option value="TRANSFER_IN">Transfer In</option>
                            <option value="TRANSFER_OUT">Transfer Out</option>
                            <option value="ADJUSTMENT_IN">Adjustment In</option>
                            <option value="ADJUSTMENT_OUT">Adjustment Out</option>
                            <option value="LOSS">Loss</option>
                            <option value="EVAPORATION">Evaporation</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Movement Category</label>
                        <select name="movement_category" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">All Categories</option>
                            <option value="NORMAL_OPERATION">Normal Operation</option>
                            <option value="CORRECTION">Correction</option>
                            <option value="INVESTIGATION">Investigation</option>
                            <option value="EMERGENCY">Emergency</option>
                            <option value="MAINTENANCE">Maintenance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                        <input type="date" name="date_from" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date To</label>
                        <input type="date" name="date_to" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div class="md:col-span-4 flex items-center justify-end space-x-3">
                        <button type="button" onclick="clearFilters()" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">
                            Clear Filters
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-slate-800 transition-colors text-sm font-medium">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Movement History Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Movement History</h3>
                        <p class="mt-1 text-sm text-slate-600">Complete transaction log for Tank {{ $tank->tank_number }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-slate-500">{{ $movements->count() }} of {{ $movements->total() }} movements</span>
                    </div>
                </div>
            </div>

            @if($movements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Transaction</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Movement Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantities</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Financial</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">FIFO Layer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Authorization</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($movements as $movement)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @php
                                                    $iconClass = match($movement->movement_type) {
                                                        'DELIVERY_IN' => 'bg-emerald-100 text-emerald-600',
                                                        'SALE_OUT' => 'bg-red-100 text-red-600',
                                                        'TRANSFER_IN' => 'bg-blue-100 text-blue-600',
                                                        'TRANSFER_OUT' => 'bg-orange-100 text-orange-600',
                                                        'ADJUSTMENT_IN' => 'bg-purple-100 text-purple-600',
                                                        'ADJUSTMENT_OUT' => 'bg-pink-100 text-pink-600',
                                                        'LOSS' => 'bg-red-100 text-red-600',
                                                        'EVAPORATION' => 'bg-gray-100 text-gray-600',
                                                        default => 'bg-slate-100 text-slate-600'
                                                    };
                                                @endphp
                                                <div class="h-10 w-10 rounded-lg {{ $iconClass }} flex items-center justify-center">
                                                    @if(in_array($movement->movement_type, ['DELIVERY_IN', 'TRANSFER_IN', 'ADJUSTMENT_IN']))
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900">#{{ $movement->id }}</div>
                                                <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($movement->movement_timestamp)->format('M d, Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($movement->movement_type == 'DELIVERY_IN') bg-emerald-100 text-emerald-800
                                                @elseif($movement->movement_type == 'SALE_OUT') bg-red-100 text-red-800
                                                @elseif(str_contains($movement->movement_type, 'TRANSFER')) bg-blue-100 text-blue-800
                                                @elseif(str_contains($movement->movement_type, 'ADJUSTMENT')) bg-purple-100 text-purple-800
                                                @else bg-slate-100 text-slate-800
                                                @endif">
                                                {{ str_replace('_', ' ', $movement->movement_type) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-500 mt-1">{{ $movement->movement_category }}</div>
                                        @if($movement->movement_reason)
                                            <div class="text-xs text-slate-400 mt-1">{{ $movement->movement_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900">
                                            @if(in_array($movement->movement_type, ['DELIVERY_IN', 'TRANSFER_IN', 'ADJUSTMENT_IN']))
                                                <span class="text-emerald-600">+{{ number_format($movement->quantity_liters, 0) }} L</span>
                                            @else
                                                <span class="text-red-600">-{{ number_format($movement->quantity_liters, 0) }} L</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            Balance: {{ number_format($movement->running_balance_liters, 0) }} L
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movement->unit_cost)
                                            <div class="text-sm text-slate-900">UGX {{ number_format($movement->unit_cost, 2) }}/L</div>
                                        @endif
                                        @if($movement->total_cost)
                                            <div class="text-sm text-slate-500">Total: UGX {{ number_format($movement->total_cost, 0) }}</div>
                                        @endif
                                        @if($movement->running_balance_value)
                                            <div class="text-xs text-slate-400">Value: UGX {{ number_format($movement->running_balance_value, 0) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movement->layer_sequence_number)
                                            <div class="text-sm text-slate-900">Layer #{{ $movement->layer_sequence_number }}</div>
                                        @endif
                                        @if($movement->delivery_batch_number)
                                            <div class="text-sm text-slate-500">{{ $movement->delivery_batch_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movement->created_by_name)
                                            <div class="text-sm text-slate-900">{{ $movement->created_by_name }}</div>
                                        @endif
                                        @if($movement->authorized_by_name)
                                            <div class="text-sm text-slate-500">Auth: {{ $movement->authorized_by_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewMovementDetails({{ $movement->id }})" class="text-slate-600 hover:text-slate-900">
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
                    {{ $movements->links('pagination::tailwind') }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No movements found</h3>
                    <p class="mt-1 text-sm text-slate-500">No inventory movements have been recorded for this tank yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Movement Details Modal -->
<div id="movementModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="movement-modal-title">Movement Details</h3>
                        <div class="mt-4" id="movement-modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="closeMovementModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-600 text-base font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter Management
let filtersVisible = false;

function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    const toggleText = document.getElementById('filterToggleText');
    const toggleIcon = document.getElementById('filterToggleIcon');

    filtersVisible = !filtersVisible;

    if (filtersVisible) {
        panel.classList.remove('hidden');
        toggleText.textContent = 'Hide Filters';
        toggleIcon.classList.add('rotate-180');
    } else {
        panel.classList.add('hidden');
        toggleText.textContent = 'Show Filters';
        toggleIcon.classList.remove('rotate-180');
    }
}

function clearFilters() {
    document.getElementById('filterForm').reset();
    applyFilters();
}

function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }

    window.location.href = window.location.pathname + '?' + params.toString();
}

// Movement Operations
function refreshMovements() {
    Swal.fire({
        title: 'Refreshing Movements',
        text: 'Please wait while we update the movement data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function exportMovements() {
    Swal.fire({
        title: 'Export Format',
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
    // TODO: Implement Excel export
    Swal.fire('Export Started', 'Your Excel export is being prepared...', 'success');
}

function exportToCSV() {
    // TODO: Implement CSV export
    Swal.fire('Export Started', 'Your CSV export is being prepared...', 'success');
}

function exportToPDF() {
    // TODO: Implement PDF export
    Swal.fire('Export Started', 'Your PDF export is being prepared...', 'success');
}

// Movement Details Modal
function viewMovementDetails(movementId) {
    document.getElementById('movementModal').classList.remove('hidden');
    document.getElementById('movement-modal-content').innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-5/6 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-2/3"></div>
        </div>
    `;

    // TODO: Replace with actual AJAX call to get movement details
    setTimeout(() => {
        document.getElementById('movement-modal-content').innerHTML = `
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Movement Information</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Movement ID</dt>
                        <dd class="text-slate-900">#${movementId}</dd>
                        <dt class="text-slate-500">Reference Table</dt>
                        <dd class="text-slate-900">deliveries</dd>
                        <dt class="text-slate-500">Reference ID</dt>
                        <dd class="text-slate-900">#123</dd>
                        <dt class="text-slate-500">Movement Notes</dt>
                        <dd class="text-slate-900 col-span-2">Regular delivery from supplier ABC Ltd.</dd>
                    </dl>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Financial Details</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Unit Cost</dt>
                        <dd class="text-slate-900">UGX 4,500.00</dd>
                        <dt class="text-slate-500">Total Cost</dt>
                        <dd class="text-slate-900">UGX 45,000,000.00</dd>
                        <dt class="text-slate-500">Running Balance Value</dt>
                        <dd class="text-slate-900">UGX 145,000,000.00</dd>
                    </dl>
                </div>
            </div>
        `;
    }, 1000);
}

function closeMovementModal() {
    document.getElementById('movementModal').classList.add('hidden');
}

// Error Handling
function handleMovementError(error, context = 'movement operation') {
    console.error('Movement Error:', error);

    Swal.fire({
        icon: 'error',
        title: 'Operation Failed',
        text: `The ${context} could not be completed. Please try again or contact support if the problem persists.`,
        confirmButtonColor: '#dc2626'
    });
}

// Form Submission Handler
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    applyFilters();
});

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

// Click outside modal to close
document.addEventListener('click', function(event) {
    const modal = document.getElementById('movementModal');
    if (event.target === modal) {
        closeMovementModal();
    }
});
</script>
@endsection
