@extends('layouts.app')

@section('title', 'Add New Pump - ' . $station->station_name)

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Navigation Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('pumps.select') }}"
                        class="inline-flex items-center text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                            </path>
                        </svg>
                        Pump Management
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-muted-foreground" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('pumps.index', $station->id) }}"
                            class="ml-1 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors md:ml-2">{{
                            $station->station_name }}</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-muted-foreground" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-muted-foreground md:ml-2">Add Pump</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Add New Pump</h1>
                    <p class="text-muted-foreground">Configure a new pump for {{ $station->station_name }}</p>
                </div>
            </div>
        </div>

        <!-- Horizontal Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-8">
                <div class="step-indicator active" id="step-1-indicator">
                    <div class="step-circle">1</div>
                    <span class="step-label">Basic Details</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-indicator" id="step-2-indicator">
                    <div class="step-circle">2</div>
                    <span class="step-label">Tank Assignment</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-indicator" id="step-3-indicator">
                    <div class="step-circle">3</div>
                    <span class="step-label">Technical Specs</span>
                </div>
                <div class="step-connector"></div>
                <div class="step-indicator" id="step-4-indicator">
                    <div class="step-circle">4</div>
                    <span class="step-label">Installation</span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('pumps.store', $station->id) }}" method="POST" id="pump-form">
            @csrf

            <!-- Step 1: Basic Details -->
            <div class="card wizard-step active" id="step-1">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Basic Pump Information</h3>
                    <p class="text-sm text-muted-foreground mt-1">Enter the pump identification and manufacturer details
                    </p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="pump_number" class="text-sm font-medium text-foreground">
                                Pump Number <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="pump_number" name="pump_number" class="input"
                                value="{{ old('pump_number', $nextPumpNumber) }}" min="1" max="99" required>
                            <p class="text-xs text-muted-foreground">Sequential number for this pump at the station</p>
                            @error('pump_number')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="pump_serial_number" class="text-sm font-medium text-foreground">
                                Serial Number <span class="text-destructive">*</span>
                            </label>
                            <input type="text" id="pump_serial_number" name="pump_serial_number" class="input"
                                value="{{ old('pump_serial_number') }}" maxlength="100" required
                                placeholder="e.g., GB-2024-001">
                            <p class="text-xs text-muted-foreground">Manufacturer's unique serial number</p>
                            @error('pump_serial_number')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="pump_manufacturer"
                                class="text-sm font-medium text-foreground">Manufacturer</label>
                            <select id="pump_manufacturer" name="pump_manufacturer" class="input">
                                <option value="">Select manufacturer</option>
                                <option value="Gilbarco" {{ old('pump_manufacturer')=='Gilbarco' ? 'selected' : '' }}>
                                    Gilbarco</option>
                                <option value="Wayne" {{ old('pump_manufacturer')=='Wayne' ? 'selected' : '' }}>Wayne
                                </option>
                                <option value="Tokheim" {{ old('pump_manufacturer')=='Tokheim' ? 'selected' : '' }}>
                                    Tokheim</option>
                                <option value="Bennett" {{ old('pump_manufacturer')=='Bennett' ? 'selected' : '' }}>
                                    Bennett</option>
                                <option value="Other" {{ old('pump_manufacturer')=='Other' ? 'selected' : '' }}>Other
                                </option>
                            </select>
                            @error('pump_manufacturer')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="pump_model" class="text-sm font-medium text-foreground">Model</label>
                            <input type="text" id="pump_model" name="pump_model" class="input"
                                value="{{ old('pump_model') }}" maxlength="100" placeholder="e.g., Encore 700S">
                            <p class="text-xs text-muted-foreground">Model number or name</p>
                            @error('pump_model')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Tank Assignment -->
            <div class="card wizard-step" id="step-2">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Tank Assignment</h3>
                    <p class="text-sm text-muted-foreground mt-1">Select which tank this pump will dispense from</p>
                </div>
                <div class="p-6">
                    @if($tanks->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($tanks as $tank)
                        <label class="tank-selector group cursor-pointer">
                            <input type="radio" name="tank_id" value="{{ $tank->id }}" {{ old('tank_id')==$tank->id ?
                            'checked' : '' }}
                            required class="sr-only">
                            <div
                                class="tank-card p-4 border-2 border-border rounded-lg bg-card transition-all duration-200 group-hover:border-primary/50 group-hover:shadow-md">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                    </path>
                                                </svg>
                                            </div>
                                            <h4 class="font-semibold text-foreground">Tank {{ $tank->tank_number }}</h4>
                                        </div>
                                        <p class="text-sm text-muted-foreground mt-1">{{ $tank->product_name }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs bg-muted px-2 py-1 rounded">{{ $tank->product_type
                                                }}</span>
                                            <span class="text-xs text-muted-foreground">{{
                                                number_format($tank->capacity_liters) }}L</span>
                                        </div>
                                    </div>
                                    <div class="tank-check-indicator opacity-0 text-primary">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-border">
                                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                                        <span>{{ $tank->pump_count }} existing pumps</span>
                                        <span>Active tank</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('tank_id')
                    <p class="text-xs text-destructive mt-2">{{ $message }}</p>
                    @enderror
                    @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-foreground mb-2">No Tanks Available</h3>
                        <p class="text-muted-foreground">You need to set up tanks before adding pumps to this station.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Step 3: Technical Specifications -->
            <div class="card wizard-step" id="step-3">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Technical Specifications</h3>
                    <p class="text-sm text-muted-foreground mt-1">Configure meter settings, flow rates, and pump
                        features</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label for="meter_type" class="text-sm font-medium text-foreground">
                                Meter Type <span class="text-destructive">*</span>
                            </label>
                            <select id="meter_type" name="meter_type" class="input" required>
                                <option value="">Select meter type</option>
                                <option value="MECHANICAL" {{ old('meter_type')=='MECHANICAL' ? 'selected' : '' }}>
                                    Mechanical</option>
                                <option value="ELECTRONIC" {{ old('meter_type', 'ELECTRONIC' )=='ELECTRONIC'
                                    ? 'selected' : '' }}>Electronic</option>
                                <option value="DIGITAL" {{ old('meter_type')=='DIGITAL' ? 'selected' : '' }}>Digital
                                </option>
                            </select>
                            @error('meter_type')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="meter_maximum_reading" class="text-sm font-medium text-foreground">
                                Maximum Reading (L) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="meter_maximum_reading" name="meter_maximum_reading" class="input"
                                value="{{ old('meter_maximum_reading', 999999999.999) }}" step="0.001" min="10000"
                                max="999999999.999" required>
                            <p class="text-xs text-muted-foreground">Maximum meter reading before reset</p>
                            @error('meter_maximum_reading')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="nozzle_count" class="text-sm font-medium text-foreground">
                                Number of Nozzles <span class="text-destructive">*</span>
                            </label>
                            <select id="nozzle_count" name="nozzle_count" class="input" required>
                                <option value="">Select count</option>
                                <option value="1" {{ old('nozzle_count')=='1' ? 'selected' : '' }}>1 Nozzle</option>
                                <option value="2" {{ old('nozzle_count', '2' )=='2' ? 'selected' : '' }}>2 Nozzles
                                </option>
                                <option value="3" {{ old('nozzle_count')=='3' ? 'selected' : '' }}>3 Nozzles</option>
                                <option value="4" {{ old('nozzle_count')=='4' ? 'selected' : '' }}>4 Nozzles</option>
                            </select>
                            @error('nozzle_count')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="flow_rate_min_lpm" class="text-sm font-medium text-foreground">
                                Min Flow Rate (L/min) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="flow_rate_min_lpm" name="flow_rate_min_lpm" class="input"
                                value="{{ old('flow_rate_min_lpm', 8) }}" step="0.1" min="1" max="100" required>
                            <p class="text-xs text-muted-foreground">International standard: 8-12 L/min</p>
                            @error('flow_rate_min_lpm')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="flow_rate_max_lpm" class="text-sm font-medium text-foreground">
                                Max Flow Rate (L/min) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="flow_rate_max_lpm" name="flow_rate_max_lpm" class="input"
                                value="{{ old('flow_rate_max_lpm', 45) }}" step="0.1" min="10" max="150" required>
                            <p class="text-xs text-muted-foreground">International standard: 38-50 L/min</p>
                            @error('flow_rate_max_lpm')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Additional Features</label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="has_preset_capability" value="1" {{
                                        old('has_preset_capability') ? 'checked' : '' }}
                                        class="rounded border-border text-primary focus:ring-primary mr-3">
                                    <span class="text-sm text-foreground">Preset capability</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="has_card_reader" value="1" {{ old('has_card_reader')
                                        ? 'checked' : '' }}
                                        class="rounded border-border text-primary focus:ring-primary mr-3">
                                    <span class="text-sm text-foreground">Card reader</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Installation & Initial Reading -->
            <div class="card wizard-step" id="step-4">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Installation & Initial Reading</h3>
                    <p class="text-sm text-muted-foreground mt-1">Set installation date and initial meter reading</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label for="installation_date" class="text-sm font-medium text-foreground">
                                    Installation Date <span class="text-destructive">*</span>
                                </label>
                                <input type="date" id="installation_date" name="installation_date" class="input"
                                    value="{{ old('installation_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                                    required>
                                @error('installation_date')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="calibration_certificate"
                                    class="text-sm font-medium text-foreground">Calibration Certificate</label>
                                <input type="text" id="calibration_certificate" name="calibration_certificate"
                                    class="input" value="{{ old('calibration_certificate') }}" maxlength="100"
                                    placeholder="e.g., CAL-2024-001">
                                <p class="text-xs text-muted-foreground">Certificate number from calibration authority
                                </p>
                                @error('calibration_certificate')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="initial_meter_reading" class="text-sm font-medium text-foreground">
                                    Initial Reading (L) <span class="text-destructive">*</span>
                                </label>
                                <input type="number" id="initial_meter_reading" name="initial_meter_reading"
                                    class="input" value="{{ old('initial_meter_reading', 0) }}" step="0.001" min="0"
                                    required>
                                <p class="text-xs text-muted-foreground">Starting meter reading (usually 0 for new
                                    pumps)</p>
                                @error('initial_meter_reading')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-muted/50 p-6 rounded-lg">
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 text-primary mt-1 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground mb-2">Setup Information</h4>
                                    <div class="space-y-2 text-xs text-muted-foreground">
                                        <p>• This reading will be recorded as the first entry in the meter readings
                                            table</p>
                                        <p>• Calibration schedule will start from the installation date</p>
                                        <p>• Next calibration will be automatically scheduled for 1 year from
                                            installation</p>
                                        <p>• Maintenance schedule will begin with 6-month intervals</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between mt-8">
            <button type="button" id="prev-btn" class="btn btn-outline hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Previous
            </button>

            <a href="{{ route('pumps.index', $station->id) }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                Cancel
            </a>

            <button type="button" id="next-btn" class="btn btn-primary">
                Next
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                    </path>
                </svg>
            </button>

            <button type="submit" id="submit-btn" form="pump-form" class="btn btn-primary hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Create Pump
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .step-indicator.active,
    .step-indicator.completed {
        opacity: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: hsl(var(--muted));
        color: hsl(var(--muted-foreground));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .step-indicator.active .step-circle {
        background-color: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
        box-shadow: 0 0 0 4px hsl(var(--primary) / 0.1);
    }

    .step-indicator.completed .step-circle {
        background-color: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
    }

    .step-label {
        font-size: 12px;
        font-weight: 500;
        color: hsl(var(--muted-foreground));
    }

    .step-indicator.active .step-label,
    .step-indicator.completed .step-label {
        color: hsl(var(--foreground));
    }

    .step-connector {
        width: 60px;
        height: 2px;
        background-color: hsl(var(--border));
        margin-top: 20px;
    }

    .step-indicator.completed+.step-connector {
        background-color: hsl(var(--primary));
    }

    .wizard-step {
        display: none;
    }

    .wizard-step.active {
        display: block;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tank-selector input:checked+.tank-card {
        border-color: hsl(var(--primary));
        background-color: hsl(var(--primary) / 0.05);
        box-shadow: 0 0 0 1px hsl(var(--primary) / 0.1);
    }

    .tank-selector input:checked+.tank-card .tank-check-indicator {
        opacity: 1;
    }

    .input:focus {
        outline: none;
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 3px hsl(var(--ring) / 0.1);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        border-radius: calc(var(--radius));
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        outline: none;
        border: 1px solid transparent;
        cursor: pointer;
        height: 40px;
        padding: 0 16px;
        text-decoration: none;
    }

    .btn-primary {
        background-color: hsl(var(--primary));
        color: hsl(var(--primary-foreground));
    }

    .btn-primary:hover {
        background-color: hsl(var(--primary) / 0.9);
    }

    .btn-outline {
        background-color: transparent;
        color: hsl(var(--foreground));
        border-color: hsl(var(--border));
    }

    .btn-outline:hover {
        background-color: hsl(var(--accent));
        color: hsl(var(--accent-foreground));
    }

    .btn-secondary {
        background-color: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border-color: hsl(var(--border));
    }

    .btn-secondary:hover {
        background-color: hsl(var(--secondary) / 0.8);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;

    const form = document.getElementById('pump-form');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');

    const flowRateStandards = {
        'PETROL_95': { min: 8, max: 45 },
        'PETROL_98': { min: 8, max: 45 },
        'DIESEL': { min: 12, max: 50 },
        'KEROSENE': { min: 6, max: 35 }
    };

    function updateStepIndicators() {
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            const step = document.getElementById(`step-${i}`);

            if (i < currentStep) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
                step.classList.remove('active');
            } else if (i === currentStep) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
                step.classList.add('active');
            } else {
                indicator.classList.remove('active', 'completed');
                step.classList.remove('active');
            }
        }
    }

    function updateNavigationButtons() {
        if (currentStep === 1) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }

        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step-${currentStep}`);
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('border-destructive');

                field.addEventListener('input', function() {
                    this.classList.remove('border-destructive');
                }, { once: true });
            } else {
                field.classList.remove('border-destructive');
            }
        });

        if (currentStep === 3) {
            const minFlow = parseFloat(document.getElementById('flow_rate_min_lpm').value);
            const maxFlow = parseFloat(document.getElementById('flow_rate_max_lpm').value);

            if (minFlow >= maxFlow) {
                isValid = false;
                document.getElementById('flow_rate_max_lpm').classList.add('border-destructive');

                Swal.fire({
                    title: 'Invalid Flow Rates',
                    text: 'Maximum flow rate must be greater than minimum flow rate.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        return isValid;
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        currentStep = step;
        updateStepIndicators();
        updateNavigationButtons();

        document.getElementById(`step-${currentStep}`).scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    prevBtn.addEventListener('click', function() {
        goToStep(currentStep - 1);
    });

    nextBtn.addEventListener('click', function() {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });

    const tankRadios = document.querySelectorAll('input[name="tank_id"]');
    tankRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const tankCard = this.closest('.tank-selector');
                const productType = tankCard.querySelector('.text-xs.bg-muted').textContent.trim();

                if (flowRateStandards[productType]) {
                    document.getElementById('flow_rate_min_lpm').value = flowRateStandards[productType].min;
                    document.getElementById('flow_rate_max_lpm').value = flowRateStandards[productType].max;
                }
            }
        });
    });

    form.addEventListener('submit', function(e) {
        if (!validateCurrentStep()) {
            e.preventDefault();
            return;
        }

        const submitButton = document.getElementById('submit-btn');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...';

        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 5000);
    });

    updateStepIndicators();
    updateNavigationButtons();

    document.getElementById('pump_number').focus();
});
</script>
@endpush
@endsection
