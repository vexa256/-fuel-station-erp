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
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Create New Station</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Add a new fuel station to the system
                    </p>
                </div>
                <div class="ml-auto">
                    <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-md font-medium">
                        {{ auth()->user()->role }} Access
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container mx-auto px-4 py-8">
        <form method="POST" action="{{ route('stations.store') }}" id="stationForm" class="max-w-4xl mx-auto">
            @csrf

            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-primary font-medium">Step 1: Basic Information</span>
                    <span class="text-primary font-medium">Step 2: Location Details</span>
                    <span class="text-primary font-medium">Step 3: Operations Setup</span>
                </div>
                <div class="mt-2 w-full bg-muted rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 33%"
                        id="progressBar"></div>
                </div>
            </div>

            <!-- Section 1: Basic Information -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm" id="section1">
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
                    <!-- Station Code -->
                    <div>
                        <label for="station_code" class="block text-sm font-medium text-foreground mb-2">
                            Station Code <span class="text-destructive">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="station_code" name="station_code" value="{{ old('station_code') }}"
                                placeholder="e.g., KLA001"
                                class="input w-full uppercase transition-all focus:ring-2 focus:ring-primary/20 @error('station_code') border-destructive @enderror"
                                required maxlength="50" autocomplete="off">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <div id="codeValidation" class="hidden">
                                    <i class="fas fa-check text-green-500 text-sm" id="codeValid"
                                        style="display: none;"></i>
                                    <i class="fas fa-times text-destructive text-sm" id="codeInvalid"
                                        style="display: none;"></i>
                                    <i class="fas fa-spinner fa-spin text-muted-foreground text-sm" id="codeChecking"
                                        style="display: none;"></i>
                                </div>
                            </div>
                        </div>
                        @error('station_code')
                        <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-muted-foreground mt-1">Unique identifier for the station</p>
                    </div>

                    <!-- Station Name -->
                    <div>
                        <label for="station_name" class="block text-sm font-medium text-foreground mb-2">
                            Station Name <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="station_name" name="station_name" value="{{ old('station_name') }}"
                            placeholder="e.g., Kampala Central Station"
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
                        <input type="text" id="region" name="region" value="{{ old('region') }}"
                            placeholder="e.g., Central Region"
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
                        <input type="text" id="district" name="district" value="{{ old('district') }}"
                            placeholder="e.g., Kampala"
                            class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('district') border-destructive @enderror"
                            required maxlength="100">
                        @error('district')
                        <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="nextSection(1)"
                        class="btn btn-primary inline-flex items-center gap-2">
                        <span>Continue to Location</span>
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Section 2: Location Details -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm opacity-50" id="section2">
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
                                value="{{ old('address_line_1') }}" placeholder="Street address"
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
                                value="{{ old('address_line_2') }}" placeholder="Apartment, suite, etc. (optional)"
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
                            <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="City name"
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
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                placeholder="Postal code (optional)"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('postal_code') border-destructive @enderror"
                                maxlength="20">
                            @error('postal_code')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Auto-Location Detection -->
                    <div class="bg-muted/30 border border-border rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-location-crosshairs text-primary text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-foreground">Auto-Detect Location</h4>
                                    <p class="text-xs text-muted-foreground">Automatically fill coordinates from address
                                        or current location</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="detectCurrentLocation()"
                                    class="btn btn-ghost text-xs px-3 py-1 h-8" id="currentLocationBtn">
                                    <i class="fas fa-crosshairs text-sm mr-1"></i>
                                    Current
                                </button>
                                <button type="button" onclick="geocodeAddress()"
                                    class="btn btn-ghost text-xs px-3 py-1 h-8" id="geocodeBtn">
                                    <i class="fas fa-search text-sm mr-1"></i>
                                    From Address
                                </button>
                            </div>
                        </div>
                        <div id="locationStatus" class="text-xs text-muted-foreground" style="display: none;"></div>
                    </div>

                    <!-- Coordinates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-foreground mb-2">
                                Latitude
                                <span id="latitudeStatus" class="text-xs text-green-600 ml-2" style="display: none;">
                                    <i class="fas fa-check-circle"></i> Auto-detected
                                </span>
                            </label>
                            <input type="number" id="latitude" name="latitude" value="{{ old('latitude') }}"
                                placeholder="e.g., 0.3476" step="any" min="-90" max="90"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('latitude') border-destructive @enderror">
                            @error('latitude')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground mt-1">Range: -90 to 90</p>
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-foreground mb-2">
                                Longitude
                                <span id="longitudeStatus" class="text-xs text-green-600 ml-2" style="display: none;">
                                    <i class="fas fa-check-circle"></i> Auto-detected
                                </span>
                            </label>
                            <input type="number" id="longitude" name="longitude" value="{{ old('longitude') }}"
                                placeholder="e.g., 32.5825" step="any" min="-180" max="180"
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
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                placeholder="e.g., +256 700 000 000"
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
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="station@example.com"
                                class="input w-full transition-all focus:ring-2 focus:ring-primary/20 @error('email') border-destructive @enderror"
                                maxlength="255">
                            @error('email')
                            <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevSection(2)" class="btn btn-ghost inline-flex items-center gap-2">
                        <i class="fas fa-arrow-left text-sm"></i>
                        <span>Back to Basic Info</span>
                    </button>
                    <button type="button" onclick="nextSection(2)"
                        class="btn btn-primary inline-flex items-center gap-2">
                        <span>Continue to Operations</span>
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Section 3: Operations Setup -->
            <div class="card bg-card border border-border rounded-lg p-8 mb-6 shadow-sm opacity-50" id="section3">
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
                                    value="{{ old('operating_hours_open', '06:00') }}"
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
                                    value="{{ old('operating_hours_close', '22:00') }}"
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
                        <div>
                            <label for="manager_user_id" class="block text-sm font-medium text-foreground mb-2">
                                Station Manager
                            </label>
                            <select id="manager_user_id" name="manager_user_id"
                                class="select w-full md:w-1/2 transition-all focus:ring-2 focus:ring-primary/20 @error('manager_user_id') border-destructive @enderror">
                                <option value="">Select a manager (optional)</option>
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
                            <p class="text-xs text-muted-foreground mt-1">Manager can be assigned later if needed</p>
                        </div>
                    </div>

                    <!-- Licensing Information -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <input type="checkbox" id="has_license"
                                class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-2">
                            <label for="has_license" class="text-lg font-medium text-foreground">
                                Add Licensing Information
                            </label>
                        </div>

                        <div id="licenseFields" class="grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                            <div>
                                <label for="license_number" class="block text-sm font-medium text-foreground mb-2">
                                    License Number
                                </label>
                                <input type="text" id="license_number" name="license_number"
                                    value="{{ old('license_number') }}" placeholder="License number"
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
                                    value="{{ old('license_expiry_date') }}"
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

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevSection(3)" class="btn btn-ghost inline-flex items-center gap-2">
                        <i class="fas fa-arrow-left text-sm"></i>
                        <span>Back to Location</span>
                    </button>
                    <button type="submit" class="btn btn-primary inline-flex items-center gap-2" id="submitBtn">
                        <i class="fas fa-save text-sm"></i>
                        <span>Create Station</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for Form Progression, Validation, and Auto-Location Detection -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    let currentSection = 1;

    // Auto-uppercase station code
    const stationCodeInput = document.getElementById('station_code');
    stationCodeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        checkStationCodeAvailability();
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
    });

    // Operating hours validation
    const openTime = document.getElementById('operating_hours_open');
    const closeTime = document.getElementById('operating_hours_close');

    function validateOperatingHours() {
        if (openTime.value && closeTime.value) {
            if (closeTime.value <= openTime.value) {
                closeTime.setCustomValidity('Closing time must be after opening time');
            } else {
                closeTime.setCustomValidity('');
            }
        }
    }

    openTime.addEventListener('change', validateOperatingHours);
    closeTime.addEventListener('change', validateOperatingHours);

    // Auto-detect location from address when moving to section 2
    const addressFields = ['address_line_1', 'city', 'district', 'region'];
    addressFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', function() {
                autoDetectLocationFromAddress();
            });
        }
    });

    // Form submission with loading state
    const form = document.getElementById('stationForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm mr-2"></i>Creating Station...';
    });

    // Auto-save functionality
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            saveFormData();
        });
    });

    // Load saved form data
    loadFormData();
});

