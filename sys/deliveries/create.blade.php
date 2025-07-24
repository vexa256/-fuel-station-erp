@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50" x-data="deliveryCreateForm()">

    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('deliveries.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-plus text-white text-sm"></i>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-900">Create Delivery</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center">
                <template x-for="step in maxSteps" :key="step">
                    <div class="flex items-center" :class="step < maxSteps ? 'flex-1' : ''">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium"
                             :class="step <= currentStep ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'">
                            <span x-text="step"></span>
                        </div>
                        <div x-show="step < maxSteps" class="flex-1 h-0.5 mx-4"
                             :class="step < currentStep ? 'bg-blue-600' : 'bg-slate-200'"></div>
                    </div>
                </template>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-600">
                <span :class="currentStep >= 1 ? 'font-medium text-blue-600' : ''">Purchase Order</span>
                <span :class="currentStep >= 2 ? 'font-medium text-blue-600' : ''">Delivery Details</span>
                <span :class="currentStep >= 3 ? 'font-medium text-blue-600' : ''">Vehicle & Quality</span>
                <span :class="currentStep >= 4 ? 'font-medium text-blue-600' : ''">Review & Submit</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">

            <!-- Step 1: Purchase Order Selection -->
            <div x-show="currentStep === 1" x-transition class="p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Select Purchase Order</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Purchase Order Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Purchase Order *</label>
                        <select x-model="formData.purchase_order_id" @change="selectPO($event.target.value)"
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="errors.purchase_order_id ? 'border-red-300' : 'border-slate-300'">
                            <option value="">Select a purchase order...</option>
                            @foreach($approvedPOs as $po)
                            <option value="{{ $po->id }}">
                                {{ $po->po_number }} - {{ $po->company_name }}
                                ({{ number_format($po->remaining_quantity, 3) }}L remaining)
                            </option>
                            @endforeach
                        </select>
                        <p x-show="errors.purchase_order_id" class="text-red-600 text-xs mt-1" x-text="errors.purchase_order_id"></p>
                    </div>

                    <!-- Delivery Note Number -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Delivery Note Number *</label>
                        <input type="text" x-model="formData.delivery_note_number" maxlength="100"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.delivery_note_number ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.delivery_note_number" class="text-red-600 text-xs mt-1" x-text="errors.delivery_note_number"></p>
                    </div>
                </div>

                <!-- Selected PO Details with Smart Suggestions -->
                <div x-show="selectedPO" x-transition class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-medium text-blue-900 mb-2">Selected Purchase Order Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="text-blue-700 font-medium">Supplier:</span>
                            <span x-text="selectedPO?.company_name || 'N/A'" class="text-blue-900"></span>
                        </div>
                        <div>
                            <span class="text-blue-700 font-medium">Station:</span>
                            <span x-text="selectedPO?.station_name || 'N/A'" class="text-blue-900"></span>
                        </div>
                        <div>
                            <span class="text-blue-700 font-medium">Product:</span>
                            <span x-text="selectedPO?.product_type || 'N/A'" class="text-blue-900"></span>
                        </div>
                        <div>
                            <span class="text-blue-700 font-medium">Remaining:</span>
                            <span x-text="selectedPO ? Number(selectedPO.remaining_quantity).toLocaleString() + 'L' : 'N/A'" class="text-blue-900"></span>
                        </div>
                    </div>

                    <!-- Automatic Tank Selection Notice -->
                    <div class="bg-white border border-blue-200 rounded-md p-3">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                            <div class="text-xs text-blue-800">
                                <p class="font-medium mb-1">Automatic Processing:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <span>✓ Tank automatically selected by system</span>
                                    <span>✓ Capacity validation performed</span>
                                    <span>✓ Expected delivery date applied</span>
                                    <span>✓ Smart defaults for vehicle & quality</span>
                                </div>
                                <p class="mt-2 text-blue-600">System will find the appropriate tank for <strong x-text="selectedPO?.product_type"></strong> at <strong x-text="selectedPO?.station_name"></strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Invoice Reference -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Supplier Invoice Reference
                        <span x-show="selectedPO && formData.supplier_invoice_reference" class="text-xs text-blue-600">
                            (Auto-generated format)
                        </span>
                    </label>
                    <input type="text" x-model="formData.supplier_invoice_reference" maxlength="100"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p x-show="selectedPO" class="text-xs text-slate-500 mt-1">
                        Format: Supplier-PO-Date (e.g., <span x-text="selectedPO ? generateIntelligentInvoiceRef(selectedPO) : 'SUP-1234-20250724'"></span>)
                    </p>
                </div>
            </div>

            <!-- Step 2: Delivery Details -->
            <div x-show="currentStep === 2" x-transition class="p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Delivery Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Delivery Date -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Delivery Date *
                            <span x-show="selectedPO && selectedPO.expected_delivery_date" class="text-xs text-blue-600">
                                (Auto-filled from PO)
                            </span>
                        </label>
                        <input type="date" x-model="formData.delivery_date"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.delivery_date ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.delivery_date" class="text-red-600 text-xs mt-1" x-text="errors.delivery_date"></p>
                    </div>

                    <!-- Delivery Time -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Delivery Time *
                            <span x-show="selectedPO && selectedPO.expected_delivery_time" class="text-xs text-blue-600">
                                (Auto-filled from PO)
                            </span>
                        </label>
                        <input type="time" x-model="formData.delivery_time"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.delivery_time ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.delivery_time" class="text-red-600 text-xs mt-1" x-text="errors.delivery_time"></p>
                    </div>

                    <!-- Quantity Delivered -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Quantity Delivered (Liters) *
                            <span x-show="selectedPO" class="text-xs text-blue-600">
                                (Auto-filled: remaining quantity)
                            </span>
                        </label>
                        <input type="number" x-model="formData.quantity_delivered_liters"
                               min="0.001" max="999999999.999" step="0.001"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.quantity_delivered_liters ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.quantity_delivered_liters" class="text-red-600 text-xs mt-1" x-text="errors.quantity_delivered_liters"></p>

                        <!-- Capacity Warning -->
                        <div x-show="checkCapacityWarning()" class="mt-2">
                            <div :class="checkCapacityWarning()?.type === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                 class="border rounded-md p-2 text-xs">
                                <i :class="checkCapacityWarning()?.type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-exclamation-triangle'" class="mr-1"></i>
                                <span x-text="checkCapacityWarning()?.message"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Estimated Total Cost (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Estimated Total Cost (UGX)</label>
                        <input type="text" :value="'UGX ' + calculateTotalCost()" readonly
                               class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm bg-slate-50 text-slate-700">
                    </div>
                </div>

                <!-- Cost Breakdown with Smart Defaults -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Transport Cost per Liter (UGX)
                            <span x-show="selectedPO && selectedPO.transport_cost_per_liter" class="text-xs text-blue-600">
                                (From PO: <span x-text="selectedPO ? parseFloat(selectedPO.transport_cost_per_liter || 0).toFixed(4) : '0.0000'"></span>)
                            </span>
                        </label>
                        <input type="number" x-model="formData.transport_cost_per_liter"
                               min="0" max="9999.9999" step="0.0001"
                               class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Handling Cost per Liter (UGX)
                            <span x-show="selectedPO && selectedPO.other_charges_per_liter" class="text-xs text-blue-600">
                                (From PO: <span x-text="selectedPO ? parseFloat(selectedPO.other_charges_per_liter || 0).toFixed(4) : '0.0000'"></span>)
                            </span>
                        </label>
                        <input type="number" x-model="formData.handling_cost_per_liter"
                               min="0" max="9999.9999" step="0.0001"
                               class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Step 3: Vehicle & Quality Details -->
            <div x-show="currentStep === 3" x-transition class="p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Vehicle & Quality Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Driver Information -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Driver Name</label>
                        <input type="text" x-model="formData.driver_name" maxlength="255"
                               class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Driver License</label>
                        <input type="text" x-model="formData.driver_license" maxlength="100"
                               class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Vehicle Information -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Vehicle Registration</label>
                        <input type="text" x-model="formData.vehicle_registration" maxlength="50"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                               :class="errors.vehicle_registration ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.vehicle_registration" class="text-red-600 text-xs mt-1" x-text="errors.vehicle_registration"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Vehicle Type
                            <span x-show="selectedPO" class="text-xs text-blue-600">
                                (Smart default for <span x-text="selectedPO?.product_type"></span>)
                            </span>
                        </label>
                        <select x-model="formData.vehicle_type"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <template x-for="type in vehicleTypes" :key="type">
                                <option :value="type" x-text="type"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Compartment Count
                            <span x-show="selectedPO" class="text-xs text-blue-600">
                                (Optimized for quantity)
                            </span>
                        </label>
                        <input type="number" x-model="formData.compartment_count"
                               min="1" max="255"
                               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.compartment_count ? 'border-red-300' : 'border-slate-300'">
                        <p x-show="errors.compartment_count" class="text-red-600 text-xs mt-1" x-text="errors.compartment_count"></p>
                    </div>
                </div>

                <!-- Seal Numbers -->
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-slate-700 mb-3">Seal Numbers</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Seal 1</label>
                            <input type="text" x-model="formData.seal_number_1" maxlength="50"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Seal 2</label>
                            <input type="text" x-model="formData.seal_number_2" maxlength="50"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Seal 3</label>
                            <input type="text" x-model="formData.seal_number_3" maxlength="50"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Quality Parameters with Product-Specific Defaults -->
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-slate-700 mb-3">
                        Quality Parameters
                        <span x-show="selectedPO" class="text-xs text-blue-600">
                            (Product-specific defaults for <span x-text="selectedPO?.product_type"></span>)
                        </span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Loading Temperature (°C)</label>
                            <input type="number" x-model="formData.loading_temperature_celsius"
                                   min="-999.9" max="999.9" step="0.1"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   :class="errors.loading_temperature_celsius ? 'border-red-300' : 'border-slate-300'">
                            <p x-show="errors.loading_temperature_celsius" class="text-red-600 text-xs mt-1" x-text="errors.loading_temperature_celsius"></p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Delivery Temperature (°C)</label>
                            <input type="number" x-model="formData.delivery_temperature_celsius"
                                   min="-999.9" max="999.9" step="0.1"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   :class="errors.delivery_temperature_celsius ? 'border-red-300' : 'border-slate-300'">
                            <p x-show="errors.delivery_temperature_celsius" class="text-red-600 text-xs mt-1" x-text="errors.delivery_temperature_celsius"></p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Density at 15°C</label>
                            <input type="number" x-model="formData.density_at_15c"
                                   min="0.0001" max="99.9999" step="0.0001"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   :class="errors.density_at_15c ? 'border-red-300' : 'border-slate-300'">
                            <p x-show="errors.density_at_15c" class="text-red-600 text-xs mt-1" x-text="errors.density_at_15c"></p>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Water Content (ppm)</label>
                            <input type="number" x-model="formData.water_content_ppm"
                                   min="0.0" max="9999.9" step="0.1"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   :class="errors.water_content_ppm ? 'border-red-300' : 'border-slate-300'">
                            <p x-show="errors.water_content_ppm" class="text-red-600 text-xs mt-1" x-text="errors.water_content_ppm"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Review & Submit -->
            <div x-show="currentStep === 4" x-transition class="p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-6">Review & Submit</h2>

                <div class="space-y-6">
                    <!-- Purchase Order Summary -->
                    <div class="bg-slate-50 rounded-lg p-4">
                        <h3 class="font-medium text-slate-900 mb-3">Purchase Order Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-slate-600">PO Number:</span>
                                <div class="font-medium" x-text="selectedPO?.po_number || 'N/A'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Supplier:</span>
                                <div class="font-medium" x-text="selectedPO?.company_name || 'N/A'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Product:</span>
                                <div class="font-medium" x-text="selectedPO?.product_type || 'N/A'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Station:</span>
                                <div class="font-medium" x-text="selectedPO?.station_name || 'N/A'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Summary -->
                    <div class="bg-slate-50 rounded-lg p-4">
                        <h3 class="font-medium text-slate-900 mb-3">Delivery Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-slate-600">Delivery Note:</span>
                                <div class="font-medium" x-text="formData.delivery_note_number"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Date & Time:</span>
                                <div class="font-medium" x-text="formData.delivery_date + ' ' + formData.delivery_time"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Quantity:</span>
                                <div class="font-medium" x-text="Number(formData.quantity_delivered_liters || 0).toLocaleString() + 'L'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Estimated Cost:</span>
                                <div class="font-medium" x-text="'UGX ' + calculateTotalCost()"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tank Summary -->
                    <div x-show="selectedPO" class="bg-slate-50 rounded-lg p-4">
                        <h3 class="font-medium text-slate-900 mb-3">Tank Assignment</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-slate-600">Product Type:</span>
                                <div class="font-medium" x-text="selectedPO?.product_type || 'N/A'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Station:</span>
                                <div class="font-medium" x-text="selectedPO?.station_name || 'N/A'"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Tank Selection:</span>
                                <div class="font-medium text-blue-600">Automatic</div>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            System will automatically select the appropriate active tank for this product type at the destination station.
                        </div>
                    </div>

                    <!-- Vehicle Summary -->
                    <div x-show="formData.driver_name || formData.vehicle_registration" class="bg-slate-50 rounded-lg p-4">
                        <h3 class="font-medium text-slate-900 mb-3">Vehicle Information</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div x-show="formData.driver_name">
                                <span class="text-slate-600">Driver:</span>
                                <div class="font-medium" x-text="formData.driver_name"></div>
                            </div>
                            <div x-show="formData.vehicle_registration">
                                <span class="text-slate-600">Vehicle:</span>
                                <div class="font-medium" x-text="formData.vehicle_registration"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Type:</span>
                                <div class="font-medium" x-text="formData.vehicle_type"></div>
                            </div>
                            <div>
                                <span class="text-slate-600">Compartments:</span>
                                <div class="font-medium" x-text="formData.compartment_count"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 mr-3"></i>
                            <div class="text-sm text-amber-800">
                                <p class="font-medium mb-1">Important:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>This delivery will be validated against tank capacity limits</li>
                                    <li>FIFO automation will be triggered upon completion</li>
                                    <li>Quality parameters will be used for variance detection</li>
                                    <li>All data will be cryptographically logged for audit purposes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <button @click="prevStep()"
                            x-show="currentStep > 1"
                            class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Previous
                    </button>

                    <div class="flex items-center space-x-3">
                        <button @click="nextStep()"
                                x-show="currentStep < maxSteps"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                            Next
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>

                        <button @click="submitForm()"
                                x-show="currentStep === maxSteps"
                                :disabled="isSubmitting"
                                :class="isSubmitting ? 'bg-slate-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                                class="inline-flex items-center px-6 py-2 text-white text-sm font-medium rounded-md transition-colors">
                            <span x-show="!isSubmitting">
                                <i class="fas fa-check mr-2"></i>
                                Create Delivery
                            </span>
                            <span x-show="isSubmitting">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deliveryCreateForm() {
    return {
        currentStep: 1,
        maxSteps: 4,

        // EXACT data from controller - NO phantom variables
        selectedPO: @json($selectedPO),
        approvedPOs: @json($approvedPOs),
        nextDeliveryNote: '{{ $nextDeliveryNote }}',

        // Form data - EXACT validation mapping from controller
        formData: {
            purchase_order_id: '{{ request('po_id', '') }}',
            delivery_note_number: '{{ $nextDeliveryNote }}',
            supplier_invoice_reference: '',
            delivery_date: '{{ now()->toDateString() }}',
            delivery_time: '{{ now()->format('H:i') }}',
            driver_name: '',
            driver_license: '',
            vehicle_registration: '',
            vehicle_type: 'TANKER',
            compartment_count: 1,
            seal_number_1: '',
            seal_number_2: '',
            seal_number_3: '',
            quantity_delivered_liters: '',
            loading_temperature_celsius: '',
            delivery_temperature_celsius: '',
            density_at_15c: '',
            water_content_ppm: '',
            transport_cost_per_liter: '',
            handling_cost_per_liter: ''
        },

        errors: {},
        isSubmitting: false,

        // EXACT enum values from controller constants
        vehicleTypes: ['TANKER', 'BOWSER', 'TRUCK', 'OTHER'],

        selectPO(poId) {
            this.formData.purchase_order_id = poId;
            const po = this.approvedPOs.find(p => p.id == poId);
            if (po) {
                this.selectedPO = po;

                // SMART AUTO-POPULATION based on PO data

                // 1. Delivery Date & Time from PO expectations
                if (po.expected_delivery_date) {
                    this.formData.delivery_date = po.expected_delivery_date;
                }
                if (po.expected_delivery_time) {
                    this.formData.delivery_time = po.expected_delivery_time.substring(0, 5); // HH:MM format
                }

                // 2. Quantity - prioritize remaining quantity
                this.formData.quantity_delivered_liters = po.remaining_quantity || po.ordered_quantity_liters;

                // 3. Cost structure from PO
                this.formData.transport_cost_per_liter = po.transport_cost_per_liter || '';
                this.formData.handling_cost_per_liter = po.other_charges_per_liter || '';

                // 4. Smart vehicle type based on product type
                this.formData.vehicle_type = this.getSmartVehicleType(po.product_type);

                // 5. Smart compartment count based on quantity
                this.formData.compartment_count = this.getSmartCompartmentCount(po.remaining_quantity || po.ordered_quantity_liters);

                // 6. Product-specific quality defaults
                const qualityDefaults = this.getProductQualityDefaults(po.product_type);
                this.formData.density_at_15c = qualityDefaults.density;
                this.formData.loading_temperature_celsius = qualityDefaults.temperature;
                this.formData.delivery_temperature_celsius = qualityDefaults.temperature;
                this.formData.water_content_ppm = qualityDefaults.water_content;

                // 7. Auto-generate intelligent invoice reference
                if (!this.formData.supplier_invoice_reference) {
                    this.formData.supplier_invoice_reference = this.generateIntelligentInvoiceRef(po);
                }
            }
        },

        validateStep(step) {
            this.errors = {};
            let isValid = true;

            if (step === 1) {
                // Purchase Order Selection
                if (!this.formData.purchase_order_id) {
                    this.errors.purchase_order_id = 'Purchase order is required';
                    isValid = false;
                }
                if (!this.formData.delivery_note_number || this.formData.delivery_note_number.length > 100) {
                    this.errors.delivery_note_number = 'Delivery note number is required (max 100 chars)';
                    isValid = false;
                }
            }

            if (step === 2) {
                // Delivery Details
                if (!this.formData.delivery_date) {
                    this.errors.delivery_date = 'Delivery date is required';
                    isValid = false;
                }
                if (!this.formData.delivery_time) {
                    this.errors.delivery_time = 'Delivery time is required';
                    isValid = false;
                }
                if (!this.formData.quantity_delivered_liters || parseFloat(this.formData.quantity_delivered_liters) < 0.001) {
                    this.errors.quantity_delivered_liters = 'Quantity must be at least 0.001 liters';
                    isValid = false;
                }
                if (parseFloat(this.formData.quantity_delivered_liters) > 999999999.999) {
                    this.errors.quantity_delivered_liters = 'Quantity cannot exceed 999,999,999.999 liters';
                    isValid = false;
                }
            }

            if (step === 3) {
                // Vehicle & Quality Details - All optional but validate ranges if provided
                if (this.formData.vehicle_registration && this.formData.vehicle_registration.length > 50) {
                    this.errors.vehicle_registration = 'Vehicle registration max 50 characters';
                    isValid = false;
                }
                if (this.formData.compartment_count && (this.formData.compartment_count < 1 || this.formData.compartment_count > 255)) {
                    this.errors.compartment_count = 'Compartment count must be between 1-255';
                    isValid = false;
                }
                if (this.formData.loading_temperature_celsius && (parseFloat(this.formData.loading_temperature_celsius) < -999.9 || parseFloat(this.formData.loading_temperature_celsius) > 999.9)) {
                    this.errors.loading_temperature_celsius = 'Temperature must be between -999.9 and 999.9°C';
                    isValid = false;
                }
                if (this.formData.delivery_temperature_celsius && (parseFloat(this.formData.delivery_temperature_celsius) < -999.9 || parseFloat(this.formData.delivery_temperature_celsius) > 999.9)) {
                    this.errors.delivery_temperature_celsius = 'Temperature must be between -999.9 and 999.9°C';
                    isValid = false;
                }
                if (this.formData.density_at_15c && (parseFloat(this.formData.density_at_15c) < 0.0001 || parseFloat(this.formData.density_at_15c) > 99.9999)) {
                    this.errors.density_at_15c = 'Density must be between 0.0001 and 99.9999';
                    isValid = false;
                }
                if (this.formData.water_content_ppm && (parseFloat(this.formData.water_content_ppm) < 0.0 || parseFloat(this.formData.water_content_ppm) > 9999.9)) {
                    this.errors.water_content_ppm = 'Water content must be between 0.0 and 9999.9 ppm';
                    isValid = false;
                }
            }

            return isValid;
        },

        nextStep() {
            if (this.validateStep(this.currentStep) && this.currentStep < this.maxSteps) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        async submitForm() {
            if (!this.validateStep(4) || this.isSubmitting) return;

            this.isSubmitting = true;

            try {
                const response = await fetch('{{ route('deliveries.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#16a34a'
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.error,
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });

                    if (data.details) {
                        console.log('Capacity details:', data.details);
                    }
                }
            } catch (error) {
                Swal.fire({
                    title: 'Network Error!',
                    text: 'Please check your connection and try again',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            } finally {
                this.isSubmitting = false;
            }
        },

        calculateTotalCost() {
            const quantity = parseFloat(this.formData.quantity_delivered_liters) || 0;
            const basePrice = this.selectedPO ? parseFloat(this.selectedPO.agreed_price_per_liter) || 0 : 0;
            const transportCost = parseFloat(this.formData.transport_cost_per_liter) || 0;
            const handlingCost = parseFloat(this.formData.handling_cost_per_liter) || 0;

            const costPerLiter = basePrice + transportCost + handlingCost;
            return (quantity * costPerLiter).toFixed(2);
        },

        // INTELLIGENT AUTO-POPULATION HELPERS

        getSmartVehicleType(productType) {
            // LPG requires specialized bowser vehicles
            if (productType === 'LPG_AUTOGAS') {
                return 'BOWSER';
            }

            // Heavy fuel oils might use trucks
            if (['HEAVY_FUEL_OIL', 'LIGHT_FUEL_OIL'].includes(productType)) {
                return 'TRUCK';
            }

            // Default to tanker for most fuel types
            return 'TANKER';
        },

        getSmartCompartmentCount(quantity) {
            // Large deliveries typically use multi-compartment vehicles
            if (quantity > 30000) return 4;      // >30k liters = 4 compartments
            if (quantity > 20000) return 3;      // >20k liters = 3 compartments
            if (quantity > 10000) return 2;      // >10k liters = 2 compartments
            return 1;                            // ≤10k liters = 1 compartment
        },

        getProductQualityDefaults(productType) {
            const defaults = {
                'PETROL_95': { density: '0.7400', temperature: '15.0', water_content: '50.0' },
                'PETROL_98': { density: '0.7350', temperature: '15.0', water_content: '50.0' },
                'DIESEL': { density: '0.8400', temperature: '15.0', water_content: '200.0' },
                'KEROSENE': { density: '0.8000', temperature: '15.0', water_content: '100.0' },
                'JET_A1': { density: '0.8050', temperature: '15.0', water_content: '30.0' },
                'HEAVY_FUEL_OIL': { density: '0.9500', temperature: '50.0', water_content: '500.0' },
                'LIGHT_FUEL_OIL': { density: '0.8600', temperature: '20.0', water_content: '300.0' },
                'LPG_AUTOGAS': { density: '0.5400', temperature: '-5.0', water_content: '10.0' },
                'ETHANOL_E10': { density: '0.7500', temperature: '20.0', water_content: '150.0' },
                'ETHANOL_E85': { density: '0.7850', temperature: '20.0', water_content: '300.0' },
                'BIODIESEL_B7': { density: '0.8450', temperature: '15.0', water_content: '200.0' },
                'BIODIESEL_B20': { density: '0.8500', temperature: '15.0', water_content: '250.0' }
            };

            return defaults[productType] || { density: '0.8500', temperature: '15.0', water_content: '100.0' };
        },

        generateIntelligentInvoiceRef(po) {
            // Generate format: SUPPLIER-PONUM-YYYYMMDD
            const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            const supplierCode = po.company_name.substring(0, 3).toUpperCase();
            const poShort = po.po_number.replace(/[^0-9]/g, '').slice(-4); // Last 4 digits
            return `${supplierCode}-${poShort}-${date}`;
        },

        // CAPACITY WARNING SYSTEM

        checkCapacityWarning() {
            if (!this.selectedPO || !this.formData.quantity_delivered_liters) return null;

            const quantity = parseFloat(this.formData.quantity_delivered_liters);
            const remaining = parseFloat(this.selectedPO.remaining_quantity || this.selectedPO.ordered_quantity_liters);

            if (quantity > remaining * 1.05) { // 5% tolerance
                return {
                    type: 'warning',
                    message: `Delivery quantity (${quantity.toLocaleString()}L) exceeds remaining PO quantity (${remaining.toLocaleString()}L)`
                };
            }

            if (quantity > remaining * 1.10) { // 10% over-delivery
                return {
                    type: 'error',
                    message: `Delivery quantity significantly exceeds PO limits. Please verify.`
                };
            }

            return null;
        },

        // DELIVERY TIME OPTIMIZATION

        suggestOptimalDeliveryTime(productType, quantity) {
            // Suggest optimal delivery times based on product type and quantity
            const timeSlots = {
                'LPG_AUTOGAS': ['08:00', '14:00'], // Cooler times for safety
                'HEAVY_FUEL_OIL': ['10:00', '15:00'], // Warmer times for viscosity
                'default': ['07:00', '13:00', '16:00'] // Standard delivery windows
            };

            const slots = timeSlots[productType] || timeSlots.default;

            // Large deliveries prefer earlier slots (less traffic)
            if (quantity > 20000) {
                return slots[0];
            }

            return slots[Math.floor(Math.random() * slots.length)];
        }
    }
}
</script>
@endsection
