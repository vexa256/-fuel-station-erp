@extends('layouts.app')

@section('title', 'Inventory Adjustments')

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Enhanced Header with Adjustment Context -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h1 class="text-2xl font-semibold text-slate-900">Inventory Adjustments</h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Audit Trail
                                </span>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-slate-600 mt-1">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    All Stations
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $adjustments->total() }} adjustments
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Last update: {{ now()->format('H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="exportAdjustments()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Report
                        </button>
                        <button onclick="refreshAdjustments()" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button onclick="showNewAdjustmentModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            New Adjustment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content with Enhanced Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Adjustment Analytics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Adjustments</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($adjustments->total()) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Positive Adjustments</p>
                        <p class="text-2xl font-semibold text-emerald-600">
                            {{ $adjustments->where('movement_type', 'ADJUSTMENT_IN')->count() }}
                        </p>
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
                        <p class="text-sm font-medium text-slate-600">Negative Adjustments</p>
                        <p class="text-2xl font-semibold text-red-600">
                            {{ $adjustments->where('movement_type', 'ADJUSTMENT_OUT')->count() }}
                        </p>
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
                        <p class="text-sm font-medium text-slate-600">This Month</p>
                        <p class="text-2xl font-semibold text-slate-900">
                            {{ $adjustments->filter(function($adj) { return \Carbon\Carbon::parse($adj->movement_timestamp)->isCurrentMonth(); })->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filtering Interface -->
        <div class="bg-white rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Filter Adjustments</h3>
                    <button onclick="toggleAdjustmentFilters()" id="adjustmentFilterToggle" class="text-sm text-slate-600 hover:text-slate-900">
                        <span id="adjustmentFilterToggleText">Show Filters</span>
                        <svg id="adjustmentFilterToggleIcon" class="w-4 h-4 ml-1 inline transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="adjustmentFilterPanel" class="hidden px-6 py-6">
                <form id="adjustmentFilterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Adjustment Type</label>
                        <select name="adjustment_type" class="w-full rounded-md border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">All Types</option>
                            <option value="ADJUSTMENT_IN">Positive (+)</option>
                            <option value="ADJUSTMENT_OUT">Negative (-)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Station</label>
                        <select name="station_id" class="w-full rounded-md border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">All Stations</option>
                            <!-- Stations will be populated dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                        <input type="date" name="date_from" class="w-full rounded-md border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date To</label>
                        <input type="date" name="date_to" class="w-full rounded-md border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Search</label>
                        <input type="text" name="search" placeholder="Notes, reasons..." class="w-full rounded-md border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <div class="md:col-span-5 flex items-center justify-end space-x-3">
                        <button type="button" onclick="clearAdjustmentFilters()" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">
                            Clear Filters
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm font-medium">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Adjustments History Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Adjustment History</h3>
                        <p class="mt-1 text-sm text-slate-600">Complete audit trail of inventory adjustments</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-slate-500">{{ $adjustments->count() }} of {{ $adjustments->total() }} adjustments</span>
                    </div>
                </div>
            </div>

            @if($adjustments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Adjustment</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tank & Station</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type & Impact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Financial Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Authorization</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reason & Notes</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($adjustments as $adjustment)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg {{ $adjustment->movement_type == 'ADJUSTMENT_IN' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center">
                                                    @if($adjustment->movement_type == 'ADJUSTMENT_IN')
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
                                                <div class="text-sm font-medium text-slate-900">#{{ $adjustment->id }}</div>
                                                <div class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($adjustment->movement_timestamp)->format('M d, Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900">Tank {{ $adjustment->tank_number }}</div>
                                        <div class="text-sm text-slate-500">{{ $adjustment->station_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $adjustment->movement_type == 'ADJUSTMENT_IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $adjustment->movement_type == 'ADJUSTMENT_IN' ? 'Increase' : 'Decrease' }}
                                            </span>
                                        </div>
                                        <div class="text-sm mt-1 {{ $adjustment->movement_type == 'ADJUSTMENT_IN' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $adjustment->movement_type == 'ADJUSTMENT_IN' ? '+' : '-' }}{{ number_format($adjustment->quantity_liters, 0) }} L
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($adjustment->unit_cost)
                                            <div class="text-sm text-slate-900">UGX {{ number_format($adjustment->unit_cost, 2) }}/L</div>
                                        @endif
                                        @if($adjustment->total_cost)
                                            <div class="text-sm text-slate-500">Total: UGX {{ number_format($adjustment->total_cost, 0) }}</div>
                                        @endif
                                        <div class="text-xs text-slate-400">
                                            Balance: {{ number_format($adjustment->running_balance_liters, 0) }} L
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($adjustment->created_by_name)
                                            <div class="text-sm text-slate-900">{{ $adjustment->created_by_name }}</div>
                                        @endif
                                        <div class="text-sm text-slate-500">{{ $adjustment->movement_category ?? 'NORMAL_OPERATION' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($adjustment->movement_reason)
                                            <div class="text-sm text-slate-900">{{ $adjustment->movement_reason }}</div>
                                        @endif
                                        @if($adjustment->movement_notes)
                                            <div class="text-sm text-slate-500 max-w-xs truncate">{{ $adjustment->movement_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewAdjustmentDetails({{ $adjustment->id }})" class="text-purple-600 hover:text-purple-900 mr-3">
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
                    {{ $adjustments->links('pagination::tailwind') }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No adjustments found</h3>
                    <p class="mt-1 text-sm text-slate-500">No inventory adjustments have been recorded yet.</p>
                    <div class="mt-6">
                        <button onclick="showNewAdjustmentModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                            Make First Adjustment
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Adjustment Details Modal -->
<div id="adjustmentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="adjustment-modal-title">Adjustment Details</h3>
                        <div class="mt-4" id="adjustment-modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="closeAdjustmentModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-600 text-base font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Adjustment Modal -->
<div id="newAdjustmentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900">New Inventory Adjustment</h3>
                        <div class="mt-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-amber-800">Authorization Required</h3>
                                        <p class="mt-2 text-sm text-amber-700">Inventory adjustments require proper authorization and documentation. This feature is currently under development.</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600">Manual adjustments will be available in the next system update with enhanced approval workflows.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="closeNewAdjustmentModal()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-600 text-base font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter Management
let adjustmentFiltersVisible = false;

function toggleAdjustmentFilters() {
    const panel = document.getElementById('adjustmentFilterPanel');
    const toggleText = document.getElementById('adjustmentFilterToggleText');
    const toggleIcon = document.getElementById('adjustmentFilterToggleIcon');
    
    adjustmentFiltersVisible = !adjustmentFiltersVisible;
    
    if (adjustmentFiltersVisible) {
        panel.classList.remove('hidden');
        toggleText.textContent = 'Hide Filters';
        toggleIcon.classList.add('rotate-180');
    } else {
        panel.classList.add('hidden');
        toggleText.textContent = 'Show Filters';
        toggleIcon.classList.remove('rotate-180');
    }
}

function clearAdjustmentFilters() {
    document.getElementById('adjustmentFilterForm').reset();
    applyAdjustmentFilters();
}

function applyAdjustmentFilters() {
    const form = document.getElementById('adjustmentFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    window.location.href = window.location.pathname + '?' + params.toString();
}

// Adjustment Operations
function refreshAdjustments() {
    Swal.fire({
        title: 'Refreshing Adjustments',
        text: 'Please wait while we update the adjustment data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function exportAdjustments() {
    Swal.fire({
        title: 'Export Adjustments',
        text: 'Select your preferred export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel Report',
        cancelButtonText: 'PDF Audit',
        showDenyButton: true,
        denyButtonText: 'CSV Data'
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
    Swal.fire('Export Started', 'Your Excel report is being prepared...', 'success');
    // TODO: Implement Excel export
}

function exportToCSV() {
    Swal.fire('Export Started', 'Your CSV data export is being prepared...', 'success');
    // TODO: Implement CSV export
}

function exportToPDF() {
    Swal.fire('Export Started', 'Your PDF audit report is being prepared...', 'success');
    // TODO: Implement PDF export
}

// Modal Management
function showNewAdjustmentModal() {
    document.getElementById('newAdjustmentModal').classList.remove('hidden');
}

function closeNewAdjustmentModal() {
    document.getElementById('newAdjustmentModal').classList.add('hidden');
}

function viewAdjustmentDetails(adjustmentId) {
    document.getElementById('adjustmentModal').classList.remove('hidden');
    document.getElementById('adjustment-modal-content').innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-5/6 mb-3"></div>
            <div class="h-4 bg-slate-200 rounded w-2/3"></div>
        </div>
    `;
    
    // TODO: Replace with actual AJAX call to get adjustment details
    setTimeout(() => {
        document.getElementById('adjustment-modal-content').innerHTML = `
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Adjustment Information</h4>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-500">Adjustment ID</dt>
                        <dd class="text-slate-900">#${adjustmentId}</dd>
                        <dt class="text-slate-500">Reference Table</dt>
                        <dd class="text-slate-900">adjustments</dd>
                        <dt class="text-slate-500">Reference ID</dt>
                        <dd class="text-slate-900">#${adjustmentId}</dd>
                        <dt class="text-slate-500">Movement Category</dt>
                        <dd class="text-slate-900">CORRECTION</dd>
                    </dl>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Authorization Details</h4>
                    <dl class="grid grid-cols-1 gap-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Created By</dt>
                            <dd class="text-slate-900">John Smith</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Authorized By</dt>
                            <dd class="text-slate-900">Jane Doe</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Approval Status</dt>
                            <dd class="text-emerald-600">Approved</dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-slate-900 mb-3">Audit Trail</h4>
                    <p class="text-sm text-slate-700">Complete audit information for this adjustment including IP address, user agent, and system validation checks.</p>
                </div>
            </div>
        `;
    }, 1000);
}

function closeAdjustmentModal() {
    document.getElementById('adjustmentModal').classList.add('hidden');
}

// Error Handling
function handleAdjustmentError(error, context = 'adjustment operation') {
    console.error('Adjustment Error:', error);
    
    Swal.fire({
        icon: 'error',
        title: 'Operation Failed',
        text: `The ${context} could not be completed. Please try again or contact support if the problem persists.`,
        confirmButtonColor: '#dc2626'
    });
}

// Form Submission Handler
document.getElementById('adjustmentFilterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    applyAdjustmentFilters();
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
    const adjustmentModal = document.getElementById('adjustmentModal');
    const newAdjustmentModal = document.getElementById('newAdjustmentModal');
    
    if (event.target === adjustmentModal) {
        closeAdjustmentModal();
    }
    
    if (event.target === newAdjustmentModal) {
        closeNewAdjustmentModal();
    }
});
</script>
@endsection