function nextSection(current) {
    if (validateSection(current)) {
        const currentEl = document.getElementById(`section${current}`);
        const nextEl = document.getElementById(`section${current + 1}`);

        currentEl.style.opacity = '0.5';
        currentEl.style.pointerEvents = 'none';

        nextEl.style.opacity = '1';
        nextEl.style.pointerEvents = 'auto';

        // Update progress bar
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = `${((current + 1) / 3) * 100}%`;

        // Auto-detect location when moving to section 2
        if (current === 1) {
            setTimeout(() => {
                autoDetectLocationFromAddress();
            }, 500);
        }

        // Scroll to next section
        nextEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function prevSection(current) {
    const currentEl = document.getElementById(`section${current}`);
    const prevEl = document.getElementById(`section${current - 1}`);

    currentEl.style.opacity = '0.5';
    currentEl.style.pointerEvents = 'none';

    prevEl.style.opacity = '1';
    prevEl.style.pointerEvents = 'auto';

    // Update progress bar
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = `${((current - 1) / 3) * 100}%`;

    // Scroll to previous section
    prevEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateSection(section) {
    const sectionEl = document.getElementById(`section${section}`);
    const requiredFields = sectionEl.querySelectorAll('input[required], select[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-destructive');
            isValid = false;
        } else {
            field.classList.remove('border-destructive');
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Fields Missing',
            text: 'Please fill in all required fields before continuing.',
            confirmButtonColor: 'hsl(var(--primary))'
        });
    }

    return isValid;
}

