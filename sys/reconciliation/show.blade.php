@extends('layouts.app')

@section('title', 'Reconciliation #' . $reconciliation->id)

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reconciliation.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <div class="h-6 w-px bg-slate-300"></div>
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Reconciliation #{{ $reconciliation->id }}</h1>
                    <p class="text-sm text-slate-600">{{ $reconciliation->station_name }} • {{ \Carbon\Carbon::parse($reconciliation->reconciliation_date)->format('M j, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                {{-- Status Badge --}}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($reconciliation->reconciliation_status === 'BALANCED') bg-emerald-100 text-emerald-800
                    @elseif($reconciliation->reconciliation_status === 'MINOR_VARIANCE') bg-yellow-100 text-yellow-800
                    @elseif($reconciliation->reconciliation_status === 'SIGNIFICANT_VARIANCE') bg-orange-100 text-orange-800
                    @elseif($reconciliation->reconciliation_status === 'CRITICAL_VARIANCE') bg-red-100 text-red-800
                    @else bg-purple-100 text-purple-800 @endif">
                    {{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_status))) }}
                </span>

                {{-- Approval Button --}}
                @if($isAutoApproved && !$reconciliation->approved_by)
                    <button onclick="approveReconciliation({{ $reconciliation->id }})"
                            class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approve
                    </button>
                @elseif($reconciliation->approved_by)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Approved
                    </span>
                @endif
            </div>
        </div>

        {{-- Tabs Container --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            {{-- Tab Navigation --}}
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button id="tab-overview" class="tab-btn active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" onclick="switchTab('overview')">
                        Overview
                    </button>
                    <button id="tab-financial" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700" onclick="switchTab('financial')">
                        Financial
                    </button>
                    <button id="tab-variances" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700" onclick="switchTab('variances')">
                        Variances
                        @if($variances->count() > 0)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">{{ $variances->count() }}</span>
                        @endif
                    </button>
                    <button id="tab-audit" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700" onclick="switchTab('audit')">
                        Audit Trail
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="h-[calc(100vh-280px)] overflow-y-auto">
                {{-- Overview Tab --}}
                <div id="content-overview" class="tab-content active p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Main Content --}}
                        <div class="lg:col-span-2 space-y-6">
                            {{-- Summary Cards --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <p class="text-xs text-blue-600 font-medium uppercase">Opening Stock</p>
                                    <p class="text-lg font-bold text-blue-900">{{ number_format($reconciliation->opening_stock_total_liters ?? 0, 0) }}L</p>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-4">
                                    <p class="text-xs text-emerald-600 font-medium uppercase">Deliveries</p>
                                    <p class="text-lg font-bold text-emerald-900">{{ number_format($reconciliation->total_deliveries_liters ?? 0, 0) }}L</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-4">
                                    <p class="text-xs text-amber-600 font-medium uppercase">Sales</p>
                                    <p class="text-lg font-bold text-amber-900">{{ number_format($reconciliation->calculated_sales_liters ?? 0, 0) }}L</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-4">
                                    <p class="text-xs text-purple-600 font-medium uppercase">Closing Stock</p>
                                    <p class="text-lg font-bold text-purple-900">{{ number_format($reconciliation->closing_stock_total_liters ?? 0, 0) }}L</p>
                                </div>
                            </div>

                            {{-- Variance Summary --}}
                            <div class="bg-white border border-slate-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Variance Analysis</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center p-4 border border-slate-200 rounded-lg">
                                        <p class="text-sm text-slate-600 font-medium">Total Variance</p>
                                        <p class="text-xl font-bold text-slate-900">{{ number_format(abs($reconciliation->total_variance_liters ?? 0), 1) }}L</p>
                                        <p class="text-xs text-slate-500">{{ number_format(abs($reconciliation->total_variance_percentage ?? 0), 2) }}%</p>
                                    </div>
                                    <div class="text-center p-4 border border-slate-200 rounded-lg">
                                        <p class="text-sm text-slate-600 font-medium">Stock Variance</p>
                                        <p class="text-xl font-bold text-slate-900">{{ number_format(abs($reconciliation->stock_variance_liters ?? 0), 1) }}L</p>
                                        <p class="text-xs text-slate-500">{{ number_format(abs($reconciliation->stock_variance_percentage ?? 0), 2) }}%</p>
                                    </div>
                                    <div class="text-center p-4 border border-slate-200 rounded-lg">
                                        <p class="text-sm text-slate-600 font-medium">Meter Variance</p>
                                        <p class="text-xl font-bold text-slate-900">{{ number_format(abs($reconciliation->meter_sales_variance_liters ?? 0), 1) }}L</p>
                                        <p class="text-xs text-slate-500">{{ number_format(abs($reconciliation->meter_sales_variance_percentage ?? 0), 2) }}%</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Basic Information --}}
                            <div class="bg-white border border-slate-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Reconciliation Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-500">Type</label>
                                        <p class="mt-1 text-sm text-slate-900">{{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_type))) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-500">Scope</label>
                                        <p class="mt-1 text-sm text-slate-900">{{ ucfirst(strtolower(str_replace('_', ' ', $reconciliation->reconciliation_scope))) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-500">Confidence</label>
                                        <p class="mt-1 text-sm text-slate-900">{{ ucfirst(strtolower($reconciliation->reconciliation_confidence ?? 'Medium')) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-500">Quality Score</label>
                                        <p class="mt-1 text-sm text-slate-900">{{ number_format($reconciliation->data_quality_score ?? 75, 1) }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <div class="space-y-6">
                            {{-- Quick Actions --}}
                            <div class="bg-white border border-slate-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h3>
                                <div class="space-y-3">
                                    @if($variances->count() > 0)
                                        <button onclick="switchTab('variances')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-amber-300 text-sm font-medium rounded-md text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            View Variances ({{ $variances->count() }})
                                        </button>
                                    @endif
                                    <button onclick="switchTab('financial')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                        Financial Details
                                    </button>
                                    <button onclick="switchTab('audit')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Audit Trail
                                    </button>
                                </div>
                            </div>

                            {{-- Tolerance Status --}}
                            <div class="bg-white border border-slate-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-slate-900 mb-4">Status</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-600">Within Tolerance</span>
                                        @if($reconciliation->variance_within_tolerance)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Yes</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">No</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-600">Threshold</span>
                                        <span class="text-sm font-medium text-slate-900">{{ number_format($reconciliation->tolerance_threshold_percentage ?? 0.5, 2) }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-600">Critical Variances</span>
                                        <span class="text-sm font-medium text-slate-900">{{ $reconciliation->critical_variances_count ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Financial Tab --}}
                <div id="content-financial" class="tab-content hidden p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Product Breakdown --}}
                        <div class="bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Product Breakdown</h3>
                            <div class="space-y-4">
                                @if(($reconciliation->opening_stock_petrol_95_liters ?? 0) > 0 || ($reconciliation->sales_petrol_95_liters ?? 0) > 0)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-medium text-slate-900 mb-3">Petrol 95</h4>
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                            <div><span class="text-slate-500">Opening</span><p class="font-medium">{{ number_format($reconciliation->opening_stock_petrol_95_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Deliveries</span><p class="font-medium">{{ number_format($reconciliation->deliveries_petrol_95_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Sales</span><p class="font-medium">{{ number_format($reconciliation->sales_petrol_95_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Closing</span><p class="font-medium">{{ number_format($reconciliation->closing_stock_petrol_95_liters ?? 0, 0) }}L</p></div>
                                        </div>
                                    </div>
                                @endif
                                @if(($reconciliation->opening_stock_diesel_liters ?? 0) > 0 || ($reconciliation->sales_diesel_liters ?? 0) > 0)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-medium text-slate-900 mb-3">Diesel</h4>
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                            <div><span class="text-slate-500">Opening</span><p class="font-medium">{{ number_format($reconciliation->opening_stock_diesel_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Deliveries</span><p class="font-medium">{{ number_format($reconciliation->deliveries_diesel_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Sales</span><p class="font-medium">{{ number_format($reconciliation->sales_diesel_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Closing</span><p class="font-medium">{{ number_format($reconciliation->closing_stock_diesel_liters ?? 0, 0) }}L</p></div>
                                        </div>
                                    </div>
                                @endif
                                @if(($reconciliation->opening_stock_petrol_98_liters ?? 0) > 0 || ($reconciliation->sales_petrol_98_liters ?? 0) > 0)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-medium text-slate-900 mb-3">Petrol 98</h4>
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                            <div><span class="text-slate-500">Opening</span><p class="font-medium">{{ number_format($reconciliation->opening_stock_petrol_98_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Deliveries</span><p class="font-medium">{{ number_format($reconciliation->deliveries_petrol_98_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Sales</span><p class="font-medium">{{ number_format($reconciliation->sales_petrol_98_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Closing</span><p class="font-medium">{{ number_format($reconciliation->closing_stock_petrol_98_liters ?? 0, 0) }}L</p></div>
                                        </div>
                                    </div>
                                @endif
                                @if(($reconciliation->opening_stock_kerosene_liters ?? 0) > 0 || ($reconciliation->sales_kerosene_liters ?? 0) > 0)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-medium text-slate-900 mb-3">Kerosene</h4>
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                            <div><span class="text-slate-500">Opening</span><p class="font-medium">{{ number_format($reconciliation->opening_stock_kerosene_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Deliveries</span><p class="font-medium">{{ number_format($reconciliation->deliveries_kerosene_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Sales</span><p class="font-medium">{{ number_format($reconciliation->sales_kerosene_liters ?? 0, 0) }}L</p></div>
                                            <div><span class="text-slate-500">Closing</span><p class="font-medium">{{ number_format($reconciliation->closing_stock_kerosene_liters ?? 0, 0) }}L</p></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Financial Summary --}}
                        <div class="bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Financial Summary</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b border-slate-200 pb-4">
                                    <span class="text-slate-600">Opening Stock Value</span>
                                    <span class="font-medium text-slate-900">UGX {{ number_format($reconciliation->opening_stock_total_value ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-200 pb-4">
                                    <span class="text-slate-600">Deliveries Value</span>
                                    <span class="font-medium text-slate-900">UGX {{ number_format($reconciliation->total_deliveries_value ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-200 pb-4">
                                    <span class="text-slate-600">Sales Value</span>
                                    <span class="font-medium text-slate-900">UGX {{ number_format($reconciliation->calculated_sales_value ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-200 pb-4">
                                    <span class="text-slate-600">Closing Stock Value</span>
                                    <span class="font-medium text-slate-900">UGX {{ number_format($reconciliation->closing_stock_total_value ?? 0, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-4">
                                    <span class="text-lg font-semibold text-slate-900">Total Variance Value</span>
                                    <span class="text-lg font-bold @if(($reconciliation->total_variance_value ?? 0) >= 0) text-red-600 @else text-emerald-600 @endif">
                                        UGX {{ number_format(abs($reconciliation->total_variance_value ?? 0), 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Variances Tab --}}
                <div id="content-variances" class="tab-content hidden p-6">
                    @forelse($variances as $variance)
                        <div class="bg-white border border-slate-200 rounded-lg mb-4 overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                                            @if($variance->risk_level === 'LOW') bg-green-100
                                            @elseif($variance->risk_level === 'MEDIUM') bg-yellow-100
                                            @elseif($variance->risk_level === 'HIGH') bg-orange-100
                                            @else bg-red-100 @endif">
                                            <svg class="w-5 h-5 @if($variance->risk_level === 'LOW') text-green-600 @elseif($variance->risk_level === 'MEDIUM') text-yellow-600 @elseif($variance->risk_level === 'HIGH') text-orange-600 @else text-red-600 @endif" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-medium text-slate-900">{{ ucfirst(strtolower(str_replace('_', ' ', $variance->variance_type))) }}</h4>
                                            <p class="text-sm text-slate-500">Tank {{ $variance->tank_id }} - {{ ucfirst(strtolower($variance->product_type)) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-slate-900">{{ number_format(abs($variance->calculated_variance_liters), 1) }}L</div>
                                        <div class="text-sm text-slate-500">{{ number_format(abs($variance->calculated_variance_percentage), 2) }}%</div>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <span class="block text-xs text-slate-500">Risk Level</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($variance->risk_level === 'LOW') bg-green-100 text-green-800
                                            @elseif($variance->risk_level === 'MEDIUM') bg-yellow-100 text-yellow-800
                                            @elseif($variance->risk_level === 'HIGH') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst(strtolower($variance->risk_level)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Financial Impact</span>
                                        <span class="text-sm font-medium text-slate-900">UGX {{ number_format(abs($variance->financial_impact_net), 0) }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Category</span>
                                        <span class="text-sm font-medium text-slate-900">{{ ucfirst(strtolower($variance->variance_category)) }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Status</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($variance->variance_status === 'APPROVED') bg-green-100 text-green-800
                                            @elseif($variance->variance_status === 'PENDING') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst(strtolower($variance->variance_status)) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button onclick="investigateVariance({{ $variance->id }})" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        Investigate
                                    </button>
                                </div>
                            </div>

                            {{-- Investigation Panel --}}
                            <div id="investigation-panel-{{ $variance->id }}" class="hidden border-t border-slate-200 bg-slate-50">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h5 class="text-lg font-semibold text-slate-900">Investigation Results</h5>
                                        <button onclick="closeInvestigation({{ $variance->id }})" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="investigation-content-{{ $variance->id }}" class="investigation-content">
                                        <div class="text-center py-8">
                                            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="text-slate-500">Loading investigation data...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-slate-900 mb-2">No Variances Found</h3>
                            <p class="text-slate-500">This reconciliation has no detected variances.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Audit Trail Tab --}}
                <div id="content-audit" class="tab-content hidden p-6">
                    @forelse($auditTrail as $audit)
                        <div class="flex items-start space-x-4 pb-6 border-b border-slate-100 last:border-b-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                @if($audit->action_type === 'CREATE') bg-green-100
                                @elseif($audit->action_type === 'UPDATE') bg-blue-100
                                @elseif($audit->action_type === 'APPROVE') bg-emerald-100
                                @else bg-slate-100 @endif">
                                <svg class="w-4 h-4 @if($audit->action_type === 'CREATE') text-green-600 @elseif($audit->action_type === 'UPDATE') text-blue-600 @elseif($audit->action_type === 'APPROVE') text-emerald-600 @else text-slate-600 @endif" fill="currentColor" viewBox="0 0 20 20">
                                    @if($audit->action_type === 'CREATE')
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                    @elseif($audit->action_type === 'UPDATE')
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                    @elseif($audit->action_type === 'APPROVE')
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-slate-900">{{ ucfirst(strtolower($audit->action_type)) }}</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($audit->timestamp)->format('M j, Y g:i A') }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">{{ $audit->change_reason ?? $audit->business_justification ?? 'Reconciliation activity' }}</p>
                                @if($audit->old_value_text || $audit->new_value_text)
                                    <div class="mt-2 text-xs">
                                        @if($audit->old_value_text)
                                            <div class="text-red-600"><span class="font-medium">From:</span> {{ Str::limit($audit->old_value_text, 100) }}</div>
                                        @endif
                                        @if($audit->new_value_text)
                                            <div class="text-green-600"><span class="font-medium">To:</span> {{ Str::limit($audit->new_value_text, 100) }}</div>
                                        @endif
                                    </div>
                                @endif
                                <div class="mt-2 text-xs text-slate-400">User ID: {{ $audit->user_id }} • IP: {{ $audit->ip_address }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-slate-900 mb-2">No Audit Trail</h3>
                            <p class="text-slate-500">No audit records found for this reconciliation.</p>
                        </div>
                    @endforelse
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
// Tab Navigation
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });

    document.querySelectorAll('.tab-btn').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-slate-500');
    });

    document.getElementById(`content-${tabName}`).classList.remove('hidden');
    document.getElementById(`content-${tabName}`).classList.add('active');

    const activeButton = document.getElementById(`tab-${tabName}`);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-slate-500');
}

// CSRF Token
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

// Investigate Variance
async function investigateVariance(varianceId) {
    try {
        const panel = document.getElementById(`investigation-panel-${varianceId}`);
        const content = document.getElementById(`investigation-content-${varianceId}`);

        panel.classList.remove('hidden');

        const response = await fetch(`/reconciliation/variance/${varianceId}/investigate`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            content.innerHTML = buildInvestigationHTML(data.data);
        } else {
            throw new Error(data.error || 'Investigation failed');
        }

    } catch (error) {
        console.error('Investigation error:', error);
        const content = document.getElementById(`investigation-content-${varianceId}`);
        content.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <h3 class="text-lg font-medium text-red-900 mb-2">Investigation Failed</h3>
                <p class="text-red-600">${error.message}</p>
                <button onclick="investigateVariance(${varianceId})" class="mt-4 px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">Retry</button>
            </div>
        `;
    }
}

// Build Investigation HTML
function buildInvestigationHTML(data) {
    const variance = data.variance;
    const investigationData = data.investigation_data;
    const recommendedActions = data.recommended_actions || [];
    const historicalPatterns = data.historical_patterns || [];

    return `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h6 class="font-semibold text-slate-900 mb-3">Variance Details</h6>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Tank:</span>
                        <span class="font-medium">${variance.tank_id}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Product:</span>
                        <span class="font-medium">${variance.product_type.replace(/_/g, ' ')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Variance:</span>
                        <span class="font-medium">${Math.abs(variance.calculated_variance_liters).toFixed(1)}L (${Math.abs(variance.calculated_variance_percentage).toFixed(2)}%)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Financial Impact:</span>
                        <span class="font-medium">UGX ${Math.abs(variance.financial_impact_net).toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h6 class="font-semibold text-slate-900 mb-3">Recommended Actions</h6>
                <div class="space-y-2">
                    ${recommendedActions.map(action => `
                        <div class="flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-slate-700">${action}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>

        ${investigationData.recent_readings && investigationData.recent_readings.length > 0 ? `
        <div class="mt-6 bg-white border border-slate-200 rounded-lg p-4">
            <h6 class="font-semibold text-slate-900 mb-3">Recent Readings (Last 7 Days)</h6>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Volume</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Temperature</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        ${investigationData.recent_readings.slice(0, 10).map(reading => `
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-sm text-slate-900">${new Date(reading.reading_date).toLocaleDateString()}</td>
                                <td class="px-3 py-2 text-sm font-medium text-slate-900">${reading.volume_liters?.toFixed(1) || 'N/A'}L</td>
                                <td class="px-3 py-2 text-sm text-slate-600">${reading.temperature_celsius || 'N/A'}°C</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
    `;
}

// Close Investigation
function closeInvestigation(varianceId) {
    document.getElementById(`investigation-panel-${varianceId}`).classList.add('hidden');
}

// Approve Reconciliation
async function approveReconciliation(reconciliationId) {
    try {
        const result = await Swal.fire({
            title: 'Approve Reconciliation?',
            html: `
                <div class="text-left">
                    <p class="mb-4">This action will approve the reconciliation and cannot be undone.</p>
                    <div class="space-y-3">
                        <div>
                            <label for="approval_notes" class="block text-sm font-medium text-slate-700 mb-1">Approval Notes (Optional)</label>
                            <textarea id="approval_notes" rows="3" class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Enter any notes about this approval..."></textarea>
                        </div>
                        <div>
                            <label for="corrective_actions" class="block text-sm font-medium text-slate-700 mb-1">Corrective Actions (Optional)</label>
                            <textarea id="corrective_actions" rows="2" class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Describe any corrective actions taken..."></textarea>
                        </div>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            width: '500px',
            preConfirm: () => {
                const approvalNotes = document.getElementById('approval_notes').value;
                const correctiveActions = document.getElementById('corrective_actions').value;
                return {
                    approval_notes: approvalNotes,
                    corrective_actions: correctiveActions
                };
            }
        });

        if (result.isConfirmed) {
            const response = await fetch(`/reconciliation/${reconciliationId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(result.value)
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios = window.axios || {};
        window.axios.defaults = window.axios.defaults || {};
        window.axios.defaults.headers = window.axios.defaults.headers || {};
        window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
    }
});
</script>
@endsection
