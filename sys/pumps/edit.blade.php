@extends('layouts.app')

@section('title', 'Edit Pump ' . $pump->pump_number . ' - ' . $pump->station_name)

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
                        <a href="{{ route('pumps.index', $pump->station_id) }}"
                            class="ml-1 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors md:ml-2">{{
                            $pump->station_name }}</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-muted-foreground" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-muted-foreground md:ml-2">Edit Pump {{
                            $pump->pump_number }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Edit Pump {{ $pump->pump_number }}</h1>
                        <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                            <span>{{ $pump->pump_serial_number }}</span>
                            <span>•</span>
                            <span>{{ $pump->station_name }}</span>
                            <span>•</span>
                            <span>Tank {{ $pump->tank_number }} ({{ $pump->product_name }})</span>
                        </div>
                    </div>
                </div>

                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <div
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Auto-Approved
                </div>
                @endif
            </div>
        </div>

        <!-- Change Summary (Hidden initially, shown when changes detected) -->
        <div id="change-summary" class="card mb-6 border-yellow-200 bg-yellow-50 hidden">
            <div class="p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Unsaved Changes</h4>
                        <p class="text-sm text-yellow-700 mt-1">You have <span id="change-count">0</span> unsaved
                            changes. Review and save to apply modifications.</p>
                    </div>
                </div>
                <div id="changes-list" class="mt-3 space-y-1"></div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('pumps.update', $pump->id) }}" method="POST" id="pump-edit-form" class="space-y-6">
            @csrf

            <!-- Basic Information Section -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Basic Information</h3>
                    <p class="text-sm text-muted-foreground mt-1">Pump identification and manufacturer details</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pump Number (Read-only) -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Pump Number</label>
                            <div class="input bg-muted text-muted-foreground cursor-not-allowed">{{ $pump->pump_number
                                }}</div>
                            <p class="text-xs text-muted-foreground">Pump number cannot be changed after creation</p>
                        </div>

                        <!-- Serial Number (Read-only) -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Serial Number</label>
                            <div class="input bg-muted text-muted-foreground cursor-not-allowed">{{
                                $pump->pump_serial_number }}</div>
                            <p class="text-xs text-muted-foreground">Serial number is permanent for audit purposes</p>
                        </div>

                        <!-- Manufacturer -->
                        <div class="space-y-2">
                            <label for="pump_manufacturer"
                                class="text-sm font-medium text-foreground">Manufacturer</label>
                            <input type="text" id="pump_manufacturer" name="pump_manufacturer" class="input"
                                value="{{ old('pump_manufacturer', $pump->pump_manufacturer) }}" maxlength="255"
                                data-original="{{ $pump->pump_manufacturer }}">
                            @error('pump_manufacturer')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Model -->
                        <div class="space-y-2">
                            <label for="pump_model" class="text-sm font-medium text-foreground">Model</label>
                            <input type="text" id="pump_model" name="pump_model" class="input"
                                value="{{ old('pump_model', $pump->pump_model) }}" maxlength="100"
                                data-original="{{ $pump->pump_model }}">
                            @error('pump_model')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tank Assignment Section -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Tank Assignment</h3>
                    <p class="text-sm text-muted-foreground mt-1">Current tank assignment and available options</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="p-4 bg-muted/50 rounded-lg border border-border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-foreground">Current Assignment</h4>
                                    <p class="text-sm text-muted-foreground">Tank {{ $pump->tank_number }} - {{
                                        $pump->product_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ number_format($pump->tank_capacity) }}L
                                        capacity, {{ $pump->product_type }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-foreground">Active</div>
                                    <div class="text-xs text-muted-foreground">{{
                                        \Carbon\Carbon::parse($pump->installation_date)->format('M j, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        @if($tanks->count() > 1)
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-foreground">Reassign to Different Tank</label>
                            @foreach($tanks as $tank)
                            <label
                                class="flex items-center p-3 border border-border rounded-lg cursor-pointer hover:bg-accent/50 transition-colors">
                                <input type="radio" name="tank_id" value="{{ $tank->id }}" {{ old('tank_id',
                                    $pump->tank_id) == $tank->id ? 'checked' : '' }}
                                class="mr-3 text-primary focus:ring-primary"
                                data-original="{{ $pump->tank_id }}">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-foreground">Tank {{ $tank->tank_number }}</div>
                                            <div class="text-sm text-muted-foreground">{{ $tank->product_name }}</div>
                                            <div class="text-xs text-muted-foreground">{{
                                                number_format($tank->capacity_liters) }}L capacity</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-foreground">{{ $tank->pump_count }}
                                                pumps</div>
                                            <div class="text-xs text-muted-foreground">{{ $tank->product_type }}</div>
                                            @if($tank->id == $pump->tank_id)
                                            <div
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-primary/10 text-primary mt-1">
                                                Current</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <input type="hidden" name="tank_id" value="{{ $pump->tank_id }}">
                        @endif

                        @error('tank_id')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Technical Specifications Section -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Technical Specifications</h3>
                    <p class="text-sm text-muted-foreground mt-1">Meter settings, flow rates, and pump features</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Meter Type (Read-only) -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Meter Type</label>
                            <div class="input bg-muted text-muted-foreground cursor-not-allowed">{{ $pump->meter_type }}
                            </div>
                        </div>

                        <!-- Maximum Reading -->
                        <div class="space-y-2">
                            <label for="meter_maximum_reading" class="text-sm font-medium text-foreground">
                                Maximum Reading (L) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="meter_maximum_reading" name="meter_maximum_reading" class="input"
                                value="{{ old('meter_maximum_reading', $pump->meter_maximum_reading) }}" step="0.001"
                                min="{{ $latestMeterReading ? $latestMeterReading->meter_reading_liters : 0 }}"
                                max="999999999.999" required data-original="{{ $pump->meter_maximum_reading }}">
                            <p class="text-xs text-muted-foreground">Cannot be less than current reading: {{
                                number_format($latestMeterReading ? $latestMeterReading->meter_reading_liters : 0, 3)
                                }}L</p>
                            @error('meter_maximum_reading')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nozzle Count -->
                        <div class="space-y-2">
                            <label for="nozzle_count" class="text-sm font-medium text-foreground">
                                Number of Nozzles <span class="text-destructive">*</span>
                            </label>
                            <select id="nozzle_count" name="nozzle_count" class="input" required
                                data-original="{{ $pump->nozzle_count }}">
                                <option value="1" {{ old('nozzle_count', $pump->nozzle_count) == 1 ? 'selected' : ''
                                    }}>1 Nozzle</option>
                                <option value="2" {{ old('nozzle_count', $pump->nozzle_count) == 2 ? 'selected' : ''
                                    }}>2 Nozzles</option>
                                <option value="3" {{ old('nozzle_count', $pump->nozzle_count) == 3 ? 'selected' : ''
                                    }}>3 Nozzles</option>
                                <option value="4" {{ old('nozzle_count', $pump->nozzle_count) == 4 ? 'selected' : ''
                                    }}>4 Nozzles</option>
                            </select>
                            @error('nozzle_count')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flow Rate Min -->
                        <div class="space-y-2">
                            <label for="flow_rate_min_lpm" class="text-sm font-medium text-foreground">
                                Min Flow Rate (L/min) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="flow_rate_min_lpm" name="flow_rate_min_lpm" class="input"
                                value="{{ old('flow_rate_min_lpm', $pump->flow_rate_min_lpm) }}" step="0.1" min="5"
                                max="100" required data-original="{{ $pump->flow_rate_min_lpm }}">
                            @error('flow_rate_min_lpm')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flow Rate Max -->
                        <div class="space-y-2">
                            <label for="flow_rate_max_lpm" class="text-sm font-medium text-foreground">
                                Max Flow Rate (L/min) <span class="text-destructive">*</span>
                            </label>
                            <input type="number" id="flow_rate_max_lpm" name="flow_rate_max_lpm" class="input"
                                value="{{ old('flow_rate_max_lpm', $pump->flow_rate_max_lpm) }}" step="0.1" min="10"
                                max="150" required data-original="{{ $pump->flow_rate_max_lpm }}">
                            @error('flow_rate_max_lpm')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Features -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Features</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="has_preset_capability" value="1" {{
                                        old('has_preset_capability', $pump->has_preset_capability) ? 'checked' : '' }}
                                    class="rounded border-border text-primary focus:ring-primary mr-2"
                                    data-original="{{ $pump->has_preset_capability ? '1' : '0' }}">
                                    <span class="text-sm text-foreground">Preset capability</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="has_card_reader" value="1" {{ old('has_card_reader',
                                        $pump->has_card_reader) ? 'checked' : '' }}
                                    class="rounded border-border text-primary focus:ring-primary mr-2"
                                    data-original="{{ $pump->has_card_reader ? '1' : '0' }}">
                                    <span class="text-sm text-foreground">Card reader</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance & Calibration Section -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Maintenance & Calibration</h3>
                    <p class="text-sm text-muted-foreground mt-1">Calibration certificates and maintenance scheduling
                    </p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Last Calibration Date -->
                        <div class="space-y-2">
                            <label for="last_calibration_date" class="text-sm font-medium text-foreground">
                                Last Calibration Date <span class="text-destructive">*</span>
                            </label>
                            <input type="date" id="last_calibration_date" name="last_calibration_date" class="input"
                                value="{{ old('last_calibration_date', $pump->last_calibration_date) }}"
                                max="{{ date('Y-m-d') }}" required data-original="{{ $pump->last_calibration_date }}">
                            <p class="text-xs text-muted-foreground">Next calibration: <span
                                    id="next-calibration-date">{{
                                    \Carbon\Carbon::parse($pump->next_calibration_date)->format('M j, Y') }}</span></p>
                            @error('last_calibration_date')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Calibration Certificate -->
                        <div class="space-y-2">
                            <label for="calibration_certificate" class="text-sm font-medium text-foreground">Calibration
                                Certificate</label>
                            <input type="text" id="calibration_certificate" name="calibration_certificate" class="input"
                                value="{{ old('calibration_certificate', $pump->calibration_certificate) }}"
                                maxlength="100" data-original="{{ $pump->calibration_certificate }}">
                            @error('calibration_certificate')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Maintenance Date -->
                        <div class="space-y-2">
                            <label for="last_maintenance_date" class="text-sm font-medium text-foreground">Last
                                Maintenance Date</label>
                            <input type="date" id="last_maintenance_date" name="last_maintenance_date" class="input"
                                value="{{ old('last_maintenance_date', $pump->last_maintenance_date) }}"
                                max="{{ date('Y-m-d') }}" data-original="{{ $pump->last_maintenance_date }}">
                            <p class="text-xs text-muted-foreground">Next maintenance: <span
                                    id="next-maintenance-date">{{
                                    \Carbon\Carbon::parse($pump->next_maintenance_date)->format('M j, Y') }}</span></p>
                            @error('last_maintenance_date')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Installation Date (Read-only) -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Installation Date</label>
                            <div class="input bg-muted text-muted-foreground cursor-not-allowed">{{
                                \Carbon\Carbon::parse($pump->installation_date)->format('M j, Y') }}</div>
                            <p class="text-xs text-muted-foreground">Installation date cannot be modified</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Status Section -->
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Operational Status</h3>
                    <p class="text-sm text-muted-foreground mt-1">Pump status and operational configuration</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Active Status -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-foreground">Pump Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active',
                                        $pump->is_active) ? 'checked' : '' }}
                                    class="rounded border-border text-primary focus:ring-primary mr-2"
                                    data-original="{{ $pump->is_active ? '1' : '0' }}">
                                    <span class="text-sm text-foreground">Active (available for use)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_operational" value="1" id="is_operational" {{
                                        old('is_operational', $pump->is_operational) ? 'checked' : '' }}
                                    class="rounded border-border text-primary focus:ring-primary mr-2"
                                    data-original="{{ $pump->is_operational ? '1' : '0' }}">
                                    <span class="text-sm text-foreground">Operational (currently working)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Out of Order Reason -->
                        <div class="space-y-2" id="out-of-order-section"
                            style="display: {{ old('is_operational', $pump->is_operational) ? 'none' : 'block' }}">
                            <label for="out_of_order_reason" class="text-sm font-medium text-foreground">
                                Out of Order Reason <span class="text-destructive">*</span>
                            </label>
                            <input type="text" id="out_of_order_reason" name="out_of_order_reason" class="input"
                                value="{{ old('out_of_order_reason', $pump->out_of_order_reason) }}" maxlength="255"
                                data-original="{{ $pump->out_of_order_reason }}"
                                placeholder="Describe the issue preventing operation">
                            <p class="text-xs text-muted-foreground">Required when pump is not operational</p>
                            @error('out_of_order_reason')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Hidden inputs for non-managers -->
            <input type="hidden" name="is_active" value="{{ $pump->is_active ? '1' : '0' }}">
            <input type="hidden" name="is_operational" value="{{ $pump->is_operational ? '1' : '0' }}">
            <input type="hidden" name="out_of_order_reason" value="{{ $pump->out_of_order_reason }}">
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('pumps.index', $pump->station_id) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Cancel
                </a>

                <div class="flex items-center space-x-3">
                    <button type="button" id="reset-changes" class="btn btn-outline" style="display: none;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Reset Changes
                    </button>

                    <button type="submit" class="btn btn-primary" id="save-button">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pump-edit-form');
    const changeSummary = document.getElementById('change-summary');
    const changeCount = document.getElementById('change-count');
    const changesList = document.getElementById('changes-list');
    const resetButton = document.getElementById('reset-changes');
    const isOperationalCheckbox = document.getElementById('is_operational');
    const outOfOrderSection = document.getElementById('out-of-order-section');
    const outOfOrderReason = document.getElementById('out_of_order_reason');
    const lastCalibrationDate = document.getElementById('last_calibration_date');
    const nextCalibrationDate = document.getElementById('next-calibration-date');
    const lastMaintenanceDate = document.getElementById('last_maintenance_date');
    const nextMaintenanceDate = document.getElementById('next-maintenance-date');
    const minFlowInput = document.getElementById('flow_rate_min_lpm');
    const maxFlowInput = document.getElementById('flow_rate_max_lpm');

    let changes = {};

    // Track all form inputs
    const inputs = form.querySelectorAll('input[data-original], select[data-original]');

    // Initialize change tracking
    inputs.forEach(input => {
        input.addEventListener('input', trackChanges);
        input.addEventListener('change', trackChanges);
    });

    function trackChanges() {
        changes = {};
        let hasChanges = false;

        inputs.forEach(input => {
            const original = input.dataset.original || '';
            let current = '';

            if (input.type === 'checkbox') {
                current = input.checked ? '1' : '0';
            } else if (input.type === 'radio') {
                if (input.checked) {
                    current = input.value;
                } else {
                    return; // Skip unchecked radio buttons
                }
            } else {
                current = input.value;
            }

            if (current !== original) {
                hasChanges = true;
                changes[input.name || input.id] = {
                    original: original,
                    current: current,
                    label: getFieldLabel(input)
                };

                // Add visual indicator
                input.classList.add('border-yellow-400', 'bg-yellow-50');
                input.classList.remove('border-border');
            } else {
                // Remove visual indicator
                input.classList.remove('border-yellow-400', 'bg-yellow-50');
                input.classList.add('border-border');
            }
        });

        updateChangeSummary(hasChanges);
    }

    function getFieldLabel(input) {
        const label = form.querySelector(`label[for="${input.id}"]`);
        if (label) {
            return label.textContent.replace('*', '').trim();
        }

        // Fallback to field name
        const fieldNames = {
            'pump_manufacturer': 'Manufacturer',
            'pump_model': 'Model',
            'tank_id': 'Tank Assignment',
            'meter_maximum_reading': 'Maximum Reading',
            'nozzle_count': 'Number of Nozzles',
            'flow_rate_min_lpm': 'Min Flow Rate',
            'flow_rate_max_lpm': 'Max Flow Rate',
            'has_preset_capability': 'Preset Capability',
            'has_card_reader': 'Card Reader',
            'last_calibration_date': 'Last Calibration Date',
            'calibration_certificate': 'Calibration Certificate',
            'last_maintenance_date': 'Last Maintenance Date',
            'is_active': 'Active Status',
            'is_operational': 'Operational Status',
            'out_of_order_reason': 'Out of Order Reason'
        };

        return fieldNames[input.name] || input.name;
    }

    function updateChangeSummary(hasChanges) {
        if (hasChanges) {
            const count = Object.keys(changes).length;
            changeCount.textContent = count;

            // Build changes list
            changesList.innerHTML = '';
            Object.entries(changes).forEach(([field, change]) => {
                const changeItem = document.createElement('div');
                changeItem.className = 'text-xs text-yellow-700';
                changeItem.innerHTML = `<strong>${change.label}:</strong> ${formatValue(change.original)} → ${formatValue(change.current)}`;
                changesList.appendChild(changeItem);
            });

            changeSummary.classList.remove('hidden');
            resetButton.style.display = 'inline-flex';
        } else {
            changeSummary.classList.add('hidden');
            resetButton.style.display = 'none';
        }
    }

    function formatValue(value) {
        if (value === '' || value === null || value === undefined) {
            return 'Empty';
        }
        if (value === '1') {
            return 'Yes';
        }
        if (value === '0') {
            return 'No';
        }
        return value;
    }

    // Reset changes
    resetButton.addEventListener('click', function() {
        inputs.forEach(input => {
            const original = input.dataset.original || '';

            if (input.type === 'checkbox') {
                input.checked = original === '1';
            } else if (input.type === 'radio') {
                input.checked = input.value === original;
            } else {
                input.value = original;
            }

            // Remove visual indicators
            input.classList.remove('border-yellow-400', 'bg-yellow-50');
            input.classList.add('border-border');
        });

        changes = {};
        updateChangeSummary(false);

        // Trigger dependent updates
        updateOperationalStatus();
        updateCalibrationDate();
        updateMaintenanceDate();
    });

    // Operational status handling
    if (isOperationalCheckbox && outOfOrderSection) {
        isOperationalCheckbox.addEventListener('change', updateOperationalStatus);

        function updateOperationalStatus() {
            if (isOperationalCheckbox.checked) {
                outOfOrderSection.style.display = 'none';
                outOfOrderReason.removeAttribute('required');
            } else {
                outOfOrderSection.style.display = 'block';
                outOfOrderReason.setAttribute('required', 'required');
            }
        }
    }

    // Calibration date handling
    if (lastCalibrationDate && nextCalibrationDate) {
        lastCalibrationDate.addEventListener('change', updateCalibrationDate);

        function updateCalibrationDate() {
            if (lastCalibrationDate.value) {
                const lastDate = new Date(lastCalibrationDate.value);
                const nextDate = new Date(lastDate);
                nextDate.setFullYear(nextDate.getFullYear() + 1);

                nextCalibrationDate.textContent = nextDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }
    }

    // Maintenance date handling
    if (lastMaintenanceDate && nextMaintenanceDate) {
        lastMaintenanceDate.addEventListener('change', updateMaintenanceDate);

        function updateMaintenanceDate() {
            if (lastMaintenanceDate.value) {
                const lastDate = new Date(lastMaintenanceDate.value);
                const nextDate = new Date(lastDate);
                nextDate.setMonth(nextDate.getMonth() + 6);

                nextMaintenanceDate.textContent = nextDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }
    }

    // Flow rate validation
    function validateFlowRates() {
        const minFlow = parseFloat(minFlowInput.value) || 0;
        const maxFlow = parseFloat(maxFlowInput.value) || 0;

        if (minFlow > 0 && maxFlow > 0 && minFlow >= maxFlow) {
            maxFlowInput.setCustomValidity('Maximum flow rate must be greater than minimum flow rate');
            maxFlowInput.classList.add('border-destructive');
        } else {
            maxFlowInput.setCustomValidity('');
            maxFlowInput.classList.remove('border-destructive');
        }
    }

    if (minFlowInput && maxFlowInput) {
        minFlowInput.addEventListener('input', validateFlowRates);
        maxFlowInput.addEventListener('input', validateFlowRates);
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        validateFlowRates();

        if (!form.checkValidity()) {
            e.preventDefault();

            Swal.fire({
                title: 'Validation Error',
                text: 'Please correct the errors in the form before submitting.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else if (Object.keys(changes).length === 0) {
            e.preventDefault();

            Swal.fire({
                title: 'No Changes',
                text: 'No changes were made to save.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        } else {
            // Show loading state
            const submitButton = document.getElementById('save-button');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';

            // Reset button after 5 seconds in case of error
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 5000);
        }
    });

    // Initial state
    updateOperationalStatus();
    trackChanges();
});
</script>
@endpush
@endsection