function checkStationCodeAvailability() {
    const code = document.getElementById('station_code').value;
    const validation = document.getElementById('codeValidation');
    const valid = document.getElementById('codeValid');
    const invalid = document.getElementById('codeInvalid');
    const checking = document.getElementById('codeChecking');

    if (code.length < 3) {
        validation.classList.add('hidden');
        return;
    }

    validation.classList.remove('hidden');
    valid.style.display = 'none';
    invalid.style.display = 'none';
    checking.style.display = 'inline';

    // Simulate API call for code checking
    setTimeout(() => {
        checking.style.display = 'none';
        // This would be replaced with actual API call
        const isAvailable = !['KLA001', 'MBR001', 'JNJ001'].includes(code);

        if (isAvailable) {
            valid.style.display = 'inline';
            invalid.style.display = 'none';
        } else {
            valid.style.display = 'none';
            invalid.style.display = 'inline';
        }
    }, 1000);
}

// Auto-detect current location using browser geolocation
function detectCurrentLocation() {
    const btn = document.getElementById('currentLocationBtn');
    const status = document.getElementById('locationStatus');

    if (!navigator.geolocation) {
        showLocationStatus('Geolocation is not supported by this browser.', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm mr-1"></i>Detecting...';
    showLocationStatus('Detecting your current location...', 'info');

    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
    };

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude.toFixed(6);
            const lng = position.coords.longitude.toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            showLocationStatus(`Location detected: ${lat}, ${lng}`, 'success');
            showCoordinateStatus(true);

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-crosshairs text-sm mr-1"></i>Current';

            saveFormData();
        },
        function(error) {
            let errorMessage = 'Unable to detect location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Location access denied by user.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Location information unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Location request timed out.';
                    break;
                default:
                    errorMessage += 'An unknown error occurred.';
                    break;
            }

            showLocationStatus(errorMessage, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-crosshairs text-sm mr-1"></i>Current';
        },
        options
    );
}

