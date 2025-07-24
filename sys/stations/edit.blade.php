@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="border-b border-border bg-card/50 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('stations.index') }}"
                    class="btn btn-ghost p-2 hover:bg-accent rounded-lg transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-foreground">Edit Station</h1>
                        <span class="badge badge-outline text-xs">{{ $station->station_code }}</span>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ $station->station_name }} • Last updated
                        {{ $station->updated_at
                        ? \Carbon\Carbon::parse($station->updated_at)->diffForHumans()
                        : 'Never'
                        }}
                    </p>

                </div>
                <div class="flex items-center gap-3">
                    @if($station->is_active)
                    <span class="badge badge-default bg-green-100 text-green-700 border border-green-200">
                        <i class="fas fa-check-circle text-xs mr-1"></i>
                        Active
                    </span>
                    @else
                    <span class="badge badge-default bg-red-100 text-red-700 border border-red-200">
                        <i class="fas fa-times-circle text-xs mr-1"></i>
                        Inactive
                    </span>
                    @endif
                    <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-md font-medium">
                        {{ auth()->user()->role }} Access
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between bg-muted/50 rounded-lg p-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('stations.dashboard', $station->id) }}"
                    class="btn btn-ghost btn-sm inline-flex items-center gap-2">
                    <i class="fas fa-chart-line text-sm"></i>
                    <span>View Dashboard</span>
                </a>
                <button type="button" onclick="showChangeHistory()"
                    class="btn btn-ghost btn-sm inline-flex items-center gap-2">
                    <i class="fas fa-history text-sm"></i>
                    <span>Change History</span>
                </button>
            </div>
            <div class="text-sm text-muted-foreground" id="unsavedChanges" style="display: none;">
                <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>
                You have unsaved changes
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container mx-auto px-4 pb-8">
        <form method="POST" action="{{ route('stations.update', $station->id) }}" id="stationEditForm"
            class="max-w-4xl mx-auto">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-primary"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Basic Information</h2>
                        <p class="text-sm text-muted-foreground">Essential station identification details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Station Code (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Station Code
                        </label>
                        <div class="relative">
                            <input type="text" value="{{ $station->station_code }}"
                                class="input w-full bg-muted/50 text-muted-foreground cursor-not-allowed" readonly
                                disabled>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-lock text-muted-foreground text-sm"></i>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Station code cannot be changed after creation</p>
                    </div>

                    <!-- Station Name -->
                    <div>
                        <label for="station_name" class="block text-sm font-medium text-foreground mb-2">
                            Station Name <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="station_name" name="station_name"
                            value="{{ old('station_name', $station->station_name) }}"
                            data-original="{{ $station->station_name }}" placeholder="e.g., Kampala Central Station"
                            class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('station_name') border-destructive @enderror"
                            required maxlength="255">
                        @error('station_name')
                        <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Region -->
                    <div>
                        <label for="region" class="block text-sm font-medium text-foreground mb-2">
                            Region <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="region" name="region" value="{{ old('region', $station->region) }}"
                            data-original="{{ $station->region }}" placeholder="e.g., Central Region"
                            class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('region') border-destructive @enderror"
                            required maxlength="100" list="regionList">
                        <datalist id="regionList">
                            <option value="Central Region">
                            <option value="Western Region">
                            <option value="Eastern Region">
                            <option value="Northern Region">
                        </datalist>
                        @error('region')
                        <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- District -->
                    <div>
                        <label for="district" class="block text-sm font-medium text-foreground mb-2">
                            District <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="district" name="district"
                            value="{{ old('district', $station->district) }}" data-original="{{ $station->district }}"
                            placeholder="e.g., Kampala"
                            class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('district') border-destructive @enderror"
                            required maxlength="100">
                        @error('district')
                        <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <!-- Station Status (CEO/SYSTEM_ADMIN Only) -->
                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-amber-800">Station Status</h3>
                            <p class="text-xs text-amber-600 mt-1">
                                Deactivating a station will affect all related operations and user access
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',
                                    $station->is_active) ? 'checked' : '' }}
                                data-original="{{ $station->is_active ? 'true' : 'false' }}"
                                class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-2"
                                onchange="confirmStatusChange(this)">
                                <span class="ml-2 text-sm font-medium text-amber-800">
                                    {{ $station->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Section 2: Location Details -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Location Details</h2>
                        <p class="text-sm text-muted-foreground">Physical address and contact information</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Address Lines -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="address_line_1" class="block text-sm font-medium text-foreground mb-2">
                                Address Line 1 <span class="text-destructive">*</span>
                            </label>
                            <input type="text" id="address_line_1" name="address_line_1"
                                value="{{ old('address_line_1', $station->address_line_1) }}"
                                data-original="{{ $station->address_line_1 }}" placeholder="Street address"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('address_line_1') border-destructive @enderror"
                                required maxlength="255">
                            @error('address_line_1')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address_line_2" class="block text-sm font-medium text-foreground mb-2">
                                Address Line 2
                            </label>
                            <input type="text" id="address_line_2" name="address_line_2"
                                value="{{ old('address_line_2', $station->address_line_2) }}"
                                data-original="{{ $station->address_line_2 }}"
                                placeholder="Apartment, suite, etc. (optional)"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('address_line_2') border-destructive @enderror"
                                maxlength="255">
                            @error('address_line_2')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- City and Postal Code -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="city" class="block text-sm font-medium text-foreground mb-2">
                                City <span class="text-destructive">*</span>
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city', $station->city) }}"
                                data-original="{{ $station->city }}" placeholder="City name"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('city') border-destructive @enderror"
                                required maxlength="100">
                            @error('city')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-foreground mb-2">
                                Postal Code
                            </label>
                            <input type="text" id="postal_code" name="postal_code"
                                value="{{ old('postal_code', $station->postal_code) }}"
                                data-original="{{ $station->postal_code }}" placeholder="Postal code (optional)"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('postal_code') border-destructive @enderror"
                                maxlength="20">
                            @error('postal_code')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Coordinates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-foreground mb-2">
                                Latitude
                            </label>
                            <input type="number" id="latitude" name="latitude"
                                value="{{ old('latitude', $station->latitude) }}"
                                data-original="{{ $station->latitude }}" placeholder="e.g., 0.3476" step="any" min="-90"
                                max="90"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('latitude') border-destructive @enderror">
                            @error('latitude')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground mt-1">Range: -90 to 90</p>
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-foreground mb-2">
                                Longitude
                            </label>
                            <input type="number" id="longitude" name="longitude"
                                value="{{ old('longitude', $station->longitude) }}"
                                data-original="{{ $station->longitude }}" placeholder="e.g., 32.5825" step="any"
                                min="-180" max="180"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('longitude') border-destructive @enderror">
                            @error('longitude')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground mt-1">Range: -180 to 180</p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-foreground mb-2">
                                Phone Number
                            </label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $station->phone) }}"
                                data-original="{{ $station->phone }}" placeholder="e.g., +256 700 000 000"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('phone') border-destructive @enderror"
                                maxlength="50">
                            @error('phone')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $station->email) }}"
                                data-original="{{ $station->email }}" placeholder="station@example.com"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('email') border-destructive @enderror"
                                maxlength="255">
                            @error('email')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Operations Setup -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cogs text-primary"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-foreground">Operations Setup</h2>
                        <p class="text-sm text-muted-foreground">Operating hours, management, and licensing</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Operating Hours -->
                    <div>
                        <h3 class="text-lg font-medium text-foreground mb-4">Operating Hours</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="operating_hours_open"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    Opening Time <span class="text-destructive">*</span>
                                </label>
                                <input type="time" id="operating_hours_open" name="operating_hours_open"
                                    value="{{ old('operating_hours_open', $station->operating_hours_open) }}"
                                    data-original="{{ $station->operating_hours_open }}"
                                    class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('operating_hours_open') border-destructive @enderror"
                                    required>
                                @error('operating_hours_open')
                                <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="operating_hours_close"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    Closing Time <span class="text-destructive">*</span>
                                </label>
                                <input type="time" id="operating_hours_close" name="operating_hours_close"
                                    value="{{ old('operating_hours_close', $station->operating_hours_close) }}"
                                    data-original="{{ $station->operating_hours_close }}"
                                    class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('operating_hours_close') border-destructive @enderror"
                                    required>
                                @error('operating_hours_close')
                                <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-muted-foreground mt-1">Must be after opening time</p>
                            </div>
                        </div>
                    </div>

                    <!-- Station Manager -->
                    <div>
                        <h3 class="text-lg font-medium text-foreground mb-4">Station Management</h3>
                        @if($station->manager_user_id)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-blue-800">Current Manager</div>
                                    <div class="text-xs text-blue-600">
                                        {{ DB::table('users')->where('id',
                                        $station->manager_user_id)->value(DB::raw('CONCAT(first_name, " ", last_name)'))
                                        }}
                                        ({{ DB::table('users')->where('id',
                                        $station->manager_user_id)->value('employee_number') }})
                                    </div>
                                </div>
                                <button type="button" onclick="confirmManagerChange()"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Change Manager
                                </button>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label for="manager_user_id" class="block text-sm font-medium text-foreground mb-2">
                                {{ $station->manager_user_id ? 'New Manager' : 'Station Manager' }}
                            </label>
                            <select id="manager_user_id" name="manager_user_id"
                                data-original="{{ $station->manager_user_id }}"
                                class="select w-full md:w-1/2 transition-all focus:ring-2 focus:ring-primary/20 @error('manager_user_id') border-destructive @enderror">
                                <option value="">{{ $station->manager_user_id ? 'Keep current manager' : 'Select a
                                    manager (optional)' }}</option>
                                @foreach($potentialManagers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_user_id')==$manager->id ? 'selected'
                                    : '' }}>
                                    {{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->employee_number
                                    }})
                                </option>
                                @endforeach
                            </select>
                            @error('manager_user_id')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @if($station->manager_user_id)
                            <p class="text-xs text-muted-foreground mt-1">
                                Changing the manager will update station access permissions
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Licensing Information -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <input type="checkbox" id="has_license" {{ $station->license_number ? 'checked' : '' }}
                            class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-2">
                            <label for="has_license" class="text-lg font-medium text-foreground">
                                Licensing Information
                            </label>
                        </div>

                        <div id="licenseFields" class="grid grid-cols-1 md:grid-cols-2 gap-6"
                            style="{{ $station->license_number ? 'display: grid;' : 'display: none;' }}">
                            <div>
                                <label for="license_number" class="block text-sm font-medium text-foreground mb-2">
                                    License Number
                                </label>
                                <input type="text" id="license_number" name="license_number"
                                    value="{{ old('license_number', $station->license_number) }}"
                                    data-original="{{ $station->license_number }}" placeholder="License number"
                                    class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('license_number') border-destructive @enderror"
                                    maxlength="100">
                                @error('license_number')
                                <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="license_expiry_date" class="block text-sm font-medium text-foreground mb-2">
                                    License Expiry Date
                                </label>
                                <input type="date" id="license_expiry_date" name="license_expiry_date"
                                    value="{{ old('license_expiry_date', $station->license_expiry_date) }}"
                                    data-original="{{ $station->license_expiry_date }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('license_expiry_date') border-destructive @enderror">
                                @error('license_expiry_date')
                                <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-muted-foreground mt-1">Must be a future date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card border border-border rounded-lg p-6">
                <div class="flex items-center gap-4">
                    <button type="button" onclick="resetForm()" class="btn btn-ghost inline-flex items-center gap-2">
                        <i class="fas fa-undo text-sm"></i>
                        <span>Reset Changes</span>
                    </button>
                    <div id="changesSummary" class="text-sm text-muted-foreground" style="display: none;">
                        <span id="changesCount">0</span> field(s) modified
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('stations.index') }}" class="btn btn-secondary inline-flex items-center gap-2">
                        <i class="fas fa-times text-sm"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2" id="submitBtn">
                        <i class="fas fa-save text-sm"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for Change Tracking and Validation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stationEditForm');
    const submitBtn = document.getElementById('submitBtn');
    const unsavedChanges = document.getElementById('unsavedChanges');
    const changesSummary = document.getElementById('changesSummary');
    const changesCount = document.getElementById('changesCount');
    let hasUnsavedChanges = false;

    // Track form changes
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            trackChanges();
            validateField(this);
        });

        if (input.type === 'text' || input.type === 'email' || input.type === 'tel') {
            input.addEventListener('input', function() {
                trackChanges();
            });
        }
    });

    // License fields toggle
    const hasLicenseCheckbox = document.getElementById('has_license');
    const licenseFields = document.getElementById('licenseFields');

    hasLicenseCheckbox.addEventListener('change', function() {
        if (this.checked) {
            licenseFields.style.display = 'grid';
        } else {
            licenseFields.style.display = 'none';
            document.getElementById('license_number').value = '';
            document.getElementById('license_expiry_date').value = '';
        }
        trackChanges();
    });

    // Operating hours validation
    const openTime = document.getElementById('operating_hours_open');
    const closeTime = document.getElementById('operating_hours_close');

    function validateOperatingHours() {
        if (openTime.value && closeTime.value) {
            if (closeTime.value <= openTime.value) {
                closeTime.setCustomValidity('Closing time must be after opening time');
                closeTime.classList.add('border-destructive');
            } else {
                closeTime.setCustomValidity('');
                closeTime.classList.remove('border-destructive');
            }
        }
    }

    openTime.addEventListener('change', validateOperatingHours);
    closeTime.addEventListener('change', validateOperatingHours);

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!hasUnsavedChanges) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'No Changes Detected',
                text: 'No changes have been made to save.',
                confirmButtonColor: 'hsl(var(--primary))'
            });
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm mr-2"></i>Saving Changes...';
    });

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    function trackChanges() {
        let changedFields = 0;
        hasUnsavedChanges = false;

        formInputs.forEach(input => {
            const originalValue = input.dataset.original || '';
            const currentValue = input.type === 'checkbox' ? (input.checked ? 'true' : 'false') : input.value;

            if (originalValue !== currentValue) {
                hasUnsavedChanges = true;
                changedFields++;
                input.classList.add('border-primary', 'bg-primary/5');
            } else {
                input.classList.remove('border-primary', 'bg-primary/5');
            }
        });

        if (hasUnsavedChanges) {
            unsavedChanges.style.display = 'block';
            changesSummary.style.display = 'block';
            changesCount.textContent = changedFields;
        } else {
            unsavedChanges.style.display = 'none';
            changesSummary.style.display = 'none';
        }
    }

    function validateField(field) {
        // Add custom validation logic here
        field.classList.remove('border-destructive');
    }

    // Initial change tracking
    trackChanges();
});

