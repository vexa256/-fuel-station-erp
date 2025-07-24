@extends('layouts.app')

@section('title', 'Contract Details - ' . $contract->contract_number)

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-semibold text-slate-900">{{ $contract->contract_number }}</h1>
                        @if($contractStatus['is_active'] && $contractStatus['is_current'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                                Active
                            </span>
                        @elseif($contractStatus['is_active'] && !$contractStatus['is_current'])
                            @if($contractStatus['days_remaining'] < 0)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                    Expired
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                    Future
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">
                                <div class="w-2 h-2 bg-slate-500 rounded-full mr-2"></div>
                                Inactive
                            </span>
                        @endif

                        @if($contractStatus['is_current'] && $contractStatus['days_remaining'] <= 30 && $contractStatus['days_remaining'] > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Expires in {{ $contractStatus['days_remaining'] }} days
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 mt-1">{{ $contract->company_name }} • {{ str_replace('_', ' ', $contract->product_type) }}</p>
                </div>
                <a href="{{ route('contracts.index') }}"
                   class="inline-flex items-center px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Contracts
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Contract Details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Contract Information</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Contract Number</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->contract_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Product Type</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', $contract->product_type) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Base Price per Liter</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $contract->currency_code ?? 'UGX' }} {{ number_format($contract->base_price_per_liter, 4) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Quantity Range</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ number_format($contract->minimum_quantity_liters, 0) }} - {{ number_format($contract->maximum_quantity_liters, 0) }} liters</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Effective Period</dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ \Carbon\Carbon::parse($contract->effective_from)->format('M d, Y') }} -
                                    {{ \Carbon\Carbon::parse($contract->effective_until)->format('M d, Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Created</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ \Carbon\Carbon::parse($contract->created_at)->format('M d, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Supplier Details</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Company Name</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $contract->company_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Supplier Code</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->supplier_code }}</dd>
                            </div>
                            @if($contract->contact_person)
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Contact Person</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->contact_person }}</dd>
                            </div>
                            @endif
                            @if($contract->email)
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Email</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->email }}</dd>
                            </div>
                            @endif
                            @if($contract->phone)
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Phone</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->phone }}</dd>
                            </div>
                            @endif
                            @if($contract->payment_terms_days)
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Payment Terms</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $contract->payment_terms_days }} days</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Recent Purchase Orders -->
                @if($relatedData['recent_orders']->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900">Recent Purchase Orders</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PO Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($relatedData['recent_orders'] as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $order->po_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ number_format($order->quantity_ordered_liters, 0) }} L</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $contract->currency_code ?? 'UGX' }} {{ number_format($order->total_order_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'PENDING' => 'bg-amber-100 text-amber-800',
                                                'APPROVED' => 'bg-blue-100 text-blue-800',
                                                'PARTIALLY_DELIVERED' => 'bg-indigo-100 text-indigo-800',
                                                'FULLY_DELIVERED' => 'bg-emerald-100 text-emerald-800',
                                                'CANCELLED' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->po_status] ?? 'bg-slate-100 text-slate-800' }}">
                                            {{ str_replace('_', ' ', $order->po_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Usage Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Usage Summary</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Total Orders</span>
                            <span class="text-sm font-semibold text-slate-900">{{ $relatedData['usage_summary']->total_orders ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Total Quantity</span>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($relatedData['usage_summary']->total_quantity ?? 0, 0) }} L</span>
                        </div>
                        @if($relatedData['usage_summary']->total_quantity > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Avg. Order Size</span>
                            <span class="text-sm font-semibold text-slate-900">
                                {{ number_format(($relatedData['usage_summary']->total_quantity ?? 0) / max(1, $relatedData['usage_summary']->total_orders ?? 1), 0) }} L
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Contract Status -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Contract Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Active Status</span>
                            <span class="text-sm font-semibold {{ $contractStatus['is_active'] ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $contractStatus['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Current Period</span>
                            <span class="text-sm font-semibold {{ $contractStatus['is_current'] ? 'text-emerald-600' : 'text-slate-600' }}">
                                {{ $contractStatus['is_current'] ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($contractStatus['is_current'])
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Days Remaining</span>
                            <span class="text-sm font-semibold {{ $contractStatus['days_remaining'] <= 30 ? 'text-amber-600' : 'text-slate-900' }}">
                                {{ $contractStatus['days_remaining'] }} days
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || auth()->user()->can_approve_purchases)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($contractStatus['is_active'] && $contractStatus['is_current'])
                        <button onclick="createPurchaseOrder()"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Purchase Order
                        </button>
                        @endif
                        <button onclick="viewAllOrders()"
                                class="w-full inline-flex items-center justify-center px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            View All Orders
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function createPurchaseOrder() {
    Swal.fire({
        title: 'Create Purchase Order',
        text: 'This will redirect you to create a new purchase order using this contract.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to PO creation with contract pre-selected
            window.location.href = `/purchase-orders/create?contract_id={{ $contract->id }}`;
        }
    });
}

function viewAllOrders() {
    // Redirect to purchase orders filtered by this contract
    window.location.href = `/purchase-orders?contract_id={{ $contract->id }}`;
}
</script>
@endsection
