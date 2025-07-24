@extends('layouts.app')

@section('title', 'Add New Supplier')

@section('content')
<div class="min-h-screen bg-slate-50/50">
    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200/60">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Back Navigation -->
                    <button onclick="window.history.back()"
                        class="p-2 text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm">
                        <a href="{{ route('suppliers.index') }}"
                            class="text-slate-500 hover:text-slate-700 transition-colors duration-200">Suppliers</a>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-slate-900 font-medium">Add New Supplier</span>
                    </nav>
                </div>

                <!-- Save Actions -->
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="saveDraft()"
                        class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save Draft
                    </button>

                    <button type="submit" form="supplierForm"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transform hover:scale-[1.02] transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Create Supplier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="px-6 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Wizard -->
            <div class="mb-8">
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-4">
                        <!-- Step 1: Basic Info -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-semibold"
                                id="step1-indicator">
                                1
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-900" id="step1-label">Basic
                                Information</span>
                        </div>

                        <div class="w-16 h-0.5 bg-slate-200" id="progress1"></div>

                        <!-- Step 2: Contact Details -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 bg-slate-200 text-slate-500 rounded-full text-sm font-semibold"
                                id="step2-indicator">
                                2
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-500" id="step2-label">Contact
                                Details</span>
                        </div>

                        <div class="w-16 h-0.5 bg-slate-200" id="progress2"></div>

                        <!-- Step 3: Business Terms -->
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-8 h-8 bg-slate-200 text-slate-500 rounded-full text-sm font-semibold"
                                id="step3-indicator">
                                3
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-500" id="step3-label">Business Terms</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form id="supplierForm" class="space-y-8" novalidate>
                @csrf

                <!-- Step 1: Basic Information -->
                <div id="step1" class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-blue-50 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-900">Basic Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supplier Code -->
                        <div class="md:col-span-1">
                            <label for="supplier_code" class="block text-sm font-medium text-slate-700 mb-2">
                                Supplier Code <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="supplier_code" name="supplier_code" value="{{ $nextCode ?? '' }}"
                                    placeholder="e.g., SUP-000001"
                                    class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    pattern="[A-Z0-9\-]+" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" onclick="generateSupplierCode()"
                                        class="text-slate-400 hover:text-blue-600 transition-colors duration-200"
                                        title="Generate Code">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="supplier_code_error"></div>
                            <p class="text-xs text-slate-500 mt-1">Unique identifier (uppercase letters, numbers,
                                hyphens only)</p>
                        </div>

                        <!-- Company Name -->
                        <div class="md:col-span-1">
                            <label for="company_name" class="block text-sm font-medium text-slate-700 mb-2">
                                Company Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="company_name" name="company_name"
                                placeholder="e.g., Kampala Fuel Distributors Ltd"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="255" required>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="company_name_error"></div>
                        </div>

                        <!-- Contact Person -->
                        <div class="md:col-span-1">
                            <label for="contact_person" class="block text-sm font-medium text-slate-700 mb-2">
                                Primary Contact <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="contact_person" name="contact_person" placeholder="e.g., John Mukasa"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="255" required>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="contact_person_error"></div>
                        </div>

                        <!-- Tax Number -->
                        <div class="md:col-span-1">
                            <label for="tax_number" class="block text-sm font-medium text-slate-700 mb-2">
                                Tax Number
                            </label>
                            <input type="text" id="tax_number" name="tax_number" placeholder="e.g., 1000123456000"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="100">
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="tax_number_error"></div>
                            <p class="text-xs text-slate-500 mt-1">Optional - for tax reporting purposes</p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-end mt-6">
                        <button type="button" onclick="nextStep(2)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                            Next: Contact Details
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Contact Details -->
                <div id="step2" class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm hidden">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-emerald-50 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-900">Contact Details</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div class="md:col-span-1">
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" placeholder="e.g., contact@supplier.co.ug"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="255" required>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="email_error"></div>
                        </div>

                        <!-- Phone -->
                        <div class="md:col-span-1">
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" placeholder="e.g., +256700000000"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="50" required>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="phone_error"></div>
                        </div>

                        <!-- Address Line 1 -->
                        <div class="md:col-span-2">
                            <label for="address_line_1" class="block text-sm font-medium text-slate-700 mb-2">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="address_line_1" name="address_line_1"
                                placeholder="e.g., Plot 123, Kampala Road"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="255" required>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="address_line_1_error"></div>
                        </div>

                        <!-- Address Line 2 -->
                        <div class="md:col-span-2">
                            <label for="address_line_2" class="block text-sm font-medium text-slate-700 mb-2">
                                Additional Address
                            </label>
                            <input type="text" id="address_line_2" name="address_line_2"
                                placeholder="e.g., P.O. Box 1234 (optional)"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="255">
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="address_line_2_error"></div>
                        </div>

                        <!-- City -->
                        <div class="md:col-span-1">
                            <label for="city" class="block text-sm font-medium text-slate-700 mb-2">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="city" name="city" placeholder="e.g., Kampala"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="100" list="citySuggestions" required>
                            <datalist id="citySuggestions">
                                @foreach($suggestions['cities'] ?? [] as $city)
                                <option value="{{ $city }}">
                                    @endforeach
                            </datalist>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="city_error"></div>
                        </div>

                        <!-- Country -->
                        <div class="md:col-span-1">
                            <label for="country" class="block text-sm font-medium text-slate-700 mb-2">
                                Country <span class="text-red-500">*</span>
                            </label>
                            <select id="country" name="country"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                                @foreach($suggestions['countries'] ?? ['Uganda'] as $country)
                                <option value="{{ $country }}" {{ $country===($defaults['country'] ?? 'Uganda' )
                                    ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                                @endforeach
                            </select>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="country_error"></div>
                        </div>

                        <!-- Postal Code -->
                        <div class="md:col-span-1">
                            <label for="postal_code" class="block text-sm font-medium text-slate-700 mb-2">
                                Postal Code
                            </label>
                            <input type="text" id="postal_code" name="postal_code" placeholder="e.g., 00256"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                maxlength="20">
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="postal_code_error"></div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="prevStep(1)"
                            class="inline-flex items-center px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </button>
                        <button type="button" onclick="nextStep(3)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                            Next: Business Terms
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Business Terms -->
                <div id="step3" class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm hidden">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-amber-50 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-900">Business Terms</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Credit Limit -->
                        <div class="md:col-span-1">
                            <label for="credit_limit" class="block text-sm font-medium text-slate-700 mb-2">
                                Credit Limit <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" id="credit_limit" name="credit_limit"
                                    value="{{ $defaults['credit_limit'] ?? '0.00' }}" placeholder="0.00" step="0.01"
                                    min="0" max="999999999.99"
                                    class="block w-full pl-8 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    required>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 text-sm">UGX</span>
                                </div>
                            </div>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="credit_limit_error"></div>
                            <p class="text-xs text-slate-500 mt-1">Maximum outstanding amount allowed</p>
                        </div>

                        <!-- Payment Terms -->
                        <div class="md:col-span-1">
                            <label for="payment_terms_days" class="block text-sm font-medium text-slate-700 mb-2">
                                Payment Terms <span class="text-red-500">*</span>
                            </label>
                            <select id="payment_terms_days" name="payment_terms_days"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                                @foreach($suggestions['payment_terms'] ?? [30] as $days)
                                <option value="{{ $days }}" {{ $days===($defaults['payment_terms_days'] ?? 30)
                                    ? 'selected' : '' }}>
                                    {{ $days }} days
                                </option>
                                @endforeach
                            </select>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="payment_terms_days_error">
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="md:col-span-1">
                            <label for="currency_code" class="block text-sm font-medium text-slate-700 mb-2">
                                Currency <span class="text-red-500">*</span>
                            </label>
                            <select id="currency_code" name="currency_code"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                required>
                                @foreach($suggestions['currencies'] ?? ['UGX'] as $currency)
                                <option value="{{ $currency }}" {{ $currency===($defaults['currency_code'] ?? 'UGX' )
                                    ? 'selected' : '' }}>
                                    {{ $currency }}
                                </option>
                                @endforeach
                            </select>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="currency_code_error"></div>
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-1">
                            <label for="is_active" class="block text-sm font-medium text-slate-700 mb-2">
                                Status
                            </label>
                            <select id="is_active" name="is_active"
                                class="block w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="1" {{ ($defaults['is_active'] ?? 1) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !($defaults['is_active'] ?? 1) ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                            <div class="error-message text-sm text-red-600 mt-1 hidden" id="is_active_error"></div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="prevStep(2)"
                            class="inline-flex items-center px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Create Supplier
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Duplicate Warning Modal -->
<div id="duplicateModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="p-2 bg-amber-50 rounded-lg mr-3">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Similar Suppliers Found</h3>
        </div>
        <div id="duplicateContent" class="mb-6"></div>
        <div class="flex space-x-3">
            <button onclick="closeDuplicateModal()"
                class="flex-1 px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors duration-200">
                Review & Edit
            </button>
            <button onclick="forceCreateSupplier()"
                class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 transition-colors duration-200">
                Create Anyway
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay"
    class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 shadow-xl flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-2 border-blue-600 border-t-transparent"></div>
        <span class="text-sm font-medium text-slate-700">Creating supplier...</span>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    let formData = {};
    let duplicateWarning = null;

    // Step management
    window.nextStep = function(step) {
        if (validateCurrentStep()) {
            showStep(step);
        }
    };

    window.prevStep = function(step) {
        showStep(step);
    };

    function showStep(step) {
        // Hide all steps
        for (let i = 1; i <= 3; i++) {
            document.getElementById(`step${i}`).classList.add('hidden');
            const indicator = document.getElementById(`step${i}-indicator`);
            const label = document.getElementById(`step${i}-label`);

            if (i < step) {
                // Completed steps
                indicator.className = 'flex items-center justify-center w-8 h-8 bg-emerald-600 text-white rounded-full text-sm font-semibold';
                indicator.innerHTML = '✓';
                label.className = 'ml-2 text-sm font-medium text-emerald-600';
                document.getElementById(`progress${i}`).className = 'w-16 h-0.5 bg-emerald-600';
            } else if (i === step) {
                // Current step
                indicator.className = 'flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-semibold';
                indicator.innerHTML = i;
                label.className = 'ml-2 text-sm font-medium text-slate-900';
            } else {
                // Future steps
                indicator.className = 'flex items-center justify-center w-8 h-8 bg-slate-200 text-slate-500 rounded-full text-sm font-semibold';
                indicator.innerHTML = i;
                label.className = 'ml-2 text-sm font-medium text-slate-500';
                if (i > 1) {
                    document.getElementById(`progress${i-1}`).className = 'w-16 h-0.5 bg-slate-200';
                }
            }
        }

        // Show current step
        document.getElementById(`step${step}`).classList.remove('hidden');
        currentStep = step;

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        const step = document.getElementById(`step${currentStep}`);
        const requiredFields = step.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });

        // Additional validation
        if (currentStep === 1) {
            const supplierCode = document.getElementById('supplier_code');
            if (supplierCode.value && !/^[A-Z0-9\-]+$/.test(supplierCode.value)) {
                showFieldError(supplierCode, 'Only uppercase letters, numbers, and hyphens allowed');
                isValid = false;
            }
        }

        if (currentStep === 2) {
            const email = document.getElementById('email');
            if (email.value && !/\S+@\S+\.\S+/.test(email.value)) {
                showFieldError(email, 'Please enter a valid email address');
                isValid = false;
            }
        }

        return isValid;
    }

   function showFieldError(field, message) {
    const errorDiv = document.getElementById(field.id + '_error');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    // Visual feedback for error state
    field.classList.add('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
    field.classList.remove('border-slate-200', 'focus:ring-blue-500', 'focus:border-transparent');

    // Scroll to first error field
    if (!document.querySelector('.border-red-300[data-scrolled]')) {
        field.setAttribute('data-scrolled', 'true');
        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        field.focus();
    }
}

    function clearFieldError(field) {
        const errorDiv = document.getElementById(field.id + '_error');
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        field.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        field.classList.add('border-slate-200', 'focus:ring-blue-500', 'focus:border-transparent');
    }



    function clearAllErrors() {
    document.querySelectorAll('.error-message').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('input, select').forEach(field => {
        field.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        field.classList.add('border-slate-200', 'focus:ring-blue-500', 'focus:border-transparent');
        field.removeAttribute('data-scrolled');
    });
}
    // Auto-generate supplier code
    window.generateSupplierCode = function() {
        const timestamp = Date.now().toString().slice(-6);
        const code = `SUP-${timestamp}`;
        document.getElementById('supplier_code').value = code;
    };

    // Form submission
 document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validateCurrentStep()) {
        return;
    }

    const formData = new FormData(this);
    showLoading();

    fetch('{{ route('suppliers.store') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        // CRITICAL FIX: Always parse JSON first, then check status
        return response.json().then(data => ({
            status: response.status,
            data: data
        }));
    })
    .then(({status, data}) => {
        hideLoading();

        if (status === 200 || status === 201) {
            // SUCCESS RESPONSE
            if (data.success) {
                showNotification(data.message, 'success');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            }
        } else if (status === 422) {
            // VALIDATION ERROR RESPONSE - FIXED HANDLING
            if (data.errors) {
                // Clear all previous errors first
                document.querySelectorAll('.error-message').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('input, select').forEach(field => {
                    field.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
                    field.classList.add('border-slate-200', 'focus:ring-blue-500', 'focus:border-transparent');
                });

                // Display each validation error
                Object.keys(data.errors).forEach(fieldName => {
                    const fieldElement = document.getElementById(fieldName);
                    const errorMessage = Array.isArray(data.errors[fieldName])
                        ? data.errors[fieldName][0]
                        : data.errors[fieldName];

                    if (fieldElement) {
                        showFieldError(fieldElement, errorMessage);

                        // Navigate to the step containing the error field
                        const stepElement = fieldElement.closest('[id^="step"]');
                        if (stepElement) {
                            const stepNumber = stepElement.id.replace('step', '');
                            showStep(parseInt(stepNumber));
                        }
                    }
                });

                showNotification('Please correct the highlighted errors and try again', 'error');
            } else if (data.requires_confirmation && data.similar_suppliers) {
                // Handle duplicate warning
                duplicateWarning = data;
                showDuplicateModal(data.similar_suppliers);
            } else {
                showNotification(data.message || 'Validation failed', 'error');
            }
        } else if (status === 403) {
            showNotification('Access denied: ' + (data.error || 'Insufficient permissions'), 'error');
        } else if (status >= 500) {
            showNotification('Server error: ' + (data.error || 'Please try again later'), 'error');
        } else {
            showNotification(data.error || data.message || 'An unexpected error occurred', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Network error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
    });
});

    // Duplicate modal functions
    function showDuplicateModal(similarSuppliers) {
        let content = '<p class="text-sm text-slate-600 mb-4">We found similar suppliers that might be duplicates:</p>';

        if (similarSuppliers.name_matches) {
            content += '<div class="mb-3"><h4 class="text-sm font-medium text-slate-900 mb-2">Similar Names:</h4>';
            similarSuppliers.name_matches.forEach(supplier => {
                content += `<div class="text-sm text-slate-600">• ${supplier.company_name} (${supplier.supplier_code})</div>`;
            });
            content += '</div>';
        }

        if (similarSuppliers.email_matches) {
            content += '<div class="mb-3"><h4 class="text-sm font-medium text-slate-900 mb-2">Similar Email Domains:</h4>';
            similarSuppliers.email_matches.forEach(supplier => {
                content += `<div class="text-sm text-slate-600">• ${supplier.company_name} (${supplier.email})</div>`;
            });
            content += '</div>';
        }

        document.getElementById('duplicateContent').innerHTML = content;
        document.getElementById('duplicateModal').classList.remove('hidden');
    }

    window.closeDuplicateModal = function() {
        document.getElementById('duplicateModal').classList.add('hidden');
        duplicateWarning = null;
    };

    window.forceCreateSupplier = function() {
        const formData = new FormData(document.getElementById('supplierForm'));
        formData.append('force_create', '1');

        closeDuplicateModal();
        showLoading();

        fetch('{{ route('suppliers.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                showNotification(data.error || 'Failed to create supplier', 'error');
            }
        })
        .catch(error => {
            console.error('Force creation error:', error);
            showNotification('Network error. Please try again.', 'error');
        })
        .finally(() => {
            hideLoading();
        });
    };

    // Draft saving
    window.saveDraft = function() {
        const formData = new FormData(document.getElementById('supplierForm'));
        localStorage.setItem('supplierDraft', JSON.stringify(Object.fromEntries(formData)));
        showNotification('Draft saved successfully', 'success');
    };

    // Load draft
    function loadDraft() {
        const draft = localStorage.getItem('supplierDraft');
        if (draft) {
            const data = JSON.parse(draft);
            Object.keys(data).forEach(key => {
                const field = document.getElementById(key);
                if (field && data[key]) {
                    field.value = data[key];
                }
            });
        }
    }

    // Utility functions
    function showLoading() {
        document.getElementById('loadingOverlay')?.classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay')?.classList.add('hidden');
    }

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
        // Ctrl/Cmd + S for save draft
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveDraft();
        }

        // Ctrl/Cmd + Enter for submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('supplierForm').dispatchEvent(new Event('submit'));
        }
    });

    // Load draft on page load
    loadDraft();
});
</script>
@endpush

@push('styles')
<style>
    /* Enhanced form styling */
    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    /* Step transition animations */
    #step1,
    #step2,
    #step3 {
        transition: all 0.3s ease-in-out;
    }

    /* Progress animation */
    #progress1,
    #progress2 {
        transition: background-color 0.3s ease;
    }

    /* Enhanced button interactions */
    button:active {
        transform: scale(0.98);
    }

    /* Modal backdrop blur */
    .backdrop-blur-sm {
        backdrop-filter: blur(4px);
    }

    /* Custom number input styling */
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endpush
