@extends('layouts.app')

@section('title', 'Purchase Order Details - ' . $po->po_number)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <!-- Header with Navigation -->
    <div class="sticky top-0 z-40 backdrop-blur-md bg-white/90 border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Purchase Orders
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Status Badge -->
                    @switch($po->order_status)
                        @case('PENDING')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                            @break
                        @case('APPROVED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Approved
                            </span>
                            @break
                        @case('PARTIALLY_DELIVERED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Partially Delivered
                            </span>
                            @break
                        @case('FULLY_DELIVERED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Fully Delivered
                            </span>
                            @break
                        @case('CANCELLED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm4.707-4.707a1 1 0 00-1.414-1.414L10 14.586 6.707 11.293a1 1 0 00-1.414 1.414L8.586 16l-3.293 3.293a1 1 0 101.414 1.414L10 17.414l3.293 3.293a1 1 0 001.414-1.414L11.414 16l3.293-3.293z" clip-rule="evenodd"></path>
                                </svg>
                                Cancelled
                            </span>
                            @break
                    @endswitch

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        @if($po->order_status === 'PENDING')
                        <a href="{{ route('purchase-orders.edit', $po->id) }}"
                           class="inline-flex items-center px-3 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        <button onclick="cancelPO({{ $po->id }}, '{{ $po->po_number }}')"
                                class="inline-flex items-center px-3 py-2 border border-red-300 text-red-700 text-sm font-medium rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Cancel
                        </button>
                        @endif

                        <!-- Print Button -->
                        <button onclick="printPO()" class="inline-flex items-center px-3 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- PO Header Information -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $po->po_number }}</h1>
                                <p class="text-sm text-slate-600">Purchase Order Details</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-600">Created</p>
                                <p class="text-lg font-medium text-slate-900">{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($po->created_at)->format('g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Supplier Information -->
                            <div>
                                <h3 class="text-sm font-medium text-slate-900 mb-3">Supplier Information</h3>
                                <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                                    <div>
                                        <p class="text-xs text-slate-500">Company Name</p>
                                        <p class="text-sm font-medium text-slate-900">{{ $po->company_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Supplier Code</p>
                                        <p class="text-sm text-slate-700">{{ $po->supplier_code }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Contact Person</p>
                                        <p class="text-sm text-slate-700">{{ $po->contact_person }}</p>
                                    </div>
                                    <div class="flex space-x-4">
                                        <div class="flex-1">
                                            <p class="text-xs text-slate-500">Email</p>
                                            <p class="text-sm text-slate-700">{{ $po->email }}</p>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-slate-500">Phone</p>
                                            <p class="text-sm text-slate-700">{{ $po->phone }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div>
                                <h3 class="text-sm font-medium text-slate-900 mb-3">Delivery Information</h3>
                                <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                                    <div>
                                        <p class="text-xs text-slate-500">Station</p>
                                        <p class="text-sm font-medium text-slate-900">{{ $po->station_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $po->station_code }}</p>
                                    </div>
                                    <div class="flex space-x-4">
                                        <div class="flex-1">
                                            <p class="text-xs text-slate-500">Expected Date</p>
                                            <p class="text-sm text-slate-700">{{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') }}</p>
                                        </div>
                                        @if($po->expected_delivery_time)
                                        <div class="flex-1">
                                            <p class="text-xs text-slate-500">Expected Time</p>
                                            <p class="text-sm text-slate-700">{{ \Carbon\Carbon::parse($po->expected_delivery_time)->format('g:i A') }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product & Pricing Details -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <h3 class="text-lg font-medium text-slate-900">Product & Pricing Details</h3>
                    </div>

                    <div class="p-6">
                        <!-- Product Information -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-medium text-slate-900">Product Information</h4>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ str_replace('_', ' ', $po->product_type) }}
                                </span>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-slate-900">{{ number_format($po->ordered_quantity_liters, 0) }}</p>
                                        <p class="text-sm text-slate-600">Liters Ordered</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-emerald-600">UGX {{ number_format($po->agreed_price_per_liter, 4) }}</p>
                                        <p class="text-sm text-slate-600">Price per Liter</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-blue-600">UGX {{ number_format($po->total_order_value, 0) }}</p>
                                        <p class="text-sm text-slate-600">Total Order Value</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div>
                            <h4 class="text-sm font-medium text-slate-900 mb-4">Cost Breakdown</h4>
                            <div class="bg-slate-50 rounded-lg overflow-hidden">
                                <table class="min-w-full">
                                    <tbody class="divide-y divide-slate-200">
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-slate-600">Agreed Price per Liter</td>
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900 text-right">UGX {{ number_format($po->agreed_price_per_liter, 4) }}</td>
                                        </tr>
                                        @if($po->transport_cost_per_liter > 0)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-slate-600">Transport Cost per Liter</td>
                                            <td class="px-4 py-3 text-sm text-slate-900 text-right">UGX {{ number_format($po->transport_cost_per_liter, 4) }}</td>
                                        </tr>
                                        @endif
                                        @if($po->other_charges_per_liter > 0)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-slate-600">Other Charges per Liter</td>
                                            <td class="px-4 py-3 text-sm text-slate-900 text-right">UGX {{ number_format($po->other_charges_per_liter, 4) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="bg-slate-100">
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900">Total Cost per Liter</td>
                                            <td class="px-4 py-3 text-sm font-bold text-slate-900 text-right">
                                                UGX {{ number_format($po->agreed_price_per_liter + $po->transport_cost_per_liter + $po->other_charges_per_liter, 4) }}
                                            </td>
                                        </tr>
                                        <tr class="bg-blue-50">
                                            <td class="px-4 py-3 text-base font-medium text-blue-900">Total Order Value</td>
                                            <td class="px-4 py-3 text-lg font-bold text-blue-900 text-right">UGX {{ number_format($po->total_order_value, 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Information -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <h3 class="text-lg font-medium text-slate-900">Contract Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Contract Number</p>
                                    <p class="text-lg text-slate-700">{{ $po->contract_number }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm text-emerald-600 font-medium">Active Contract</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Delivery Status -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Delivery Status</h3>

                    @if($deliveryStatus->delivery_count > 0)
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">Delivered</span>
                                <span class="font-medium text-slate-900">
                                    {{ number_format($deliveryStatus->total_delivered, 0) }}L / {{ number_format($po->ordered_quantity_liters, 0) }}L
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, ($deliveryStatus->total_delivered / $po->ordered_quantity_liters) * 100) }}%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ number_format(min(100, ($deliveryStatus->total_delivered / $po->ordered_quantity_liters) * 100), 1) }}% Complete
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Deliveries Made:</span>
                                <span class="font-medium text-slate-900">{{ $deliveryStatus->delivery_count }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Remaining:</span>
                                <span class="font-medium text-slate-900">{{ number_format($po->ordered_quantity_liters - $deliveryStatus->total_delivered, 0) }}L</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                        </svg>
                        <h4 class="mt-2 text-sm font-medium text-slate-900">No Deliveries Yet</h4>
                        <p class="mt-1 text-sm text-slate-500">Awaiting delivery from supplier</p>
                    </div>
                    @endif
                </div>

                <!-- Timeline -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Timeline</h3>

                    <div class="flow-root">
                        <ul class="-mb-8">
                            <!-- PO Created -->
                            <li>
                                <div class="relative pb-8">
                                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200"></div>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm text-slate-500">
                                                    PO Created on {{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- PO Approved -->
                            @if($po->approved_at)
                            <li>
                                <div class="relative pb-8">
                                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200"></div>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm text-slate-500">
                                                    Auto-approved on {{ \Carbon\Carbon::parse($po->approved_at)->format('M d, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif

                            <!-- Expected Delivery -->
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @if(\Carbon\Carbon::parse($po->expected_delivery_date)->isPast() && $po->order_status !== 'FULLY_DELIVERED')
                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </span>
                                            @elseif($po->order_status === 'FULLY_DELIVERED')
                                            <span class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </span>
                                            @else
                                            <span class="h-8 w-8 rounded-full bg-amber-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm text-slate-500">
                                                    Expected delivery: {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') }}
                                                    @if($po->expected_delivery_time)
                                                    at {{ \Carbon\Carbon::parse($po->expected_delivery_time)->format('g:i A') }}
                                                    @endif
                                                </p>
                                                @if(\Carbon\Carbon::parse($po->expected_delivery_date)->isPast() && $po->order_status !== 'FULLY_DELIVERED')
                                                <p class="text-xs text-red-600 font-medium">Overdue</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                {{-- @if($po->order_status === 'APPROVED' || $po->order_status === 'PARTIALLY_DELIVERED')
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button onclick="updateDeliveryStatus()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-300 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Update Delivery Status
                        </button>
                    </div>
                </div>
                @endif --}}
            </div>
        </div>
    </div>
</div>

<script>
// Print PO Function
function printPO() {
    window.print();
}

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

// Update Delivery Status Function - Matches Controller receive() method
async function updateDeliveryStatus() {
    const result = await Swal.fire({
        title: 'Update Delivery Status',
        text: 'This will sync the PO status based on completed deliveries.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Update Status',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/purchase-orders/{{ $po->id }}/receive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: 'Status Updated!',
                    text: `PO status updated to: ${data.new_status}`,
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
                text: error.message || 'Failed to update delivery status',
                icon: 'error'
            });
        }
    }
}

// Print styles
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .backdrop-blur-sm,
    .bg-white\/80 {
        background: white !important;
        backdrop-filter: none !important;
    }
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .backdrop-blur-sm,
    .bg-white\/80 {
        background: white !important;
        backdrop-filter: none !important;
    }

    .shadow-lg,
    .shadow-slate-900\/5 {
        box-shadow: none !important;
    }

    .border-slate-200\/50 {
        border-color: #e2e8f0 !important;
    }
}
</style>
@endsection
