@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <!-- Header with Glass Morphism -->
    <div class="sticky top-0 z-40 backdrop-blur-md bg-white/90 border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Purchase Orders</h1>
                        <p class="text-sm text-slate-600">Manage fuel procurement and supplier contracts</p>
                    </div>
                </div>

                <a href="{{ route('purchase-orders.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Purchase Order
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Statistics - Premium Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total POs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 p-6 shadow-lg shadow-slate-900/5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-slate-600 font-medium">Total Orders</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($stats['total_pos']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending POs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-amber-200/50 p-6 shadow-lg shadow-amber-900/5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-amber-700 font-medium">Pending</p>
                        <p class="text-2xl font-semibold text-amber-900">{{ number_format($stats['pending_pos']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Approved POs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-emerald-200/50 p-6 shadow-lg shadow-emerald-900/5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-emerald-700 font-medium">Approved</p>
                        <p class="text-2xl font-semibold text-emerald-900">{{ number_format($stats['approved_pos']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Overdue Deliveries -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-red-200/50 p-6 shadow-lg shadow-red-900/5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-red-700 font-medium">Overdue</p>
                        <p class="text-2xl font-semibold text-red-900">{{ number_format($stats['overdue_deliveries']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-blue-200/50 p-6 shadow-lg shadow-blue-900/5 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-blue-700 font-medium">Pending Value</p>
                        <p class="text-lg font-semibold text-blue-900">UGX {{ number_format($stats['total_value_pending'], 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 p-6 shadow-lg shadow-slate-900/5 mb-6">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search PO number, supplier..."
                           class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg bg-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                </div>

                <!-- Status Filter -->
                <select name="status" class="block w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                    <option value="PARTIALLY_DELIVERED" {{ request('status') == 'PARTIALLY_DELIVERED' ? 'selected' : '' }}>Partially Delivered</option>
                    <option value="FULLY_DELIVERED" {{ request('status') == 'FULLY_DELIVERED' ? 'selected' : '' }}>Fully Delivered</option>
                    <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <!-- Product Type Filter -->
                <select name="product_type" class="block w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    <option value="">All Products</option>
                    <option value="PETROL_95" {{ request('product_type') == 'PETROL_95' ? 'selected' : '' }}>Petrol 95</option>
                    <option value="PETROL_98" {{ request('product_type') == 'PETROL_98' ? 'selected' : '' }}>Petrol 98</option>
                    <option value="DIESEL" {{ request('product_type') == 'DIESEL' ? 'selected' : '' }}>Diesel</option>
                    <option value="KEROSENE" {{ request('product_type') == 'KEROSENE' ? 'selected' : '' }}>Kerosene</option>
                    <option value="JET_A1" {{ request('product_type') == 'JET_A1' ? 'selected' : '' }}>Jet A1</option>
                    <option value="HEAVY_FUEL_OIL" {{ request('product_type') == 'HEAVY_FUEL_OIL' ? 'selected' : '' }}>Heavy Fuel Oil</option>
                    <option value="LIGHT_FUEL_OIL" {{ request('product_type') == 'LIGHT_FUEL_OIL' ? 'selected' : '' }}>Light Fuel Oil</option>
                    <option value="LPG_AUTOGAS" {{ request('product_type') == 'LPG_AUTOGAS' ? 'selected' : '' }}>LPG Autogas</option>
                </select>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Filter
                    </button>
                    <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center justify-center px-3 py-2 border border-slate-300 bg-white text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors duration-200">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Purchase Orders Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Station</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity (L)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Delivery Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/60 divide-y divide-slate-200">
                        @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-slate-900">{{ $po->po_number }}</div>
                                    <div class="text-xs text-slate-500 ml-2">{{ $po->contract_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900 font-medium">{{ $po->company_name }}</div>
                                <div class="text-xs text-slate-500">{{ $po->supplier_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $po->station_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ str_replace('_', ' ', $po->product_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-slate-900 font-medium">{{ number_format($po->ordered_quantity_liters, 0) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-slate-900 font-medium">UGX {{ number_format($po->total_order_value, 0) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @switch($po->order_status)
                                    @case('PENDING')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pending
                                        </span>
                                        @break
                                    @case('APPROVED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Approved
                                        </span>
                                        @break
                                    @case('PARTIALLY_DELIVERED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Partial Delivery
                                        </span>
                                        @break
                                    @case('FULLY_DELIVERED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Delivered
                                        </span>
                                        @break
                                    @case('CANCELLED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- View Button -->
                                    <a href="{{ route('purchase-orders.show', $po->id) }}"
                                       class="inline-flex items-center p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-200"
                                       title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <!-- Edit Button (Only for PENDING) -->
                                    @if($po->order_status === 'PENDING')
                                    <a href="{{ route('purchase-orders.edit', $po->id) }}"
                                       class="inline-flex items-center p-1 rounded-md text-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200"
                                       title="Edit PO">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Cancel Button (Only for PENDING) -->
                                    <button onclick="cancelPO({{ $po->id }}, '{{ $po->po_number }}')"
                                            class="inline-flex items-center p-1 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200"
                                            title="Cancel PO">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-slate-900">No purchase orders found</h3>
                                <p class="mt-1 text-sm text-slate-500">Get started by creating a new purchase order.</p>
                                <div class="mt-6">
                                    <a href="{{ route('purchase-orders.create') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        New Purchase Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($purchaseOrders->hasPages())
            <div class="bg-white/60 px-4 py-3 border-t border-slate-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if ($purchaseOrders->onFirstPage())
                            <span class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-500 bg-white cursor-default">
                                Previous
                            </span>
                        @else
                            <a href="{{ $purchaseOrders->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50">
                                Previous
                            </a>
                        @endif

                        @if ($purchaseOrders->hasMorePages())
                            <a href="{{ $purchaseOrders->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50">
                                Next
                            </a>
                        @else
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-500 bg-white cursor-default">
                                Next
                            </span>
                        @endif
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-700">
                                Showing <span class="font-medium">{{ $purchaseOrders->firstItem() }}</span>
                                to <span class="font-medium">{{ $purchaseOrders->lastItem() }}</span>
                                of <span class="font-medium">{{ $purchaseOrders->total() }}</span> results
                            </p>
                        </div>
                        <div>
                            {{ $purchaseOrders->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div id="success-toast" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5">
    <div class="flex-1 w-0 p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-slate-900">Success!</p>
                <p class="mt-1 text-sm text-slate-500">{{ session('success') }}</p>
            </div>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div id="error-toast" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5">
    <div class="flex-1 w-0 p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-slate-900">Error!</p>
                <p class="mt-1 text-sm text-slate-500">{{ session('error') }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<script>
// Auto-hide toasts
setTimeout(() => {
    const successToast = document.getElementById('success-toast');
    const errorToast = document.getElementById('error-toast');
    if (successToast) successToast.remove();
    if (errorToast) errorToast.remove();
}, 5000);

// Cancel PO Function - Matches Controller destroy() method
async function cancelPO(poId, poNumber) {
    const result = await Swal.fire({
        title: `Cancel Purchase Order?`,
        text: `Are you sure you want to cancel PO ${poNumber}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Cancel PO',
        cancelButtonText: 'Keep PO'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/purchase-orders/${poId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: 'Cancelled!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                window.location.reload();
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            await Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to cancel purchase order',
                icon: 'error'
            });
        }
    }
}
</script>
@endsection
