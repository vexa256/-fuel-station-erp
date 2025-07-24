@extends('layouts.app')

@section('title', 'Create Purchase Order')

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
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create Purchase Order</h1>
                        <p class="text-sm text-slate-600">Contract-based fuel procurement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <span class="ml-2 font-medium">Contract Selection</span>
                    </div>
                    <div class="flex-1 mx-4 h-0.5 bg-slate-200"></div>
                    <div class="flex items-center text-sm text-blue-600">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-blue-600 rounded-full bg-blue-600 text-white font-medium">
                            2
                        </div>
                        <span class="ml-2 font-medium">Order Details</span>
                    </div>
                    <div class="flex-1 mx-4 h-0.5 bg-slate-200"></div>
                    <div class="flex items-center text-sm text-slate-400">
                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center border-2 border-slate-300 rounded-full bg-white">
                            3
                        </div>
                        <span class="ml-2">Review & Submit</span>
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
                        <p class="mt-1 text-sm text-slate-600">Complete the form to create a new purchase order</p>
                    </div>

                    <form id="createPOForm" class="p-6 space-y-6">
                        @csrf

                        <!-- Contract Selection -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-slate-900">Supplier Contract <span class="text-red-500">*</span></label>
                                @if(!$selectedContract)
                                <button type="button" onclick="openContractSelector()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    Browse Contracts
                                </button>
                                @endif
                            </div>

                            @if($selectedContract)
                            <!-- Selected Contract Display -->
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-emerald-900">{{ $selectedContract->company_name }}</p>
                                            <p class="text-xs text-emerald-700">Contract: {{ $selectedContract->contract_number }}</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearContract()" class="text-emerald-600 hover:text-emerald-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-4">
                                    <div class="bg-white rounded-md p-3">
                                        <p class="text-xs text-slate-600">Product Type</p>
                                        <p class="text-sm font-medium text-slate-900">{{ str_replace('_', ' ', $selectedContract->product_type) }}</p>
                                    </div>
                                    <div class="bg-white rounded-md p-3">
                                        <p class="text-xs text-slate-600">Base Price/Liter</p>
                                        <p class="text-sm font-medium text-slate-900">UGX {{ number_format($selectedContract->base_price_per_liter, 4) }}</p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="supplier_contract_id" value="{{ $selectedContract->id }}" required>
                            @else
                            <!-- Contract Selection Dropdown -->
                            <select name="supplier_contract_id" id="contractSelect" required class="block w-full px-3 py-2 border @error('supplier_contract_id') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select a supplier contract</option>
                                @foreach($activeContracts as $contract)
                                <option value="{{ $contract->id }}"
                                        {{ old('supplier_contract_id') == $contract->id ? 'selected' : '' }}
                                        data-supplier="{{ $contract->company_name }}"
                                        data-product="{{ $contract->product_type }}"
                                        data-price="{{ $contract->base_price_per_liter }}"
                                        data-min="{{ $contract->minimum_quantity_liters }}"
                                        data-max="{{ $contract->maximum_quantity_liters }}">
                                    {{ $contract->company_name }} - {{ $contract->contract_number }} ({{ str_replace('_', ' ', $contract->product_type) }})
                                </option>
                                @endforeach
                            </select>
                            @error('supplier_contract_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @endif
                        </div>

                        <!-- PO Number -->
                        <div>
                            <label for="po_number" class="block text-sm font-medium text-slate-900 mb-2">PO Number <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="po_number"
                                   id="po_number"
                                   value="{{ old('po_number', $nextPONumber) }}"
                                   required
                                   maxlength="100"
                                   class="block w-full px-3 py-2 border @error('po_number') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            @error('po_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">Auto-generated PO number (can be modified)</p>
                        </div>

                        <!-- Station Selection -->
                        <div>
                            <label for="station_id" class="block text-sm font-medium text-slate-900 mb-2">Delivery Station <span class="text-red-500">*</span></label>
                            <select name="station_id" id="station_id" required class="block w-full px-3 py-2 border @error('station_id') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <option value="">Select delivery station</option>
                                @foreach($stations as $station)
                                <option value="{{ $station->id }}" {{ old('station_id') == $station->id ? 'selected' : '' }}>{{ $station->station_name }} ({{ $station->station_code }})</option>
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
                                       min="1"
                                       max="999999999.999"
                                       value="{{ old('ordered_quantity_liters') }}"
                                       required
                                       oninput="calculatePricing()"
                                       class="block w-full px-3 py-2 pr-16 border @error('ordered_quantity_liters') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-slate-500 text-sm">L</span>
                                </div>
                            </div>
                            @error('ordered_quantity_liters')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div id="quantityConstraints" class="mt-1 text-xs text-slate-500 hidden">
                                Min: <span id="minQuantity">0</span>L | Max: <span id="maxQuantity">0</span>L
                            </div>
                        </div>

                        <!-- Delivery Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-slate-900 mb-2">Expected Delivery Date <span class="text-red-500">*</span></label>
                                <input type="date"
                                       name="expected_delivery_date"
                                       id="expected_delivery_date"
                                       value="{{ old('expected_delivery_date', $defaults['expected_delivery_date']) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required
                                       class="block w-full px-3 py-2 border @error('expected_delivery_date') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                @error('expected_delivery_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="expected_delivery_time" class="block text-sm font-medium text-slate-900 mb-2">Expected Delivery Time</label>
                                <input type="time"
                                       name="expected_delivery_time"
                                       id="expected_delivery_time"
                                       value="10:00:00"
                                       class="block w-full px-3 py-2 border @error('expected_delivery_time') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
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
                                               value="{{ old('transport_cost_per_liter', $defaults['transport_cost_per_liter']) }}"
                                               oninput="calculatePricing()"
                                               class="block w-full px-3 py-2 pr-16 border @error('transport_cost_per_liter') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
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
                                               value="{{ old('other_charges_per_liter', $defaults['other_charges_per_liter']) }}"
                                               oninput="calculatePricing()"
                                               class="block w-full px-3 py-2 pr-16 border @error('other_charges_per_liter') border-red-300 @else border-slate-300 @enderror rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
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
                            <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" id="submitBtn" class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="space-y-6">
                <!-- Contract Info -->
                <div id="contractInfo" class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6 hidden">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Contract Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Supplier:</span>
                            <span id="contractSupplier" class="font-medium text-slate-900"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Product:</span>
                            <span id="contractProduct" class="font-medium text-slate-900"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Base Price:</span>
                            <span id="contractPrice" class="font-medium text-slate-900"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Min Quantity:</span>
                                <span id="contractMinQty" class="text-slate-700"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Max Quantity:</span>
                                <span id="contractMaxQty" class="text-slate-700"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Summary -->
                <div id="pricingSummary" class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200/50 shadow-lg shadow-slate-900/5 p-6 hidden">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Quantity:</span>
                            <span id="summaryQuantity" class="font-medium text-slate-900">0 L</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Base Price:</span>
                            <span id="summaryBasePrice" class="text-slate-900">UGX 0.0000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Volume Discount:</span>
                            <span id="summaryDiscount" class="text-emerald-600">-UGX 0.0000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Agreed Price:</span>
                            <span id="summaryAgreedPrice" class="font-medium text-slate-900">UGX 0.0000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Transport Cost:</span>
                            <span id="summaryTransport" class="text-slate-900">UGX 0.0000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Other Charges:</span>
                            <span id="summaryOther" class="text-slate-900">UGX 0.0000</span>
                        </div>
                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-slate-900">Total Order Value:</span>
                                <span id="summaryTotal" class="text-lg font-semibold text-blue-600">UGX 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auto-Approval Notice -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-emerald-900">Auto-Approval</h4>
                            <p class="text-xs text-emerald-700 mt-1">This PO will be automatically approved upon creation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contract Selector Modal -->
<div id="contractModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-500 bg-opacity-75" onclick="closeContractSelector()"></div>
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="w-full">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">Select Supplier Contract</h3>
                    <div class="max-h-96 overflow-y-auto">
                        <div class="grid gap-3">
                            @foreach($activeContracts as $contract)
                            <div class="border border-slate-200 rounded-lg p-4 hover:border-blue-300 cursor-pointer transition-colors duration-200" onclick="selectContract({{ $contract->id }}, '{{ $contract->company_name }}', '{{ $contract->contract_number }}', '{{ $contract->product_type }}', {{ $contract->base_price_per_liter }}, {{ $contract->minimum_quantity_liters }}, {{ $contract->maximum_quantity_liters }})">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-slate-900">{{ $contract->company_name }}</h4>
                                        <p class="text-sm text-slate-600">{{ $contract->contract_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-slate-900">{{ str_replace('_', ' ', $contract->product_type) }}</p>
                                        <p class="text-xs text-slate-500">UGX {{ number_format($contract->base_price_per_liter, 4) }}/L</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-between text-xs text-slate-500">
                                    <span>Min: {{ number_format($contract->minimum_quantity_liters, 0) }}L</span>
                                    <span>Max: {{ number_format($contract->maximum_quantity_liters, 0) }}L</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="closeContractSelector()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentContract = @json($selectedContract);

// Form submission with validation
document.getElementById('createPOForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...';

    try {
        const formData = new FormData(this);
        const response = await fetch('{{ route("purchase-orders.store") }}', {
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
                throw new Error(data.error || 'Failed to create purchase order');
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

// Contract selection logic
function openContractSelector() {
    document.getElementById('contractModal').classList.remove('hidden');
}

function closeContractSelector() {
    document.getElementById('contractModal').classList.add('hidden');
}

function selectContract(id, supplier, contractNum, product, price, minQty, maxQty) {
    currentContract = {
        id: id,
        company_name: supplier,
        contract_number: contractNum,
        product_type: product,
        base_price_per_liter: price,
        minimum_quantity_liters: minQty,
        maximum_quantity_liters: maxQty
    };

    // Update hidden input
    document.querySelector('input[name="supplier_contract_id"]').value = id;

    // Update contract info display
    updateContractDisplay();
    closeContractSelector();
}

function clearContract() {
    currentContract = null;
    document.querySelector('input[name="supplier_contract_id"]').value = '';
    document.getElementById('contractInfo').classList.add('hidden');
    document.getElementById('pricingSummary').classList.add('hidden');
}

function updateContractDisplay() {
    if (!currentContract) return;

    document.getElementById('contractSupplier').textContent = currentContract.company_name;
    document.getElementById('contractProduct').textContent = currentContract.product_type.replace('_', ' ');
    document.getElementById('contractPrice').textContent = `UGX ${parseFloat(currentContract.base_price_per_liter).toFixed(4)}`;
    document.getElementById('contractMinQty').textContent = `${parseInt(currentContract.minimum_quantity_liters).toLocaleString()}L`;
    document.getElementById('contractMaxQty').textContent = `${parseInt(currentContract.maximum_quantity_liters).toLocaleString()}L`;

    // Update quantity constraints
    document.getElementById('minQuantity').textContent = parseInt(currentContract.minimum_quantity_liters).toLocaleString();
    document.getElementById('maxQuantity').textContent = parseInt(currentContract.maximum_quantity_liters).toLocaleString();
    document.getElementById('quantityConstraints').classList.remove('hidden');

    // Update quantity input constraints
    const qtyInput = document.getElementById('ordered_quantity_liters');
    qtyInput.min = currentContract.minimum_quantity_liters;
    qtyInput.max = currentContract.maximum_quantity_liters;

    document.getElementById('contractInfo').classList.remove('hidden');
    calculatePricing();
}

// Real-time pricing calculation
function calculatePricing() {
    if (!currentContract) return;

    const quantity = parseFloat(document.getElementById('ordered_quantity_liters').value) || 0;
    const transportCost = parseFloat(document.getElementById('transport_cost_per_liter').value) || 0;
    const otherCharges = parseFloat(document.getElementById('other_charges_per_liter').value) || 0;

    if (quantity <= 0) {
        document.getElementById('pricingSummary').classList.add('hidden');
        return;
    }

    // Calculate volume discount (simplified)
    const basePrice = parseFloat(currentContract.base_price_per_liter);
    let volumeDiscount = 0;

    // Volume discount logic would match controller's calculateVolumeDiscount method
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

    document.getElementById('pricingSummary').classList.remove('hidden');
}

// Initialize contract display if pre-selected
@if($selectedContract)
currentContract = @json($selectedContract);
updateContractDisplay();
@endif

// Contract select dropdown handler
document.getElementById('contractSelect')?.addEventListener('change', function(e) {
    const option = e.target.selectedOptions[0];
    if (option && option.value) {
        selectContract(
            option.value,
            option.dataset.supplier,
            option.textContent.split(' - ')[1].split(' (')[0],
            option.dataset.product,
            parseFloat(option.dataset.price),
            parseFloat(option.dataset.min),
            parseFloat(option.dataset.max)
        );
    }
});
</script>
@endsection
