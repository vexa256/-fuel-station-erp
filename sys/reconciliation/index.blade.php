@extends('layouts.app')

@section('title', 'Reconciliation Management')

@section('content')
{{--
FUEL_ERP - Reconciliation Index View
Controller: ReconciliationController@index
Data Flow: Matches controller's paginated reconciliations with filters
Schema: Exact compliance with reconciliations table fields
--}}

<div class="min-h-screen bg-slate-50 p-4">
    <div class="max-w-7xl mx-auto">
        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Reconciliation Management</h1>
                    <p class="text-slate-600 mt-1">Track and manage fuel inventory reconciliations</p>
                </div>

                @if($isAutoApproved)
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Auto-Approved Access
                        </span>
                        <a href="{{ route('reconciliation.daily') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Reconciliation
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button id="tab-overview"
                            class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600"
                            onclick="switchTab('overview')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Overview
                    </button>
                    <button id="tab-filters"
                            class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300"
                            onclick="switchTab('filters')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                        </svg>
                        Filters & Search
                    </button>
                    <button id="tab-reconciliations"
                            class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300"
                            onclick="switchTab('reconciliations')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Reconciliations
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                            {{ $reconciliations->total() }}
                        </span>
                    </button>
                    <button id="tab-analytics"
                            class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300"
                            onclick="switchTab('analytics')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analytics
                    </button>
                </nav>
            </div>

            {{-- Tab Content Container --}}
            <div class="h-[calc(100vh-200px)] overflow-hidden">

                {{-- Overview Tab --}}
                <div id="content-overview" class="tab-content active h-full overflow-y-auto p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
                        {{-- Quick Stats --}}
                        <div class="lg:col-span-1 space-y-4">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Statistics</h3>

                            {{-- Total Reconciliations Card --}}
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-blue-600 text-sm font-medium">Total Reconciliations</p>
                                        <p class="text-2xl font-bold text-blue-900">{{ $reconciliations->total() }}</p>
                                    </div>
                                    <div class="p-3 bg-blue-500 rounded-full">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Distribution --}}
                            @php
                                $statusCounts = $reconciliations->groupBy('reconciliation_status');
                                $balancedCount = $statusCounts->get('BALANCED', collect())->count();
                                $varianceCount = $reconciliations->total() - $balancedCount;
                            @endphp

                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-lg border border-emerald-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-emerald-600 text-sm font-medium">Balanced</p>
                                        <p class="text-2xl font-bold text-emerald-900">{{ $balancedCount }}</p>
                                    </div>
                                    <div class="p-3 bg-emerald-500 rounded-full">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-lg border border-amber-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-amber-600 text-sm font-medium">With Variances</p>
                                        <p class="text-2xl font-bold text-amber-900">{{ $varianceCount }}</p>
                                    </div>
                                    <div class="p-3 bg-amber-500 rounded-full">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Activity --}}
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Recent Reconciliations</h3>
                            <div class="bg-white rounded-lg border border-slate-200 h-[calc(100%-3rem)]">
                                <div class="overflow-y-auto h-full">
                                    @forelse($reconciliations->take(5) as $reconciliation)
                                        <div class="p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors duration-150">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        @if($reconciliation->reconciliation_status === 'BALANCED')
                                                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-900">
                                                            {{ $reconciliation->station_name }} - {{ $reconciliation->reconciliation_date }}
                                                        </p>
                                                        <p class="text-xs text-slate-500">
                                                            {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_type))) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($reconciliation->reconciliation_status === 'BALANCED') bg-emerald-100 text-emerald-800
                                                        @elseif($reconciliation->reconciliation_status === 'MINOR_VARIANCE') bg-yellow-100 text-yellow-800
                                                        @elseif($reconciliation->reconciliation_status === 'SIGNIFICANT_VARIANCE') bg-orange-100 text-orange-800
                                                        @else bg-red-100 text-red-800 @endif">
                                                        {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_status))) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center">
                                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="text-slate-500">No reconciliations found</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters Tab --}}
                <div id="content-filters" class="tab-content hidden h-full overflow-y-auto p-6">
                    <div class="max-w-4xl mx-auto">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6">Advanced Filters & Search</h3>

                        <form method="GET" action="{{ route('reconciliation.index') }}" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {{-- Station Filter --}}
                                <div>
                                    <label for="station_id" class="block text-sm font-medium text-slate-700 mb-2">Station</label>
                                    <select name="station_id" id="station_id" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="">All Stations</option>
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}" {{ (request('station_id') == $station->id) ? 'selected' : '' }}>
                                                {{ $station->station_name }} ({{ $station->station_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date From --}}
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                                    <input type="date" name="date_from" id="date_from"
                                           value="{{ request('date_from') }}"
                                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>

                                {{-- Date To --}}
                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-2">Date To</label>
                                    <input type="date" name="date_to" id="date_to"
                                           value="{{ request('date_to') }}"
                                           class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>

                                {{-- Status Filter --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                                    <select name="status" id="status" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="">All Statuses</option>
                                        <option value="BALANCED" {{ request('status') === 'BALANCED' ? 'selected' : '' }}>Balanced</option>
                                        <option value="MINOR_VARIANCE" {{ request('status') === 'MINOR_VARIANCE' ? 'selected' : '' }}>Minor Variance</option>
                                        <option value="SIGNIFICANT_VARIANCE" {{ request('status') === 'SIGNIFICANT_VARIANCE' ? 'selected' : '' }}>Significant Variance</option>
                                        <option value="CRITICAL_VARIANCE" {{ request('status') === 'CRITICAL_VARIANCE' ? 'selected' : '' }}>Critical Variance</option>
                                        <option value="INVESTIGATION_REQUIRED" {{ request('status') === 'INVESTIGATION_REQUIRED' ? 'selected' : '' }}>Investigation Required</option>
                                    </select>
                                </div>

                                {{-- Records Per Page --}}
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-slate-700 mb-2">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                                <a href="{{ route('reconciliation.index') }}"
                                   class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Clear Filters
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                                    </svg>
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Reconciliations Tab --}}
                <div id="content-reconciliations" class="tab-content hidden h-full overflow-hidden">
                    <div class="h-full flex flex-col">
                        {{-- Table Header --}}
                        <div class="flex-shrink-0 px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">Reconciliation Records</h3>
                                <div class="text-sm text-slate-500">
                                    Showing {{ $reconciliations->firstItem() ?? 0 }} to {{ $reconciliations->lastItem() ?? 0 }}
                                    of {{ $reconciliations->total() }} results
                                </div>
                            </div>
                        </div>

                        {{-- Table Content --}}
                        <div class="flex-1 overflow-y-auto">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date & Station</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type & Scope</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Variance</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Financial Impact</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quality</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        @forelse($reconciliations as $reconciliation)
                                            <tr class="hover:bg-slate-50 transition-colors duration-150">
                                                {{-- Date & Station --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900">
                                                            {{ \Carbon\Carbon::parse($reconciliation->reconciliation_date)->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-sm text-slate-500">
                                                            {{ $reconciliation->station_name }}
                                                            <span class="text-xs text-slate-400">({{ $reconciliation->station_code }})</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Type & Scope --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div class="text-sm text-slate-900">
                                                            {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_type))) }}
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_scope))) }}
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Status --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($reconciliation->reconciliation_status === 'BALANCED') bg-emerald-100 text-emerald-800
                                                        @elseif($reconciliation->reconciliation_status === 'MINOR_VARIANCE') bg-yellow-100 text-yellow-800
                                                        @elseif($reconciliation->reconciliation_status === 'SIGNIFICANT_VARIANCE') bg-orange-100 text-orange-800
                                                        @elseif($reconciliation->reconciliation_status === 'CRITICAL_VARIANCE') bg-red-100 text-red-800
                                                        @else bg-purple-100 text-purple-800 @endif">
                                                        {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_status))) }}
                                                    </span>
                                                </td>

                                                {{-- Variance --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900">
                                                            {{ number_format(abs($reconciliation->total_variance_liters ?? 0), 1) }}L
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            {{ number_format(abs($reconciliation->total_variance_percentage ?? 0), 2) }}%
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Financial Impact --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-slate-900">
                                                        UGX {{ number_format(abs($reconciliation->total_variance_value ?? 0), 0) }}
                                                    </div>
                                                </td>

                                                {{-- Quality --}}
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 w-10 h-10">
                                                            @php
                                                                $qualityScore = $reconciliation->data_quality_score ?? 75;
                                                                $qualityColor = $qualityScore >= 90 ? 'emerald' : ($qualityScore >= 70 ? 'yellow' : 'red');
                                                            @endphp
                                                            <div class="w-10 h-10 rounded-full bg-{{ $qualityColor }}-100 flex items-center justify-center">
                                                                <span class="text-xs font-medium text-{{ $qualityColor }}-800">
                                                                    {{ round($qualityScore) }}%
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-xs text-slate-500">
                                                                {{ ucfirst(strtolower($reconciliation->reconciliation_confidence ?? 'Medium')) }} Confidence
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Actions --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end space-x-2">
                                                        <a href="{{ route('reconciliation.show', $reconciliation->id) }}"
                                                           class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>

                                                        @if($isAutoApproved && !$reconciliation->approved_by)
                                                            <button onclick="approveReconciliation({{ $reconciliation->id }})"
                                                                    class="text-emerald-600 hover:text-emerald-900 transition-colors duration-150">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-6 py-12 text-center">
                                                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <p class="text-slate-500 text-lg">No reconciliations found</p>
                                                    <p class="text-slate-400 text-sm mt-1">Try adjusting your filters or date range</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Pagination --}}
                        @if($reconciliations->hasPages())
                            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-white">
                                {{ $reconciliations->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Analytics Tab --}}
                <div id="content-analytics" class="tab-content hidden h-full overflow-y-auto p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
                        {{-- Status Distribution Chart --}}
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4">Status Distribution</h4>
                            <div class="space-y-4">
                                @php
                                    $statusCounts = [
                                        'BALANCED' => $reconciliations->where('reconciliation_status', 'BALANCED')->count(),
                                        'MINOR_VARIANCE' => $reconciliations->where('reconciliation_status', 'MINOR_VARIANCE')->count(),
                                        'SIGNIFICANT_VARIANCE' => $reconciliations->where('reconciliation_status', 'SIGNIFICANT_VARIANCE')->count(),
                                        'CRITICAL_VARIANCE' => $reconciliations->where('reconciliation_status', 'CRITICAL_VARIANCE')->count(),
                                        'INVESTIGATION_REQUIRED' => $reconciliations->where('reconciliation_status', 'INVESTIGATION_REQUIRED')->count(),
                                    ];
                                    $total = array_sum($statusCounts);
                                @endphp

                                @foreach($statusCounts as $status => $count)
                                    @if($count > 0)
                                        @php
                                            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                            $colorClass = [
                                                'BALANCED' => 'emerald',
                                                'MINOR_VARIANCE' => 'yellow',
                                                'SIGNIFICANT_VARIANCE' => 'orange',
                                                'CRITICAL_VARIANCE' => 'red',
                                                'INVESTIGATION_REQUIRED' => 'purple'
                                            ][$status] ?? 'slate';
                                        @endphp
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-4 h-4 bg-{{ $colorClass }}-500 rounded"></div>
                                                <span class="text-sm text-slate-600">{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-slate-900">{{ $count }}</span>
                                                <span class="text-xs text-slate-500">({{ number_format($percentage, 1) }}%)</span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-2">
                                            <div class="bg-{{ $colorClass }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Quality Metrics --}}
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4">Quality Metrics</h4>
                            <div class="space-y-6">
                                @php
                                    $avgQuality = $reconciliations->avg('data_quality_score') ?? 75;
                                    $highConfidence = $reconciliations->where('reconciliation_confidence', 'HIGH')->count();
                                    $mediumConfidence = $reconciliations->where('reconciliation_confidence', 'MEDIUM')->count();
                                    $lowConfidence = $reconciliations->where('reconciliation_confidence', 'LOW')->count();
                                @endphp

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-slate-700">Average Quality Score</span>
                                        <span class="text-lg font-bold text-slate-900">{{ number_format($avgQuality, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-3">
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $avgQuality }}%"></div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <h5 class="text-sm font-medium text-slate-700">Confidence Levels</h5>

                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-slate-600">High Confidence</span>
                                            <span class="text-sm font-medium text-emerald-600">{{ $highConfidence }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-slate-600">Medium Confidence</span>
                                            <span class="text-sm font-medium text-yellow-600">{{ $mediumConfidence }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-slate-600">Low Confidence</span>
                                            <span class="text-sm font-medium text-red-600">{{ $lowConfidence }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Messages --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 5000,
                showConfirmButton: true
            });
        });
    </script>
@endif

<script>
/**
 * Tab Navigation System
 * Eliminates vertical scrolling by containing all content in fixed-height tabs
 */
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });

    // Remove active state from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-slate-500');
    });

    // Show selected tab content
    document.getElementById(`content-${tabName}`).classList.remove('hidden');
    document.getElementById(`content-${tabName}`).classList.add('active');

    // Activate selected tab button
    const activeButton = document.getElementById(`tab-${tabName}`);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-slate-500');
}

/**
 * CSRF Token Setup for AJAX requests
 */
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

/**
 * Approve Reconciliation (CEO/SYSTEM_ADMIN only)
 */
async function approveReconciliation(reconciliationId) {
    try {
        const result = await Swal.fire({
            title: 'Approve Reconciliation?',
            text: 'This action will approve the reconciliation and cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            const response = await fetch(`/reconciliation/${reconciliationId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Approved!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });

                // Reload page to update UI
                window.location.reload();
            } else {
                throw new Error(data.error || 'Approval failed');
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Failed to approve reconciliation',
            timer: 5000,
            showConfirmButton: true
        });
    }
}

/**
 * Initialize on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios = window.axios || {};
        window.axios.defaults = window.axios.defaults || {};
        window.axios.defaults.headers = window.axios.defaults.headers || {};
        window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
    }

    // Initialize with overview tab active
    if (!document.querySelector('.tab-content.active')) {
        switchTab('overview');
    }
});
</script>
@endsection
