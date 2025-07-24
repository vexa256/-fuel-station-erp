@extends('layouts.app')

@section('title', 'Edit Purchase Order - ' . $po->po_number)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <!-- Header with Navigation -->
    <div class="sticky top-0 z-40 backdrop-blur-md bg-white/90 border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to PO Details
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-amber-600 to-amber-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Purchase Order</h1>
                        <p class="text-sm text-slate-600">{{ $po->po_number }} - Modify order details</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Status Alert -->
        @if($po->order_status !== 'PENDING')
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-amber-900">Read-Only Mode</h4>
                    <p class="text-xs text-amber-700 mt-1">Only pending purchase orders can be edited. Current status: {{ ucfirst(strtolower(str_replace('_', ' ', $po->order_status))) }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex items-center text-sm text-emerald-600">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-emerald-600 rounded-full bg-emerald-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="ml-2 font-medium">Contract Selected</span>
                    </div>
                    <div class="flex-1 mx-4 h-0.5 bg-emerald-200"></div>
                    <div class="flex items-center text-sm text-amber-600">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-amber-600 rounded-full bg-amber-600 text-white font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 font-medium">Editing Details</span>
                    </div>
                    <div class="flex-1 mx-4 h-0.5 bg-slate-200"></div>
                    <div class="flex items-center text-sm text-slate-400">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-slate-300 rounded-full bg-white">
                            3
                        </div>
                        <span class="ml-2">Update & Save</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5">
                    <div class="px-6 py-4 border-b border-slate-200/60">
                        <h3 class="text-lg font-medium text-slate-900">Purchase Order Details</h3>
                        <p class="mt-1 text-sm text-slate-600">Update the editable fields for this purchase order</p>
                    </div>

                    <form id="editPOForm" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Contract Information (Read-Only) -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2">Supplier Contract (Read-Only)</label>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-slate-900">{{ $po->company_name }}</p>
                                                <p class="text-xs text-slate-600">Contract: {{ $po->contract_number }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-slate-900">{{ str_replace('_', ' ', $po->product_type) }}</p>
                                            <p class="text-xs text-slate-500">UGX {{ number_format($po->base_price_per_liter, 4) }}/L</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-4">
                                        <div class="bg-white rounded-md p-3">
                                            <p class="text-xs text-slate-500">Min Quantity</p>
                                            <p class="text-sm font-medium text-slate-900">{{ number_format($po->minimum_quantity_liters, 0) }}L</p>
                                        </div>
                                        <div class="bg-white rounded-md p-3">
                                            <p class="text-xs text-slate-500">Max Quantity</p>
                                            <p class="text-sm font-medium text-slate-900">{{ number_format($po->maximum_quantity_liters, 0) }}L</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PO Number (Read-Only) -->
                        <div>
                            <label for="po_number_display" class="block text-sm font-medium text-slate-900 mb-2">PO Number (Read-Only)</label>
                            <input type="text"
                                   id="po_number_display"
                                   value="{{ $po->po_number }}"
                                   readonly
                                   class="block w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-sm text-slate-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-500">PO number cannot be changed after creation</p>
                        </div>

                        <!-- Station Selection -->
                        <div>
                            <label for="station_id" class="block text-sm font-medium text-slate-900 mb-2">Delivery Station <span class="text-red-500">*</span></label>
                            <select name="station_id"
                                    id="station_id"
                                    required
                                    {{ $po->order_status !== 'PENDING' ? 'disabled' : '' }}
                                    class="block w-full px-3 py-2 border @error('station_id') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                <option value="">Select delivery station</option>
                                @foreach($stations as $station)
                                <option value="{{ $station->id }}" {{ (old('station_id', $po->station_id) == $station->id) ? 'selected' : '' }}>
                                    {{ $station->station_name }} ({{ $station->station_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('station_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="ordered_quantity_liters" class="block text-sm font-medium text-slate-900 mb-2">Order Quantity (Liters) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number"
                                       name="ordered_quantity_liters"
                                       id="ordered_quantity_liters"
                                       step="0.001"
                                       min="{{ $po->minimum_quantity_liters }}"
                                       max="{{ $po->maximum_quantity_liters }}"
                                       value="{{ old('ordered_quantity_liters', $po->ordered_quantity_liters) }}"
                                       required
                                       {{ $po->order_status !== 'PENDING' ? 'readonly' : '' }}
                                       oninput="calculatePricing()"
                                       class="block w-full px-3 py-2 pr-16 border @error('ordered_quantity_liters') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-slate-500 text-sm">L</span>
                                </div>
                            </div>
                            @error('ordered_quantity_liters')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="mt-1 text-xs text-slate-500">
                                Min: {{ number_format($po->minimum_quantity_liters, 0) }}L | Max: {{ number_format($po->maximum_quantity_liters, 0) }}L
                            </div>
                        </div>

                        <!-- Delivery Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-slate-900 mb-2">Expected Delivery Date <span class="text-red-500">*</span></label>
                                <input type="date"
                                       name="expected_delivery_date"
                                       id="expected_delivery_date"
                                       value="{{ old('expected_delivery_date', $po->expected_delivery_date) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required
                                       {{ $po->order_status !== 'PENDING' ? 'readonly' : '' }}
                                       class="block w-full px-3 py-2 border @error('expected_delivery_date') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                @error('expected_delivery_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="expected_delivery_time" class="block text-sm font-medium text-slate-900 mb-2">Expected Delivery Time</label>
                                <input type="time"
                                       name="expected_delivery_time"
                                       id="expected_delivery_time"
                                       value="{{ old('expected_delivery_time', $po->expected_delivery_time ? \Carbon\Carbon::parse($po->expected_delivery_time)->format('H:i') : '') }}"
                                       {{ $po->order_status !== 'PENDING' ? 'readonly' : '' }}
                                       class="block w-full px-3 py-2 border @error('expected_delivery_time') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                @error('expected_delivery_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Costs -->
                        <div class="border-t border-slate-200 pt-6">
                            <h4 class="text-sm font-medium text-slate-900 mb-4">Additional Costs (Optional)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="transport_cost_per_liter" class="block text-sm font-medium text-slate-700 mb-2">Transport Cost per Liter</label>
                                    <div class="relative">
                                        <input type="number"
                                               name="transport_cost_per_liter"
                                               id="transport_cost_per_liter"
                                               step="0.0001"
                                               min="0"
                                               max="99999.9999"
                                               value="{{ old('transport_cost_per_liter', $po->transport_cost_per_liter) }}"
                                               {{ $po->order_status !== 'PENDING' ? 'readonly' : '' }}
                                               oninput="calculatePricing()"
                                               class="block w-full px-3 py-2 pr-16 border @error('transport_cost_per_liter') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-slate-500 text-xs">UGX</span>
                                        </div>
                                    </div>
                                    @error('transport_cost_per_liter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="other_charges_per_liter" class="block text-sm font-medium text-slate-700 mb-2">Other Charges per Liter</label>
                                    <div class="relative">
                                        <input type="number"
                                               name="other_charges_per_liter"
                                               id="other_charges_per_liter"
                                               step="0.0001"
                                               min="0"
                                               max="99999.9999"
                                               value="{{ old('other_charges_per_liter', $po->other_charges_per_liter) }}"
                                               {{ $po->order_status !== 'PENDING' ? 'readonly' : '' }}
                                               oninput="calculatePricing()"
                                               class="block w-full px-3 py-2 pr-16 border @error('other_charges_per_liter') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $po->order_status !== 'PENDING' ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : '' }}">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-slate-500 text-xs">UGX</span>
                                        </div>
                                    </div>
                                    @error('other_charges_per_liter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </a>
                            @if($po->order_status === 'PENDING')
                            <button type="submit" id="submitBtn" class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Purchase Order
                            </button>
                            @else
                            <div class="inline-flex items-center px-6 py-2 bg-slate-300 text-slate-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Cannot Edit ({{ ucfirst(strtolower(str_replace('_', ' ', $po->order_status))) }})
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="space-y-6">
                <!-- Current Order Summary -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Updated Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Quantity:</span>
                            <span id="summaryQuantity" class="font-medium text-slate-900">{{ number_format($po->ordered_quantity_liters, 0) }}L</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Base Price:</span>
                            <span id="summaryBasePrice" class="text-slate-900">UGX {{ number_format($po->base_price_per_liter, 4) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Volume Discount:</span>
                            <span id="summaryDiscount" class="text-emerald-600">-UGX 0.0000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Agreed Price:</span>
                            <span id="summaryAgreedPrice" class="font-medium text-slate-900">UGX {{ number_format($po->agreed_price_per_liter, 4) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Transport Cost:</span>
                            <span id="summaryTransport" class="text-slate-900">UGX {{ number_format($po->transport_cost_per_liter, 4) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Other Charges:</span>
                            <span id="summaryOther" class="text-slate-900">UGX {{ number_format($po->other_charges_per_liter, 4) }}</span>
                        </div>
                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-slate-900">Total Order Value:</span>
                                <span id="summaryTotal" class="text-lg font-semibold text-blue-600">UGX {{ number_format($po->total_order_value, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changes Made -->
                <div id="changesAlert" class="bg-amber-50 border border-amber-200 rounded-xl p-4 hidden">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-amber-900">Changes Detected</h4>
                            <p class="text-xs text-amber-700 mt-1">Order summary will update when you save changes.</p>
                        </div>
                    </div>
                </div>

                <!-- Original Order Info -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <h4 class="text-sm font-medium text-slate-700 mb-3">Original Order</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Created:</span>
                            <span class="text-slate-700">{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Original Value:</span>
                            <span class="text-slate-700">UGX {{ number_format($po->total_order_value, 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status:</span>
                            <span class="text-slate-700">{{ ucfirst(strtolower(str_replace('_', ' ', $po->order_status))) }}</span>
                        </div>
                    </div>
                </div>

                @if($po->order_status === 'PENDING')
                <!-- Edit Guidelines -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-900">Edit Guidelines</h4>
                            <ul class="text-xs text-blue-700 mt-1 space-y-1">
                                <li>• Contract cannot be changed</li>
                                <li>• Quantity must be within contract limits</li>
                                <li>• Pricing will be recalculated automatically</li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
let originalValues = {
    station_id: '{{ $po->station_id }}',
    ordered_quantity_liters: '{{ $po->ordered_quantity_liters }}',
    expected_delivery_date: '{{ $po->expected_delivery_date }}',
    expected_delivery_time: '{{ $po->expected_delivery_time ? \Carbon\Carbon::parse($po->expected_delivery_time)->format('H:i') : '' }}',
    transport_cost_per_liter: '{{ $po->transport_cost_per_liter }}',
    other_charges_per_liter: '{{ $po->other_charges_per_liter }}'
};

let contractData = {
    base_price_per_liter: {{ $po->base_price_per_liter }},
    minimum_quantity_liters: {{ $po->minimum_quantity_liters }},
    maximum_quantity_liters: {{ $po->maximum_quantity_liters }}
};

// Form submission with validation
document.getElementById('editPOForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    @if($po->order_status !== 'PENDING')
    await Swal.fire({
        title: 'Cannot Edit',
        text: 'Only pending purchase orders can be edited.',
        icon: 'warning'
    });
    return;
    @endif

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Updating...';

    try {
        const formData = new FormData(this);
        const response = await fetch('{{ route("purchase-orders.update", $po->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            window.location.href = data.redirect;
        } else {
            // Handle validation errors
            if (data.errors) {
                let errorMessages = [];
                for (const [field, messages] of Object.entries(data.errors)) {
                    errorMessages.push(...messages);
                }
                throw new Error(errorMessages.join('\n'));
            } else {
                throw new Error(data.error || 'Failed to update purchase order');
            }
        }
    } catch (error) {
        await Swal.fire({
            title: 'Validation Error!',
            html: error.message.replace(/\n/g, '<br>'),
            icon: 'error',
            width: '500px'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Real-time pricing calculation
function calculatePricing() {
    const quantity = parseFloat(document.getElementById('ordered_quantity_liters').value) || 0;
    const transportCost = parseFloat(document.getElementById('transport_cost_per_liter').value) || 0;
    const otherCharges = parseFloat(document.getElementById('other_charges_per_liter').value) || 0;

    if (quantity <= 0) return;

    // Calculate volume discount (simplified - would need server-side calculation for accuracy)
    const basePrice = contractData.base_price_per_liter;
    let volumeDiscount = 0; // Simplified - real calculation would match controller logic

    const agreedPrice = basePrice - volumeDiscount;
    const totalOrderValue = quantity * (agreedPrice + transportCost + otherCharges);

    // Update summary
    document.getElementById('summaryQuantity').textContent = `${quantity.toLocaleString()}L`;
    document.getElementById('summaryBasePrice').textContent = `UGX ${basePrice.toFixed(4)}`;
    document.getElementById('summaryDiscount').textContent = `-UGX ${volumeDiscount.toFixed(4)}`;
    document.getElementById('summaryAgreedPrice').textContent = `UGX ${agreedPrice.toFixed(4)}`;
    document.getElementById('summaryTransport').textContent = `UGX ${transportCost.toFixed(4)}`;
    document.getElementById('summaryOther').textContent = `UGX ${otherCharges.toFixed(4)}`;
    document.getElementById('summaryTotal').textContent = `UGX ${totalOrderValue.toLocaleString()}`;

    // Check for changes
    checkForChanges();
}

// Check for changes and show alert
function checkForChanges() {
    const currentValues = {
        station_id: document.getElementById('station_id').value,
        ordered_quantity_liters: document.getElementById('ordered_quantity_liters').value,
        expected_delivery_date: document.getElementById('expected_delivery_date').value,
        expected_delivery_time: document.getElementById('expected_delivery_time').value,
        transport_cost_per_liter: document.getElementById('transport_cost_per_liter').value,
        other_charges_per_liter: document.getElementById('other_charges_per_liter').value
    };

    let hasChanges = false;
    for (const [key, value] of Object.entries(currentValues)) {
        if (value !== originalValues[key]) {
            hasChanges = true;
            break;
        }
    }

    const changesAlert = document.getElementById('changesAlert');
    if (hasChanges) {
        changesAlert.classList.remove('hidden');
    } else {
        changesAlert.classList.add('hidden');
    }
}

// Add event listeners for change detection
document.getElementById('station_id').addEventListener('change', checkForChanges);
document.getElementById('ordered_quantity_liters').addEventListener('input', function() {
    calculatePricing();
    checkForChanges();
});
document.getElementById('expected_delivery_date').addEventListener('change', checkForChanges);
document.getElementById('expected_delivery_time').addEventListener('change', checkForChanges);
document.getElementById('transport_cost_per_liter').addEventListener('input', function() {
    calculatePricing();
    checkForChanges();
});
document.getElementById('other_charges_per_liter').addEventListener('input', function() {
    calculatePricing();
    checkForChanges();
});

// Initialize calculation
calculatePricing();
</script>
@endsection
