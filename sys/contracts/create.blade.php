@extends('layouts.app')

@section('title', 'Create New Contract')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Create New Contract</h1>
                    <p class="text-sm text-slate-600 mt-1">Set up a new supplier contract for fuel procurement</p>
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

        <!-- Contract Form Wizard -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200">
            <!-- Wizard Steps -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6">
                    <button class="tab-btn active py-4 px-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600" data-tab="basic">
                        Basic Details
                    </button>
                    <button class="tab-btn py-4 px-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700" data-tab="pricing">
                        Pricing & Terms
                    </button>
                    <button class="tab-btn py-4 px-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700" data-tab="review">
                        Review & Submit
                    </button>
                </nav>
            </div>

            <form id="contractForm" class="p-6">
                @csrf

                <!-- Step 1: Basic Details -->
                <div id="tab-basic" class="tab-content active">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Contract Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Contract Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="contract_number"
                                   id="contract_number"
                                   value="{{ $nextContractNumber }}"
                                   maxlength="100"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="text-xs text-slate-500 mt-1">Auto-generated contract number</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <select name="supplier_id"
                                    id="supplier_id"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Select a supplier...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->company_name }} ({{ $supplier->supplier_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Product Type <span class="text-red-500">*</span>
                            </label>
                            <select name="product_type"
                                    id="product_type"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Select product type...</option>
                                <option value="PETROL_95">Petrol 95</option>
                                <option value="PETROL_98">Petrol 98</option>
                                <option value="DIESEL">Diesel</option>
                                <option value="KEROSENE">Kerosene</option>
                                <option value="JET_A1">Jet A1</option>
                                <option value="HEAVY_FUEL_OIL">Heavy Fuel Oil</option>
                                <option value="LIGHT_FUEL_OIL">Light Fuel Oil</option>
                                <option value="LPG_AUTOGAS">LPG Autogas</option>
                                <option value="ETHANOL_E10">Ethanol E10</option>
                                <option value="ETHANOL_E85">Ethanol E85</option>
                                <option value="BIODIESEL_B7">Biodiesel B7</option>
                                <option value="BIODIESEL_B20">Biodiesel B20</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Contract Status
                            </label>
                            <select name="is_active"
                                    id="is_active"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="button"
                                onclick="nextTab('pricing')"
                                class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Continue to Pricing
                        </button>
                    </div>
                </div>

                <!-- Step 2: Pricing & Terms -->
                <div id="tab-pricing" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Pricing & Quantity Terms</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Base Price per Liter (UGX) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="base_price_per_liter"
                                   id="base_price_per_liter"
                                   step="0.0001"
                                   min="0"
                                   max="999999.9999"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Minimum Quantity (Liters) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="minimum_quantity_liters"
                                   id="minimum_quantity_liters"
                                   step="0.001"
                                   min="0"
                                   max="999999999.999"
                                   value="{{ $defaults['minimum_quantity_liters'] }}"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Maximum Quantity (Liters) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="maximum_quantity_liters"
                                   id="maximum_quantity_liters"
                                   step="0.001"
                                   min="0"
                                   max="999999999.999"
                                   value="{{ $defaults['maximum_quantity_liters'] }}"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Effective From <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="effective_from"
                                   id="effective_from"
                                   value="{{ $defaults['effective_from'] }}"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Effective Until <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="effective_until"
                                   id="effective_until"
                                   value="{{ $defaults['effective_until'] }}"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button"
                                onclick="prevTab('basic')"
                                class="px-6 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            Back to Basic Details
                        </button>
                        <button type="button"
                                onclick="nextTab('review')"
                                class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Review Contract
                        </button>
                    </div>
                </div>

                <!-- Step 3: Review & Submit -->
                <div id="tab-review" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Review Contract Details</h3>

                    <div id="reviewSummary" class="bg-slate-50 rounded-lg p-6 mb-6">
                        <!-- Summary will be populated by JavaScript -->
                    </div>

                    <div class="flex justify-between">
                        <button type="button"
                                onclick="prevTab('pricing')"
                                class="px-6 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            Back to Pricing
                        </button>
                        <button type="submit"
                                id="submitBtn"
                                class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                            Create Contract
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    window.nextTab = function(tabName) {
        if (validateCurrentTab()) {
            showTab(tabName);
        }
    };

    window.prevTab = function(tabName) {
        showTab(tabName);
    };

    function showTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
            content.classList.remove('active');
        });

        // Remove active from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-slate-500');
        });

        // Show selected tab content
        document.getElementById('tab-' + tabName).classList.remove('hidden');
        document.getElementById('tab-' + tabName).classList.add('active');

        // Activate tab button
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active', 'border-blue-600', 'text-blue-600');
        document.querySelector(`[data-tab="${tabName}"]`).classList.remove('border-transparent', 'text-slate-500');

        // Update review summary if on review tab
        if (tabName === 'review') {
            updateReviewSummary();
        }
    }

    function validateCurrentTab() {
        const activeTab = document.querySelector('.tab-content.active');
        const requiredFields = activeTab.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        // Additional validation for pricing tab
        if (activeTab.id === 'tab-pricing') {
            const minQty = parseFloat(document.getElementById('minimum_quantity_liters').value);
            const maxQty = parseFloat(document.getElementById('maximum_quantity_liters').value);

            if (maxQty < minQty) {
                Swal.fire('Validation Error', 'Maximum quantity cannot be less than minimum quantity', 'error');
                return false;
            }
        }

        if (!isValid) {
            Swal.fire('Validation Error', 'Please fill in all required fields', 'error');
        }

        return isValid;
    }

    function updateReviewSummary() {
        const formData = new FormData(document.getElementById('contractForm'));
        const supplierSelect = document.getElementById('supplier_id');
        const productSelect = document.getElementById('product_type');

        const summary = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-slate-900 mb-2">Contract Details</h4>
                    <p><span class="text-slate-600">Contract Number:</span> ${formData.get('contract_number')}</p>
                    <p><span class="text-slate-600">Supplier:</span> ${supplierSelect.selectedOptions[0]?.text || 'Not selected'}</p>
                    <p><span class="text-slate-600">Product:</span> ${productSelect.selectedOptions[0]?.text || 'Not selected'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-slate-900 mb-2">Pricing & Terms</h4>
                    <p><span class="text-slate-600">Base Price:</span> UGX ${parseFloat(formData.get('base_price_per_liter') || 0).toLocaleString()}</p>
                    <p><span class="text-slate-600">Quantity Range:</span> ${parseFloat(formData.get('minimum_quantity_liters') || 0).toLocaleString()} - ${parseFloat(formData.get('maximum_quantity_liters') || 0).toLocaleString()} liters</p>
                    <p><span class="text-slate-600">Valid:</span> ${formData.get('effective_from')} to ${formData.get('effective_until')}</p>
                </div>
            </div>
        `;

        document.getElementById('reviewSummary').innerHTML = summary;
    }

    // Form submission
    document.getElementById('contractForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateCurrentTab()) return;

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creating...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch('{{ route("contracts.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'View Contract'
                }).then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                Swal.fire('Error', data.error, 'error');
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const fieldElement = document.getElementById(field);
                        if (fieldElement) {
                            fieldElement.classList.add('border-red-500');
                        }
                    });
                }
            }
        })
        .catch(error => {
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
@endsection