// Geocode address to get coordinates
function geocodeAddress() {
    const btn = document.getElementById('geocodeBtn');
    const address = buildAddressString();

    if (!address.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Address',
            text: 'Please fill in the address fields first.',
            confirmButtonColor: 'hsl(var(--primary))'
        });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm mr-1"></i>Searching...';
    showLocationStatus('Searching for address coordinates...', 'info');

    // Using OpenStreetMap Nominatim API (free alternative to Google Geocoding)
    const encodedAddress = encodeURIComponent(address);
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}&limit=1&countrycodes=ug`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat).toFixed(6);
                const lng = parseFloat(data[0].lon).toFixed(6);

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                showLocationStatus(`Coordinates found: ${lat}, ${lng}`, 'success');
                showCoordinateStatus(true);
                saveFormData();
            } else {
                showLocationStatus('Address not found. Please check the address or enter coordinates manually.', 'warning');
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            showLocationStatus('Error finding address coordinates. Please try again or enter manually.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search text-sm mr-1"></i>From Address';
        });
}

// Auto-detect location when address fields are filled
function autoDetectLocationFromAddress() {
    const latField = document.getElementById('latitude');
    const lngField = document.getElementById('longitude');

    // Only auto-detect if coordinates are empty
    if (latField.value || lngField.value) {
        return;
    }

    const address = buildAddressString();
    if (!address.trim() || address.split(',').length < 2) {
        return;
    }

    // Auto-geocode with minimal UI feedback
    const encodedAddress = encodeURIComponent(address);
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}&limit=1&countrycodes=ug`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat).toFixed(6);
                const lng = parseFloat(data[0].lon).toFixed(6);

                latField.value = lat;
                lngField.value = lng;

                showCoordinateStatus(true);
                showLocationStatus(`Auto-detected from address: ${lat}, ${lng}`, 'success');
                saveFormData();
            }
        })
        .catch(error => {
            console.error('Auto-geocoding error:', error);
        });
}

function buildAddressString() {
    const parts = [
        document.getElementById('address_line_1')?.value,
        document.getElementById('address_line_2')?.value,
        document.getElementById('city')?.value,
        document.getElementById('district')?.value,
        document.getElementById('region')?.value,
        'Uganda'
    ].filter(part => part && part.trim());

    return parts.join(', ');
}

function showLocationStatus(message, type) {
    const status = document.getElementById('locationStatus');
    status.style.display = 'block';
    status.className = `text-xs mt-2 ${type === 'error' ? 'text-destructive' : type === 'success' ? 'text-green-600' : type === 'warning' ? 'text-yellow-600' : 'text-muted-foreground'}`;
    status.textContent = message;

    // Auto-hide after 5 seconds for success/info messages
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            status.style.display = 'none';
        }, 5000);
    }
}

function showCoordinateStatus(show) {
    const latStatus = document.getElementById('latitudeStatus');
    const lngStatus = document.getElementById('longitudeStatus');

    if (show) {
        latStatus.style.display = 'inline';
        lngStatus.style.display = 'inline';
    } else {
        latStatus.style.display = 'none';
        lngStatus.style.display = 'none';
    }
}

function saveFormData() {
    const formData = new FormData(document.getElementById('stationForm'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    localStorage.setItem('stationFormData', JSON.stringify(data));
}

function loadFormData() {
    const savedData = localStorage.getItem('stationFormData');
    if (savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = data[key];

                // Show coordinate status if coordinates were loaded
                if ((key === 'latitude' || key === 'longitude') && data[key]) {
                    showCoordinateStatus(true);
                }
            }
        });
    }
}
</script>
@endsection