function resetForm() {
    if (!confirm('Are you sure you want to reset all changes? This cannot be undone.')) {
        return;
    }

    const form = document.getElementById('stationEditForm');
    const formInputs = form.querySelectorAll('input, select, textarea');

    formInputs.forEach(input => {
        const originalValue = input.dataset.original || '';

        if (input.type === 'checkbox') {
            input.checked = originalValue === 'true';
        } else {
            input.value = originalValue;
        }

        input.classList.remove('border-primary', 'bg-primary/5', 'border-destructive');
    });

    // Reset license fields visibility
    const hasLicenseCheckbox = document.getElementById('has_license');
    const licenseFields = document.getElementById('licenseFields');

    if (hasLicenseCheckbox.dataset.original === 'true') {
        licenseFields.style.display = 'grid';
    } else {
        licenseFields.style.display = 'none';
    }

    // Trigger change tracking
    const event = new Event('change');
    formInputs[0].dispatchEvent(event);
}

function confirmStatusChange(checkbox) {
    if (checkbox.dataset.original !== (checkbox.checked ? 'true' : 'false')) {
        const action = checkbox.checked ? 'activate' : 'deactivate';
        const message = `Are you sure you want to ${action} this station? This will affect all related operations and user access.`;

        if (!confirm(message)) {
            checkbox.checked = checkbox.dataset.original === 'true';
        }
    }
}

function confirmManagerChange() {
    Swal.fire({
        icon: 'warning',
        title: 'Change Station Manager',
        text: 'Changing the station manager will update access permissions and station assignments. Are you sure you want to proceed?',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--primary))',
        cancelButtonColor: 'hsl(var(--muted-foreground))',
        confirmButtonText: 'Yes, Change Manager'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('manager_user_id').focus();
        }
    });
}

function showChangeHistory() {
    Swal.fire({
        icon: 'info',
        title: 'Change History',
        text: 'Change history functionality will be implemented in the audit trail module.',
        confirmButtonColor: 'hsl(var(--primary))'
    });
}
</script>
@endsection
