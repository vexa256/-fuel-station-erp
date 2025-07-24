@extends('layouts.app')

@section('title', 'Edit Tank')

@section('content')
<div class="min-h-screen bg-background p-4 sm:p-6 lg:p-8">
    <div class="mx-auto max-w-4xl">
        <!-- Header Navigation -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-muted-foreground">
                <a href="{{ route('tanks.select') }}" class="hover:text-foreground transition-colors">Tank Management</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('tanks.index', $tank->station_id) }}" class="hover:text-foreground transition-colors">{{ $tank->station_name }}</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-foreground font-medium">Edit Tank {{ $tank->tank_number }}</span>
            </nav>
        </div>

        <!-- Tank Information Header -->
        <div class="bg-card border border-border rounded-lg p-6 mb-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground mb-2">Edit Tank {{ $tank->tank_number }}</h1>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-gas-pump text-xs"></i>
                            {{ $tank->station_name }} ({{ $tank->station_code }})
                        </span>
                        <span class="flex items-center gap-2">
                            <i class="fas fa-oil-can text-xs"></i>
                            {{ $tank->product_name }}
                        </span>
                        <span class="flex items-center gap-2">
                            <i class="fas fa-tachometer-alt text-xs"></i>
                            {{ number_format($tank->capacity_liters, 0) }}L Capacity
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge {{ $tank->is_active ? 'badge-default' : 'bg-destructive text-destructive-foreground' }}">
                        {{ $tank->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($tank->calibration_valid_until < now()->toDateString())
                        <span class="badge bg-destructive text-destructive-foreground">
                            <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                            Calibration Expired
                        </span>
                        @endif
                </div>
            </div>
        </div>

        <!-- Current Inventory Alert -->
        @if($currentInventory > 0)
        <div class="bg-accent border border-border rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-accent-foreground mt-0.5"></i>
                <div>
                    <h3 class="font-medium text-accent-foreground">Current Inventory Notice</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        This tank currently contains <strong>{{ number_format($currentInventory, 0) }} liters</strong> of fuel.
                        Tank capacity cannot be reduced below the current inventory level.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Edit Form -->
        <form method="POST" action="{{ route('tanks.update', $tank->id) }}" class="space-y-6" id="tank-edit-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information Card -->
                <div class="bg-card border border-border rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-primary text-sm"></i>
                        Basic Information
                    </h2>

                    <div class="space-y-4">
                        <!-- Tank Number (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Tank Number</label>
                            <input type="text" value="{{ $tank->tank_number }}" class="input input-bordered w-full bg-muted" readonly>
                            <p class="text-xs text-muted-foreground mt-1">Tank number cannot be changed after creation</p>
                        </div>

                        <!-- Product Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Product Type</label>
                            <input type="text" value="{{ $tank->product_name }} ({{ $tank->product_type }})" class="input input-bordered w-full bg-muted" readonly>
                            <p class="text-xs text-muted-foreground mt-1">Product type cannot be changed after creation</p>
                        </div>

                        <!-- Tank Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Tank Type</label>
                            <input type="text" value="{{ ucfirst(strtolower(str_replace('_', ' ', $tank->tank_type))) }}" class="input input-bordered w-full bg-muted" readonly>
                        </div>

                        <!-- Tank Material (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Tank Material</label>
                            <input type="text" value="{{ ucfirst(strtolower($tank->tank_material)) }}" class="input input-bordered w-full bg-muted" readonly>
                        </div>
                    </div>
                </div>

                <!-- Capacity & Levels Card -->
                <div class="bg-card border border-border rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                        <i class="fas fa-tachometer-alt text-primary text-sm"></i>
                        Capacity & Levels
                    </h2>

                    <div class="space-y-4">
                        <!-- Capacity -->
                        <div>
                            <label for="capacity_liters" class="block text-sm font-medium text-foreground mb-1">
                                Tank Capacity (Liters) <span class="text-destructive">*</span>
                            </label>
                            <input type="number"
                                id="capacity_liters"
                                name="capacity_liters"
                                value="{{ old('capacity_liters', $tank->capacity_liters) }}"
                                min="{{ $currentInventory }}"
                                max="100000"
                                step="0.001"
                                class="input input-bordered w-full @error('capacity_liters') border-destructive @enderror"
                                required>
                            @error('capacity_liters')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                            @if($currentInventory > 0)
                            <p class="text-xs text-muted-foreground mt-1">Minimum: {{ number_format($currentInventory, 0) }}L (current inventory)</p>
                            @endif
                        </div>

                        <!-- Minimum Stock Level -->
                        <div>
                            <label for="minimum_stock_level_liters" class="block text-sm font-medium text-foreground mb-1">
                                Minimum Stock Level (Liters) <span class="text-destructive">*</span>
                            </label>
                            <input type="number"
                                id="minimum_stock_level_liters"
                                name="minimum_stock_level_liters"
                                value="{{ old('minimum_stock_level_liters', $tank->minimum_stock_level_liters) }}"
                                min="0"
                                step="0.001"
                                class="input input-bordered w-full @error('minimum_stock_level_liters') border-destructive @enderror"
                                required>
                            @error('minimum_stock_level_liters')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Critical Low Level -->
                        <div>
                            <label for="critical_low_level_liters" class="block text-sm font-medium text-foreground mb-1">
                                Critical Low Level (Liters) <span class="text-destructive">*</span>
                            </label>
                            <input type="number"
                                id="critical_low_level_liters"
                                name="critical_low_level_liters"
                                value="{{ old('critical_low_level_liters', $tank->critical_low_level_liters) }}"
                                min="0"
                                step="0.001"
                                class="input input-bordered w-full @error('critical_low_level_liters') border-destructive @enderror"
                                required>
                            @error('critical_low_level_liters')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Maximum Stock Level (Auto-calculated) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Maximum Stock Level (Auto-calculated)</label>
                            <input type="text" id="max_stock_display" class="input input-bordered w-full bg-muted" readonly>
                            <p class="text-xs text-muted-foreground mt-1">Automatically set to 95% of tank capacity</p>
                        </div>

                        <!-- Maximum Variance Percentage -->
                        <div>
                            <label for="maximum_variance_percentage" class="block text-sm font-medium text-foreground mb-1">
                                Maximum Variance Percentage <span class="text-destructive">*</span>
                            </label>
                            <input type="number"
                                id="maximum_variance_percentage"
                                name="maximum_variance_percentage"
                                value="{{ old('maximum_variance_percentage', $tank->maximum_variance_percentage) }}"
                                min="0.1"
                                max="10.0"
                                step="0.1"
                                class="input input-bordered w-full @error('maximum_variance_percentage') border-destructive @enderror"
                                required>
                            @error('maximum_variance_percentage')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground mt-1">Acceptable variance range: 0.1% - 10.0%</p>
                        </div>
                    </div>
                </div>

                <!-- Equipment Details Card -->
                <div class="bg-card border border-border rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                        <i class="fas fa-cogs text-primary text-sm"></i>
                        Equipment Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Tank Manufacturer -->
                        <div>
                            <label for="tank_manufacturer" class="block text-sm font-medium text-foreground mb-1">Tank Manufacturer</label>
                            <input type="text"
                                id="tank_manufacturer"
                                name="tank_manufacturer"
                                value="{{ old('tank_manufacturer', $tank->tank_manufacturer) }}"
                                maxlength="255"
                                class="input input-bordered w-full @error('tank_manufacturer') border-destructive @enderror">
                            @error('tank_manufacturer')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tank Serial Number -->
                        <div>
                            <label for="tank_serial_number" class="block text-sm font-medium text-foreground mb-1">Tank Serial Number</label>
                            <input type="text"
                                id="tank_serial_number"
                                name="tank_serial_number"
                                value="{{ old('tank_serial_number', $tank->tank_serial_number) }}"
                                maxlength="100"
                                class="input input-bordered w-full @error('tank_serial_number') border-destructive @enderror">
                            @error('tank_serial_number')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Safety Features -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-foreground">Safety Features</h3>

                            <div class="flex items-center space-x-3">
                                <input type="hidden" name="leak_detection_system" value="0">
                                <input type="checkbox"
                                    id="leak_detection_system"
                                    name="leak_detection_system"
                                    value="1"
                                    {{ old('leak_detection_system', $tank->leak_detection_system) ? 'checked' : '' }}
                                    class="checkbox">
                                <label for="leak_detection_system" class="text-sm text-foreground">Leak Detection System</label>
                            </div>

                            <div class="flex items-center space-x-3">
                                <input type="hidden" name="overfill_protection" value="0">
                                <input type="checkbox"
                                    id="overfill_protection"
                                    name="overfill_protection"
                                    value="1"
                                    {{ old('overfill_protection', $tank->overfill_protection) ? 'checked' : '' }}
                                    class="checkbox">
                                <label for="overfill_protection" class="text-sm text-foreground">Overfill Protection</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspection & Calibration Card -->
                <div class="bg-card border border-border rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-primary text-sm"></i>
                        Inspection & Calibration
                    </h2>

                    <div class="space-y-4">
                        <!-- Last Inspection Date -->
                        <div>
                            <label for="last_inspection_date" class="block text-sm font-medium text-foreground mb-1">
                                Last Inspection Date <span class="text-destructive">*</span>
                            </label>
                            <input type="date"
                                id="last_inspection_date"
                                name="last_inspection_date"
                                value="{{ old('last_inspection_date', $tank->last_inspection_date) }}"
                                max="{{ now()->toDateString() }}"
                                class="input input-bordered w-full @error('last_inspection_date') border-destructive @enderror"
                                required>
                            @error('last_inspection_date')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Next Inspection Date (Auto-calculated) -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Next Inspection Date (Auto-calculated)</label>
                            <input type="text" id="next_inspection_display" class="input input-bordered w-full bg-muted" readonly>
                            <p class="text-xs text-muted-foreground mt-1">Automatically set to 1 year after last inspection</p>
                        </div>

                        <!-- Calibration Certificate -->
                        <div>
                            <label for="calibration_certificate" class="block text-sm font-medium text-foreground mb-1">Calibration Certificate Number</label>
                            <input type="text"
                                id="calibration_certificate"
                                name="calibration_certificate"
                                value="{{ old('calibration_certificate', $tank->calibration_certificate) }}"
                                maxlength="100"
                                class="input input-bordered w-full @error('calibration_certificate') border-destructive @enderror">
                            @error('calibration_certificate')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Calibration Status -->
                        <div class="p-3 bg-accent rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Calibration Status</span>
                                @if($tank->calibration_valid_until < now()->toDateString())
                                    <span class="badge bg-destructive text-destructive-foreground">
                                        <i class="fas fa-exclamation-triangle text-xs mr-1"></i>
                                        Expired
                                    </span>
                                    @else
                                    <span class="badge badge-default">
                                        <i class="fas fa-check-circle text-xs mr-1"></i>
                                        Valid
                                    </span>
                                    @endif
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                Valid until: {{ \Carbon\Carbon::parse($tank->calibration_valid_until)->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Controls (CEO/SYSTEM_ADMIN only) -->
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
            <div class="bg-card border border-border rounded-lg p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-primary text-sm"></i>
                    Administrative Controls
                </h2>

                <div class="flex items-center space-x-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $tank->is_active) ? 'checked' : '' }}
                        class="checkbox">
                    <label for="is_active" class="text-sm text-foreground">Tank is Active</label>
                </div>
                <p class="text-xs text-muted-foreground mt-1">Deactivating a tank will prevent new readings and deliveries</p>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-border">
                <button type="submit" class="btn btn-primary flex items-center justify-center gap-2 sm:order-2">
                    <i class="fas fa-save text-sm"></i>
                    Update Tank
                </button>
                <a href="{{ route('tanks.index', $tank->station_id) }}" class="btn btn-ghost flex items-center justify-center gap-2 sm:order-1">
                    <i class="fas fa-arrow-left text-sm"></i>
                    Back to Tank List
                </a>
                <a href="{{ route('tanks.calibration', $tank->id) }}" class="btn btn-outline flex items-center justify-center gap-2 sm:order-3">
                    <i class="fas fa-ruler text-sm"></i>
                    Manage Calibration
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const capacityInput = document.getElementById('capacity_liters');
        const minStockInput = document.getElementById('minimum_stock_level_liters');
        const criticalLevelInput = document.getElementById('critical_low_level_liters');
        const maxStockDisplay = document.getElementById('max_stock_display');
        const lastInspectionInput = document.getElementById('last_inspection_date');
        const nextInspectionDisplay = document.getElementById('next_inspection_display');
        const form = document.getElementById('tank-edit-form');

        // Update max stock level when capacity changes
        function updateMaxStock() {
            const capacity = parseFloat(capacityInput.value) || 0;
            const maxStock = capacity * 0.95;
            maxStockDisplay.value = maxStock.toLocaleString('en-US', {
                maximumFractionDigits: 0
            }) + ' L';
        }

        // Update next inspection date when last inspection changes
        function updateNextInspection() {
            if (lastInspectionInput.value) {
                const lastDate = new Date(lastInspectionInput.value);
                const nextDate = new Date(lastDate);
                nextDate.setFullYear(nextDate.getFullYear() + 1);

                nextInspectionDisplay.value = nextDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }

        // Validate level hierarchy
        function validateLevels() {
            const capacity = parseFloat(capacityInput.value) || 0;
            const minStock = parseFloat(minStockInput.value) || 0;
            const criticalLevel = parseFloat(criticalLevelInput.value) || 0;

            // Clear previous custom validity
            minStockInput.setCustomValidity('');
            criticalLevelInput.setCustomValidity('');

            if (minStock >= capacity) {
                minStockInput.setCustomValidity('Minimum stock level must be less than tank capacity');
            }

            if (criticalLevel >= minStock) {
                criticalLevelInput.setCustomValidity('Critical level must be less than minimum stock level');
            }
        }

        // Event listeners
        capacityInput.addEventListener('input', function() {
            updateMaxStock();
            validateLevels();
        });

        minStockInput.addEventListener('input', validateLevels);
        criticalLevelInput.addEventListener('input', validateLevels);
        lastInspectionInput.addEventListener('change', updateNextInspection);

        // Initialize calculations
        updateMaxStock();
        updateNextInspection();

        // Form submission with Swal2
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: 'Update Tank Configuration?',
                text: 'This will update the tank settings and configuration.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Update Tank',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Keyboard shortcut for form submission
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    });

    // Display success message if present
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Tank Updated',
        text: '{{ session('
        success ') }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    @endif

    // Display error message if present
    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: '{{ session('
        error ') }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000
    });
    @endif
</script>
@endsection
