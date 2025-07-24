@extends('layouts.app')

@section('title', 'Supplier Details - ' . ($supplier->company_name ?? 'Unknown'))

@section('content')
<div class="min-h-screen bg-slate-50/50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200/60">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Back Navigation -->
                    <button onclick="window.history.back()" class="p-2 text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm">
                        <a href="{{ route('suppliers.index') }}" class="text-slate-500 hover:text-slate-700 transition-colors duration-200">Suppliers</a>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-slate-900 font-medium">{{ $supplier->company_name ?? 'Supplier Details' }}</span>
                    </nav>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button onclick="exportSupplierData()" class="inline-flex items-center px-3 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>

                    @if(in_array(auth()->user()->role ?? '', ['CEO', 'SYSTEM_ADMIN']) ||
                        (isset($hasPermission) && $hasPermission('SUPPLIER_EDIT')))
                        <button onclick="editSupplier()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transform hover:scale-[1.02] transition-all duration-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Supplier
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Supplier Header Card -->
            <div class="bg-white rounded-xl border border-slate-200/60 p-6 mb-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                <span class="text-xl font-bold text-white">
                                    {{ substr($supplier->company_name ?? '', 0, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ $supplier->company_name ?? 'N/A' }}</h1>
                            <p class="text-lg text-slate-600 mt-1">{{ $supplier->supplier_code ?? 'N/A' }}</p>
                            <p class="text-sm text-slate-500 mt-2">{{ $supplier->contact_person ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Status & Quick Info -->
                    <div class="text-right">
                        <div class="flex items-center justify-end mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $supplier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                                <span class="w-2 h-2 rounded-full mr-2 {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-gray-500' }}"></span>
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-500">
                            <div>Payment Terms: {{ $supplier->payment_terms_days ?? 0 }} days</div>
                            <div>Currency: {{ $supplier->currency_code ?? 'N/A' }}</div>
                            <div>Added: {{ $supplier->created_at ? \Carbon\Carbon::parse($supplier->created_at)->format('M j, Y') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
                <div class="border-b border-slate-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button onclick="showTab('overview')" class="tab-button active py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap" data-tab="overview">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Overview
                            </div>
                        </button>

                        <button onclick="showTab('deliveries')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap" data-tab="deliveries">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Deliveries
                                @if(isset($relatedData['recent_deliveries']) && count($relatedData['recent_deliveries']) > 0)
                                    <span class="ml-2 bg-blue-100 text-blue-800 text-xs rounded-full px-2 py-0.5">{{ count($relatedData['recent_deliveries']) }}</span>
                                @endif
                            </div>
                        </button>

                        <button onclick="showTab('contracts')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap" data-tab="contracts">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Contracts
                                @if(isset($relatedData['active_contracts']) && count($relatedData['active_contracts']) > 0)
                                    <span class="ml-2 bg-emerald-100 text-emerald-800 text-xs rounded-full px-2 py-0.5">{{ count($relatedData['active_contracts']) }}</span>
                                @endif
                            </div>
                        </button>

                        <button onclick="showTab('performance')" class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap" data-tab="performance">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Performance
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Overview Tab -->
                    <div id="overview-tab" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Contact Information -->
                            <div class="lg:col-span-2">
                                <div class="bg-slate-50/50 rounded-lg p-4 mb-6">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Contact Information
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-slate-900">{{ $supplier->email ?? 'N/A' }}</span>
                                                @if($supplier->email ?? false)
                                                    <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Phone</label>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-slate-900">{{ $supplier->phone ?? 'N/A' }}</span>
                                                @if($supplier->phone ?? false)
                                                    <a href="tel:{{ $supplier->phone }}" class="text-blue-600 hover:text-blue-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Address</label>
                                            <div class="text-sm text-slate-900">
                                                <div>{{ $supplier->address_line_1 ?? 'N/A' }}</div>
                                                @if($supplier->address_line_2 ?? false)
                                                    <div>{{ $supplier->address_line_2 }}</div>
                                                @endif
                                                <div>{{ $supplier->city ?? 'N/A' }}{{ $supplier->postal_code ? ', ' . $supplier->postal_code : '' }}</div>
                                                <div>{{ $supplier->country ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Business Details -->
                                <div class="bg-slate-50/50 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Business Details
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Tax Number</label>
                                            <span class="text-sm text-slate-900">{{ $supplier->tax_number ?? 'Not provided' }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Credit Limit</label>
                                            <span class="text-sm text-slate-900">{{ $supplier->currency_code ?? 'UGX' }} {{ number_format($supplier->credit_limit ?? 0, 2) }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Payment Terms</label>
                                            <span class="text-sm text-slate-900">{{ $supplier->payment_terms_days ?? 0 }} days</span>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-1">Currency</label>
                                            <span class="text-sm text-slate-900">{{ $supplier->currency_code ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="space-y-4">
                                <!-- Purchase Orders -->
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-blue-900">Purchase Orders</h4>
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-2xl font-bold text-blue-900">{{ $relatedData['purchase_orders']->count ?? 0 }}</div>
                                    <div class="text-sm text-blue-700">Total Value: {{ $supplier->currency_code ?? 'UGX' }} {{ number_format($relatedData['purchase_orders']->total_value ?? 0, 2) }}</div>
                                </div>

                                <!-- Recent Deliveries -->
                                <div class="bg-emerald-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-emerald-900">Recent Deliveries</h4>
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="text-2xl font-bold text-emerald-900">{{ count($relatedData['recent_deliveries'] ?? []) }}</div>
                                    <div class="text-sm text-emerald-700">Last 10 deliveries</div>
                                </div>

                                <!-- Active Contracts -->
                                <div class="bg-amber-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-amber-900">Active Contracts</h4>
                                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-2xl font-bold text-amber-900">{{ count($relatedData['active_contracts'] ?? []) }}</div>
                                    <div class="text-sm text-amber-700">Current agreements</div>
                                </div>

                                <!-- Payment Summary -->
                                <div class="bg-purple-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-purple-900">Payments</h4>
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                    <div class="text-2xl font-bold text-purple-900">{{ $relatedData['payment_summary']->payment_count ?? 0 }}</div>
                                    <div class="text-sm text-purple-700">Total Paid: {{ $supplier->currency_code ?? 'UGX' }} {{ number_format($relatedData['payment_summary']->total_paid ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deliveries Tab -->
                    <div id="deliveries-tab" class="tab-content hidden">
                        <div class="bg-slate-50/50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Recent Deliveries
                            </h3>

                            @if(!empty($relatedData['recent_deliveries']) && count($relatedData['recent_deliveries']) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Station</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tank</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-slate-200">
                                            @foreach($relatedData['recent_deliveries'] as $delivery)
                                                <tr class="hover:bg-slate-50 transition-colors duration-150">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                                        {{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('M j, Y') : 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                                        {{ $delivery->station_name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                                        Tank {{ $delivery->tank_number ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                                        {{ number_format($delivery->quantity_delivered_liters ?? 0, 0) }} L
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                                            {{ $delivery->delivery_status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' :
                                                               ($delivery->delivery_status === 'PENDING' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                                                            {{ $delivery->delivery_status ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <h3 class="text-sm font-medium text-slate-900 mb-1">No deliveries found</h3>
                                    <p class="text-sm text-slate-500">This supplier hasn't made any deliveries yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contracts Tab -->
                    <div id="contracts-tab" class="tab-content hidden">
                        <div class="bg-slate-50/50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Active Contracts
                            </h3>

                            @if(!empty($relatedData['active_contracts']) && count($relatedData['active_contracts']) > 0)
                                <div class="space-y-4">
                                    @foreach($relatedData['active_contracts'] as $contract)
                                        <div class="bg-white rounded-lg border border-slate-200 p-4">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="text-sm font-semibold text-slate-900">{{ $contract->contract_number ?? 'N/A' }}</h4>
                                                    <p class="text-sm text-slate-600 mt-1">{{ $contract->product_type ?? 'N/A' }}</p>
                                                    <p class="text-xs text-slate-500 mt-2">
                                                        Valid until: {{ $contract->effective_until ? \Carbon\Carbon::parse($contract->effective_until)->format('M j, Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-lg font-semibold text-slate-900">
                                                        {{ $supplier->currency_code ?? 'UGX' }} {{ number_format($contract->unit_price ?? 0, 2) }}
                                                    </div>
                                                    <div class="text-xs text-slate-500">per unit</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-sm font-medium text-slate-900 mb-1">No active contracts</h3>
                                    <p class="text-sm text-slate-500">No contracts are currently active for this supplier.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Performance Tab -->
                    <div id="performance-tab" class="tab-content hidden">
                        <div class="bg-slate-50/50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Performance Metrics
                            </h3>

                            @if(isset($relatedData['performance']) && $relatedData['performance'])
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                                        <h4 class="text-sm font-medium text-slate-600 mb-2">Overall Score</h4>
                                        <div class="text-2xl font-bold text-slate-900">{{ number_format($relatedData['performance']->overall_performance_score ?? 0, 1) }}</div>
                                        <div class="text-sm text-slate-500">Grade: {{ $relatedData['performance']->performance_grade ?? 'N/A' }}</div>
                                    </div>

                                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                                        <h4 class="text-sm font-medium text-slate-600 mb-2">On-Time Delivery</h4>
                                        <div class="text-2xl font-bold text-emerald-600">
                                            {{ $relatedData['performance']->total_deliveries_completed > 0 ?
                                               number_format(($relatedData['performance']->deliveries_on_time / $relatedData['performance']->total_deliveries_completed) * 100, 1) : 0 }}%
                                        </div>
                                        <div class="text-sm text-slate-500">{{ $relatedData['performance']->deliveries_on_time ?? 0 }}/{{ $relatedData['performance']->total_deliveries_completed ?? 0 }} deliveries</div>
                                    </div>

                                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                                        <h4 class="text-sm font-medium text-slate-600 mb-2">Quality Score</h4>
                                        <div class="text-2xl font-bold text-blue-600">{{ number_format($relatedData['performance']->quantity_accuracy_percentage ?? 0, 1) }}%</div>
                                        <div class="text-sm text-slate-500">Quantity accuracy</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="text-sm font-medium text-slate-900 mb-1">No performance data</h3>
                                    <p class="text-sm text-slate-500">Performance metrics will appear after evaluation periods.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="p-2 bg-blue-50 rounded-lg mr-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Export Supplier Data</h3>
        </div>

        <div class="space-y-3 mb-6">
            <button onclick="exportPDF()" class="w-full flex items-center justify-center px-4 py-3 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export as PDF
            </button>

            <button onclick="exportExcel()" class="w-full flex items-center justify-center px-4 py-3 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export as Excel
            </button>
        </div>

        <div class="flex space-x-3">
            <button onclick="closeExportModal()" class="flex-1 px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors duration-200">
                Cancel
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab management
    window.showTab = function(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active state from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'border-blue-500', 'text-blue-600');
            button.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
        });

        // Show selected tab content
        document.getElementById(tabName + '-tab').classList.remove('hidden');

        // Add active state to selected tab button
        const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeButton) {
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
            activeButton.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
        }
    };

    // Export functions
    window.exportSupplierData = function() {
        document.getElementById('exportModal').classList.remove('hidden');
    };

    window.closeExportModal = function() {
        document.getElementById('exportModal').classList.add('hidden');
    };

    window.exportPDF = function() {
        closeExportModal();
        showNotification('PDF export feature coming soon', 'info');
    };

    window.exportExcel = function() {
        closeExportModal();
        showNotification('Excel export feature coming soon', 'info');
    };

    // Edit supplier function
    window.editSupplier = function() {
        showNotification('Edit functionality coming soon', 'info');
    };

    // Utility functions
    function showNotification(message, type = 'info') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                text: message,
                icon: type === 'error' ? 'error' : type === 'success' ? 'success' : 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + E for edit
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            editSupplier();
        }

        // Tab navigation with numbers
        if (e.key >= '1' && e.key <= '4') {
            const tabs = ['overview', 'deliveries', 'contracts', 'performance'];
            const tabIndex = parseInt(e.key) - 1;
            if (tabs[tabIndex]) {
                showTab(tabs[tabIndex]);
            }
        }
    });

    // Close modals on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExportModal();
        }
    });

    // Close modals on backdrop click
    document.getElementById('exportModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeExportModal();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Tab styling */
.tab-button {
    @apply border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-all duration-200;
}

.tab-button.active {
    @apply border-blue-500 text-blue-600;
}

/* Smooth transitions */
.tab-content {
    @apply transition-all duration-200;
}

/* Enhanced hover effects */
.hover\:scale-\[1\.02\]:hover {
    transform: scale(1.02);
}

/* Modal backdrop */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

/* Status indicator animation */
@keyframes pulse-gentle {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.status-indicator {
    animation: pulse-gentle 2s infinite;
}

/* Card hover effects */
.card-hover {
    @apply transition-all duration-200 hover:shadow-md hover:-translate-y-0.5;
}
</style>
@endpush
