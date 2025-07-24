@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-muted-foreground mb-4">
                    <a href="{{ route('tanks.select') }}" class="hover:text-foreground transition-colors">Tank
                        Management</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('tanks.index', $station->id) }}"
                        class="hover:text-foreground transition-colors">{{ $station->station_name }}</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-foreground font-medium">Add New Tank</span>
                </nav>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg">
                            <i class="fas fa-plus text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-foreground">Add New Tank</h1>
                            <p class="text-sm text-muted-foreground">{{ $station->station_code }} • Configure a new fuel
                                storage tank</p>
                        </div>
                    </div>

                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                        <i class="fas fa-crown w-3 h-3 mr-1"></i>
                        Auto-Approved
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('tanks.store', $station->id) }}" method="POST" id="tank-create-form" class="space-y-8">
            @csrf

            <!-- Basic Tank Information -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-foreground">Basic Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tank Number -->
                    <div>
                        <label for="tank_number" class="block text-sm font-medium text-foreground mb-2">
                            Tank Number <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="tank_number" name="tank_number"
                            value="{{ old('tank_number', $nextTankNumber) }}" min="1" max="999"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('tank_number') border-red-500 @enderror"
                            required>
                        @error('tank_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Unique identifier for this tank at the station</p>
                    </div>

                    <!-- Product Type -->
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-foreground mb-2">
                            Fuel Product <span class="text-red-500">*</span>
                        </label>
                        <select id="product_id" name="product_id"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('product_id') border-red-500 @enderror"
                            required>
                            <option value="">Select fuel product...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id')==$product->id ? 'selected' : '' }}>
                                {{ $product->product_name }} ({{ $product->product_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Type of fuel this tank will store</p>
                    </div>
                </div>
            </div>

            <!-- Capacity Configuration -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-green-600">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-foreground">Capacity & Operating Levels</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tank Capacity -->
                    <div class="md:col-span-2">
                        <label for="capacity_liters" class="block text-sm font-medium text-foreground mb-2">
                            Tank Capacity (Liters) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="capacity_liters" name="capacity_liters"
                            value="{{ old('capacity_liters') }}" min="1000" max="100000" step="0.001"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('capacity_liters') border-red-500 @enderror"
                            placeholder="e.g., 50000" required>
                        @error('capacity_liters')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Maximum storage capacity in liters (1,000L -
                            100,000L)</p>
                    </div>

                    <!-- Minimum Stock Level -->
                    <div>
                        <label for="minimum_stock_level_liters" class="block text-sm font-medium text-foreground mb-2">
                            Minimum Stock Level (Liters) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="minimum_stock_level_liters" name="minimum_stock_level_liters"
                            value="{{ old('minimum_stock_level_liters') }}" min="0" step="0.001"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('minimum_stock_level_liters') border-red-500 @enderror"
                            placeholder="e.g., 5000" required>
                        @error('minimum_stock_level_liters')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Reorder trigger level</p>
                    </div>

                    <!-- Critical Low Level -->
                    <div>
                        <label for="critical_low_level_liters" class="block text-sm font-medium text-foreground mb-2">
                            Critical Low Level (Liters) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="critical_low_level_liters" name="critical_low_level_liters"
                            value="{{ old('critical_low_level_liters') }}" min="0" step="0.001"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('critical_low_level_liters') border-red-500 @enderror"
                            placeholder="e.g., 1000" required>
                        @error('critical_low_level_liters')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Emergency low level alert threshold</p>
                    </div>

                    <!-- Maximum Variance -->
                    <div class="md:col-span-2">
                        <label for="maximum_variance_percentage" class="block text-sm font-medium text-foreground mb-2">
                            Maximum Variance Threshold (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="maximum_variance_percentage" name="maximum_variance_percentage"
                            value="{{ old('maximum_variance_percentage', '2.00') }}" min="0.1" max="10.0" step="0.01"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('maximum_variance_percentage') border-red-500 @enderror"
                            required>
                        @error('maximum_variance_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Acceptable variance before triggering approval
                            workflow (0.1% - 10.0%)</p>
                    </div>
                </div>

                <!-- Level Hierarchy Validation Display -->
                <div id="level-validation" class="mt-4 p-3 bg-muted/20 rounded-lg border border-border hidden">
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        <span class="font-medium text-foreground">Level Hierarchy:</span>
                        <span class="text-muted-foreground">Critical &lt; Minimum &lt; Capacity</span>
                    </div>
                </div>
            </div>

            <!-- Technical Specifications -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-cogs text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-foreground">Technical Specifications</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tank Type -->
                    <div>
                        <label for="tank_type" class="block text-sm font-medium text-foreground mb-2">
                            Tank Type <span class="text-red-500">*</span>
                        </label>
                        <select id="tank_type" name="tank_type"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('tank_type') border-red-500 @enderror"
                            required>
                            <option value="">Select tank type...</option>
                            <option value="UNDERGROUND" {{ old('tank_type')=='UNDERGROUND' ? 'selected' : '' }}>
                                Underground</option>
                            <option value="ABOVE_GROUND" {{ old('tank_type')=='ABOVE_GROUND' ? 'selected' : '' }}>Above
                                Ground</option>
                            <option value="PORTABLE" {{ old('tank_type')=='PORTABLE' ? 'selected' : '' }}>Portable
                            </option>
                        </select>
                        @error('tank_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tank Material -->
                    <div>
                        <label for="tank_material" class="block text-sm font-medium text-foreground mb-2">
                            Tank Material <span class="text-red-500">*</span>
                        </label>
                        <select id="tank_material" name="tank_material"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('tank_material') border-red-500 @enderror"
                            required>
                            <option value="">Select material...</option>
                            <option value="STEEL" {{ old('tank_material')=='STEEL' ? 'selected' : '' }}>Steel</option>
                            <option value="FIBERGLASS" {{ old('tank_material')=='FIBERGLASS' ? 'selected' : '' }}>
                                Fiberglass</option>
                            <option value="CONCRETE" {{ old('tank_material')=='CONCRETE' ? 'selected' : '' }}>Concrete
                            </option>
                        </select>
                        @error('tank_material')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manufacturer -->
                    <div>
                        <label for="tank_manufacturer" class="block text-sm font-medium text-foreground mb-2">
                            Manufacturer
                        </label>
                        <input type="text" id="tank_manufacturer" name="tank_manufacturer"
                            value="{{ old('tank_manufacturer') }}" maxlength="255"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('tank_manufacturer') border-red-500 @enderror"
                            placeholder="e.g., Tankmart Industries">
                        @error('tank_manufacturer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label for="tank_serial_number" class="block text-sm font-medium text-foreground mb-2">
                            Serial Number
                        </label>
                        <input type="text" id="tank_serial_number" name="tank_serial_number"
                            value="{{ old('tank_serial_number') }}" maxlength="100"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('tank_serial_number') border-red-500 @enderror"
                            placeholder="e.g., TM-2024-001">
                        @error('tank_serial_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Installation & Inspection -->
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-foreground">Installation & Inspection</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Installation Date -->
                    <div>
                        <label for="installation_date" class="block text-sm font-medium text-foreground mb-2">
                            Installation Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="installation_date" name="installation_date"
                            value="{{ old('installation_date') }}" max="{{ date('Y-m-d') }}"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('installation_date') border-red-500 @enderror"
                            required>
                        @error('installation_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Date when tank was installed</p>
                    </div>

                    <!-- Last Inspection -->
                    <div>
                        <label for="last_inspection_date" class="block text-sm font-medium text-foreground mb-2">
                            Last Inspection Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="last_inspection_date" name="last_inspection_date"
                            value="{{ old('last_inspection_date') }}" max="{{ date('Y-m-d') }}"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('last_inspection_date') border-red-500 @enderror"
                            required>
                        @error('last_inspection_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Most recent safety inspection date</p>
                    </div>

                    <!-- Calibration Certificate -->
                    <div class="md:col-span-2">
                        <label for="calibration_certificate" class="block text-sm font-medium text-foreground mb-2">
                            Calibration Certificate Reference
                        </label>
                        <input type="text" id="calibration_certificate" name="calibration_certificate"
                            value="{{ old('calibration_certificate') }}" maxlength="100"
                            class="block w-full px-3 py-2 border border-input rounded-lg bg-background text-foreground placeholder-muted-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-colors @error('calibration_certificate') border-red-500 @enderror"
                            placeholder="e.g., CERT-2024-TK001">
                        @error('calibration_certificate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted-foreground">Reference number for calibration documentation</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-border">
                <a href="{{ route('tanks.index', $station->id) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground border border-border rounded-lg hover:bg-muted transition-colors">
                    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                    Back to Tanks
                </a>

                <div class="flex items-center space-x-3">
                    <button type="button" id="reset-form"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground border border-border rounded-lg hover:bg-muted transition-colors">
                        <i class="fas fa-undo w-4 h-4 mr-2"></i>
                        Reset Form
                    </button>

                    <button type="submit" id="submit-btn"
                        class="inline-flex items-center px-6 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-lg font-medium text-sm transition-all duration-200 shadow-sm">
                        <i class="fas fa-plus w-4 h-4 mr-2"></i>
                        <span id="submit-text">Create Tank</span>
                        <div id="submit-spinner" class="hidden ml-2">
                            <i class="fas fa-spinner animate-spin w-4 h-4"></i>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tank-create-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const resetBtn = document.getElementById('reset-form');

    // Form field elements
    const capacityInput = document.getElementById('capacity_liters');
    const minimumInput = document.getElementById('minimum_stock_level_liters');
    const criticalInput = document.getElementById('critical_low_level_liters');
    const levelValidation = document.getElementById('level-validation');

    let formModified = false;
    let isSubmitting = false;

    // Track form modifications
    form.addEventListener('input', function() {
        formModified = true;
    });

    // Level hierarchy validation
    function validateLevelHierarchy() {
        const capacity = parseFloat(capacityInput.value) || 0;
        const minimum = parseFloat(minimumInput.value) || 0;
        const critical = parseFloat(criticalInput.value) || 0;

        let isValid = true;
        let messages = [];

        if (critical && minimum && critical >= minimum) {
            isValid = false;
            messages.push('Critical level must be less than minimum level');
        }

        if (minimum && capacity && minimum >= capacity) {
            isValid = false;
            messages.push('Minimum level must be less than capacity');
        }

        if (critical && capacity && critical >= capacity) {
            isValid = false;
            messages.push('Critical level must be less than capacity');
        }

        // Update validation display
        if (capacity && minimum && critical) {
            if (isValid) {
                levelValidation.className = 'mt-4 p-3 bg-green-50 rounded-lg border border-green-200';
                levelValidation.innerHTML = `
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="fas fa-check-circle text-green-600"></i>
                        <span class="font-medium text-green-800">Valid Hierarchy:</span>
                        <span class="text-green-700">${critical.toLocaleString()}L < ${minimum.toLocaleString()}L < ${capacity.toLocaleString()}L</span>
                    </div>
                `;
            } else {
                levelValidation.className = 'mt-4 p-3 bg-red-50 rounded-lg border border-red-200';
                levelValidation.innerHTML = `
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                            <span class="font-medium text-red-800">Invalid Level Hierarchy</span>
                        </div>
                        ${messages.map(msg => `<p class="text-sm text-red-700 ml-6">• ${msg}</p>`).join('')}
                    </div>
                `;
            }
            levelValidation.classList.remove('hidden');
        } else {
            levelValidation.classList.add('hidden');
        }

        return isValid;
    }

    // Add validation listeners
    [capacityInput, minimumInput, criticalInput].forEach(input => {
        if (input) {
            input.addEventListener('input', validateLevelHierarchy);
            input.addEventListener('blur', validateLevelHierarchy);
        }
    });

    // Auto-calculate maximum stock level (95% of capacity)
    if (capacityInput) {
        capacityInput.addEventListener('input', function() {
            const capacity = parseFloat(this.value);
            if (capacity && capacity >= 1000) {
                // Auto-suggest minimum as 10% of capacity
                if (minimumInput && !minimumInput.value) {
                    minimumInput.value = Math.round(capacity * 0.1);
                }
                // Auto-suggest critical as 2% of capacity
                if (criticalInput && !criticalInput.value) {
                    criticalInput.value = Math.round(capacity * 0.02);
                }
                validateLevelHierarchy();
            }
        });
    }

    // Form submission handling
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        // Validate level hierarchy before submission
        if (!validateLevelHierarchy()) {
            e.preventDefault();
            Swal.fire({
                title: 'Invalid Configuration',
                text: 'Please fix the level hierarchy errors before proceeding.',
                icon: 'error',
                confirmButtonColor: 'hsl(var(--primary))'
            });
            return;
        }

        // Show loading state
        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Creating Tank...';
        submitSpinner.classList.remove('hidden');

        formModified = false;
    });

    // Reset form
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (formModified) {
                Swal.fire({
                    title: 'Reset Form?',
                    text: 'All entered data will be lost. This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'hsl(var(--destructive))',
                    cancelButtonColor: 'hsl(var(--muted-foreground))',
                    confirmButtonText: 'Yes, Reset',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.reset();
                        formModified = false;
                        levelValidation.classList.add('hidden');

                        // Reset to next tank number
                        const tankNumberInput = document.getElementById('tank_number');
                        if (tankNumberInput) {
                            tankNumberInput.value = '{{ $nextTankNumber }}';
                        }
                    }
                });
            } else {
                form.reset();
                levelValidation.classList.add('hidden');
            }
        });
    }

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (formModified && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Disable submit button during submission to prevent double-click
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!isSubmitting) {
                setTimeout(() => {
                    if (!form.checkValidity()) {
                        isSubmitting = false;
                        submitBtn.disabled = false;
                        submitText.textContent = 'Create Tank';
                        submitSpinner.classList.add('hidden');
                    }
                }, 100);
            }
        });
    }

    // Focus first input on load
    const firstInput = form.querySelector('input:not([type="hidden"])');
    if (firstInput) {
        firstInput.focus();
    }
});
</script>
@endsection
