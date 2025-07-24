@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100" x-data="meterReadingForm()" x-init="init()">
    <!-- Header Section -->
    <div class="border-b border-slate-200 bg-white/80 backdrop-blur-sm sticky top-0 z-10">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-900">Create Meter Reading</h1>
                        <p class="text-sm text-slate-600">{{ $currentShift }} Shift - Enter precise meter readings</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($isAutoApproved)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-crown mr-1"></i>Auto-Approved
                        </span>
                    @endif
                    @if($automationStatus['ready'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-check-circle mr-1"></i>Automation Ready
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>System Alert
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Time Validation Alert -->
    @if(!$timeValidation['valid'] && !$isAutoApproved)
        <div class="px-6 pt-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Time Window Restriction</h4>
                        <p class="text-sm text-red-700 mt-1">{{ $timeValidation['message'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Automation Status Alert -->
    @if(!$automationStatus['ready'])
        <div class="px-6 pt-6">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-cogs text-amber-400 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-amber-800">Automation Status</h4>
                        <p class="text-sm text-amber-700 mt-1">{{ $automationStatus['reason'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Form Container -->
    <div class="px-6 pt-6 pb-8">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Step Progress -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between">
                    <div class="flex space-x-8">
                        <div class="flex items-center">
                            <div :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
                                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200">
                                <span x-show="currentStep > 1">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span x-show="currentStep <= 1">1</span>
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-900">Select Pump</span>
                        </div>
                        <div class="flex items-center">
                            <div :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
                                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200">
                                <span x-show="currentStep > 2">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span x-show="currentStep <= 2">2</span>
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-900">Enter Reading</span>
                        </div>
                        <div class="flex items-center">
                            <div :class="currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
                                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200">
                                <span x-show="currentStep > 3">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span x-show="currentStep <= 3">3</span>
                            </div>
                            <span class="ml-2 text-sm font-medium text-slate-900">Confirm</span>
                        </div>
                    </div>
                    <div class="text-sm text-slate-600">
                        Step <span x-text="currentStep"></span> of 3
                    </div>
                </div>
            </div>

            <!-- Form Steps -->
            <div class="p-6">
                <!-- Step 1: Select Pump -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Select Pump</h3>
                        <p class="text-sm text-slate-600">Choose the pump for meter reading entry</p>
                    </div>

                    @if(count($availablePumps) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($availablePumps as $pump)
                                @php
                                    // Get latest meter reading from readings table
                                    $latestMeterReading = DB::table('readings')
                                        ->select([
                                            'meter_reading_liters',
                                            'reading_date',
                                            'reading_shift',
                                            'reading_time',
                                            'created_at',
                                            'entered_by'
                                        ])
                                        ->where('pump_id', $pump->pump_id)
                                        ->whereNotNull('meter_reading_liters')
                                        ->orderBy('reading_date', 'desc')
                                        ->orderBy('reading_time', 'desc')
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                                    // Get user who entered the reading
                                    $enteredByUser = null;
                                    if ($latestMeterReading && $latestMeterReading->entered_by) {
                                        $enteredByUser = DB::table('users')
                                            ->select(['first_name', 'last_name', 'employee_number'])
                                            ->where('id', $latestMeterReading->entered_by)
                                            ->first();
                                    }
                                @endphp
                                <div @click="selectPump({{ json_encode($pump) }})"
                                     :class="selectedPump && selectedPump.pump_id === {{ $pump->pump_id }} ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-200' : 'hover:bg-slate-50 border-slate-200'"
                                     class="border rounded-lg p-4 cursor-pointer transition-all duration-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                                        @if($pump->fifo_ready) bg-emerald-100 @else bg-yellow-100 @endif">
                                                <i class="fas fa-gas-pump text-sm
                                                   @if($pump->fifo_ready) text-emerald-600 @else text-yellow-600 @endif"></i>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-900">{{ $pump->station_name }}</div>
                                                <div class="text-sm text-slate-600">Pump {{ $pump->pump_number }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($pump->requires_morning_baseline)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Baseline Required
                                                </span>
                                            @endif
                                            @php
                                                // Get last meter reading for this specific pump in the card
                                                $pumpLastReading = DB::table('meter_readings')
                                                    ->where('pump_id', $pump->pump_id)
                                                    ->orderBy('reading_date', 'desc')
                                                    ->orderBy('reading_shift', 'desc')
                                                    ->first();
                                            @endphp
                                            @if($pumpLastReading)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    <i class="fas fa-shield-alt mr-1"></i>Protected
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-star mr-1"></i>First Reading
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Product:</span>
                                            <span class="font-medium text-slate-900">{{ $pump->product_name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Tank:</span>
                                            <span class="font-medium text-slate-900">{{ $pump->tank_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Serial:</span>
                                            <span class="font-medium text-slate-900">{{ $pump->pump_serial_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">FIFO Status:</span>
                                            <span class="font-medium @if($pump->fifo_ready) text-emerald-600 @else text-yellow-600 @endif">
                                                @if($pump->fifo_ready) Ready @else Pending @endif
                                            </span>
                                        </div>
                                    </div>

                                    @php
                                        // Get last meter reading for card display
                                        $cardLastReading = DB::table('meter_readings')
                                            ->where('pump_id', $pump->pump_id)
                                            ->orderBy('reading_date', 'desc')
                                            ->orderBy('reading_shift', 'desc')
                                            ->first();
                                    @endphp
                                    @if($cardLastReading)
                                        <!-- Latest Meter Reading Display -->
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-medium text-red-700 uppercase tracking-wide">⚠️ Last Official Reading</span>
                                                <span class="text-xs text-red-600 font-bold">MUST EXCEED</span>
                                            </div>
                                            <div class="space-y-1 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-slate-600">Reading:</span>
                                                    <span class="font-bold text-xl text-red-600">
                                                        {{ number_format($cardLastReading->meter_reading_liters, 3) }}L
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-600">Date:</span>
                                                    <span class="font-medium text-slate-900">
                                                        {{ \Carbon\Carbon::parse($cardLastReading->reading_date)->format('M j, Y') }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-600">Shift:</span>
                                                    <span class="font-medium text-slate-900">{{ $cardLastReading->reading_shift }}</span>
                                                </div>
                                                @php
                                                    $cardEnteredByUser = null;
                                                    if ($cardLastReading->entered_by) {
                                                        $cardEnteredByUser = DB::table('users')
                                                            ->select(['first_name', 'last_name', 'employee_number'])
                                                            ->where('id', $cardLastReading->entered_by)
                                                            ->first();
                                                    }
                                                @endphp
                                                @if($cardEnteredByUser)
                                                    <div class="flex justify-between">
                                                        <span class="text-slate-600">By:</span>
                                                        <span class="font-medium text-slate-900">{{ $cardEnteredByUser->first_name }} {{ $cardEnteredByUser->last_name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <!-- First Reading Message -->
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-center py-2">
                                                <div class="text-xs text-green-600 mb-1">
                                                    <i class="fas fa-star mr-1"></i>TRUE first reading
                                                </div>
                                                <div class="text-xs text-slate-500">No fraud protection active</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-gas-pump text-slate-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-900 mb-2">No Pumps Available</h3>
                            <p class="text-slate-600">No active pumps are assigned to your stations.</p>
                        </div>
                    @endif
                </div>

                <!-- Step 2: Enter Reading -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Enter Meter Reading</h3>
                        <p class="text-sm text-slate-600">Enter the precise meter reading (0.001L precision required)</p>
                    </div>

                    <div x-show="selectedPump" class="space-y-6">
                        <!-- Selected Pump Info with Latest Reading -->
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-gas-pump text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900" x-text="selectedPump?.station_name"></div>
                                    <div class="text-sm text-slate-600">Pump <span x-text="selectedPump?.pump_number"></span></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-slate-600">Product:</span>
                                    <span class="font-medium text-slate-900 ml-1" x-text="selectedPump?.product_name"></span>
                                </div>
                                <div>
                                    <span class="text-slate-600">Max Reading:</span>
                                    <span class="font-medium text-slate-900 ml-1" x-text="selectedPump?.meter_maximum_reading ? selectedPump.meter_maximum_reading.toLocaleString() + 'L' : 'N/A'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Latest Reading Alert Box -->
                        @foreach($availablePumps as $pump)
                            @php
                                // CRITICAL FIX: Check meter_readings table (what the trigger actually validates against)
                                $lastMeterReading = DB::table('meter_readings')
                                    ->select([
                                        'meter_reading_liters',
                                        'reading_date',
                                        'reading_shift',
                                        'reading_timestamp',
                                        'created_at',
                                        'entered_by',
                                        'meter_reset_occurred',
                                        'pre_reset_reading'
                                    ])
                                    ->where('pump_id', $pump->pump_id)
                                    ->orderBy('reading_date', 'desc')
                                    ->orderBy('reading_shift', 'desc')
                                    ->first();

                                // Get pump's reset threshold for reset detection
                                $pumpDetails = DB::table('pumps')
                                    ->select(['meter_reset_threshold', 'meter_maximum_reading', 'installation_date', 'last_calibration_date', 'pump_manufacturer', 'pump_model'])
                                    ->where('id', $pump->pump_id)
                                    ->first();

                                $enteredByUser = null;
                                if ($lastMeterReading && $lastMeterReading->entered_by) {
                                    $enteredByUser = DB::table('users')
                                        ->select(['first_name', 'last_name', 'employee_number'])
                                        ->where('id', $lastMeterReading->entered_by)
                                        ->first();
                                }

                                // TRUE first reading detection: no entries in meter_readings table
                                $isFirstReading = !$lastMeterReading;
                            @endphp
                            <div x-show="selectedPump && selectedPump.pump_id === {{ $pump->pump_id }}">
                                @if($lastMeterReading)
                                    <!-- CRITICAL: Previous Reading from meter_readings table (what trigger validates) -->
                                    <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-red-600 mt-0.5 mr-3"></i>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-red-800 mb-2">⚠️ FRAUD PROTECTION ACTIVE</h4>
                                                <p class="text-sm text-red-700 mb-3">This pump has a previous meter reading. Your new reading MUST be higher to prevent fraud alerts.</p>

                                                <div class="bg-white border border-red-200 rounded-lg p-3 mb-3">
                                                    <div class="text-xs font-medium text-red-800 mb-2">LAST OFFICIAL METER READING:</div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between">
                                                                <span class="text-red-700">Reading:</span>
                                                                <span class="font-bold text-xl text-red-900">{{ number_format($lastMeterReading->meter_reading_liters, 3) }}L</span>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <span class="text-red-700">Date:</span>
                                                                <span class="font-medium text-red-900">{{ \Carbon\Carbon::parse($lastMeterReading->reading_date)->format('M j, Y') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between">
                                                                <span class="text-red-700">Shift:</span>
                                                                <span class="font-medium text-red-900">{{ $lastMeterReading->reading_shift }}</span>
                                                            </div>
                                                            @if($lastMeterReading->meter_reset_occurred)
                                                                <div class="flex justify-between">
                                                                    <span class="text-red-700">Reset:</span>
                                                                    <span class="font-medium text-amber-700">Yes ({{ number_format($lastMeterReading->pre_reset_reading, 3) }}L)</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($enteredByUser)
                                                        <div class="mt-2 pt-2 border-t border-red-200">
                                                            <span class="text-xs text-red-600">
                                                                Entered by: {{ $enteredByUser->first_name }} {{ $enteredByUser->last_name }}
                                                                @if($enteredByUser->employee_number) ({{ $enteredByUser->employee_number }}) @endif
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="space-y-2 text-xs">
                                                    <div class="flex items-start">
                                                        <i class="fas fa-shield-alt text-red-600 mt-0.5 mr-2"></i>
                                                        <span class="text-red-700"><strong>Your new reading must be > {{ number_format($lastMeterReading->meter_reading_liters, 3) }}L</strong></span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <i class="fas fa-ban text-red-600 mt-0.5 mr-2"></i>
                                                        <span class="text-red-700">Lower readings trigger "FRAUD ALERT: Meter reading cannot go backward"</span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <i class="fas fa-info-circle text-red-600 mt-0.5 mr-2"></i>
                                                        <span class="text-red-700">Reset threshold: {{ number_format($pumpDetails->meter_reset_threshold, 0) }}L (auto-detected at 95%)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- TRUE First Reading -->
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-star text-green-500 mt-0.5 mr-3"></i>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-green-800 mb-2">✅ TRUE FIRST METER READING</h4>
                                                <p class="text-sm text-green-700 mb-3">No previous meter readings found. You can enter any valid reading as the baseline.</p>

                                                <div class="bg-white border border-green-200 rounded-lg p-3 mb-3">
                                                    <div class="text-xs font-medium text-green-800 mb-2">Pump Installation Details:</div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                                        <div class="flex justify-between">
                                                            <span class="text-green-700">Installed:</span>
                                                            <span class="font-medium text-green-900">
                                                                {{ \Carbon\Carbon::parse($pumpDetails->installation_date)->format('M j, Y') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-green-700">Last Calibrated:</span>
                                                            <span class="font-medium text-green-900">
                                                                {{ \Carbon\Carbon::parse($pumpDetails->last_calibration_date)->format('M j, Y') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-green-700">Manufacturer:</span>
                                                            <span class="font-medium text-green-900">{{ $pumpDetails->pump_manufacturer }}</span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span class="text-green-700">Model:</span>
                                                            <span class="font-medium text-green-900">{{ $pumpDetails->pump_model }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="space-y-2 text-xs">
                                                    <div class="flex items-start">
                                                        <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                                                        <span class="text-green-700">No fraud protection - system accepts any valid reading</span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                                                        <span class="text-green-700">Read the meter display carefully - include all digits</span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <i class="fas fa-check-circle text-green-600 mt-0.5 mr-2"></i>
                                                        <span class="text-green-700">Use exactly 3 decimal places (e.g., 1234.567)</span>
                                                    </div>
                                                    <div class="flex items-start">
                                                        <i class="fas fa-star text-green-600 mt-0.5 mr-2"></i>
                                                        <span class="text-green-700">This becomes the baseline for all future readings</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Reading Form -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Reading Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       x-model="form.reading_date"
                                       :max="new Date().toISOString().split('T')[0]"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Reading Shift <span class="text-red-500">*</span>
                                </label>
                                <select x-model="form.reading_shift"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">Select Shift</option>
                                    <option value="MORNING">Morning Shift</option>
                                    <option value="EVENING">Evening Shift</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Meter Reading (Liters) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           x-model="form.meter_reading_liters"
                                           @input="validateMeterReading"
                                           placeholder="0.000"
                                           pattern="^\d+(\.\d{3})?$"
                                           class="w-full px-3 py-2 pr-12 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-sm text-slate-500">L</span>
                                    </div>
                                </div>
                                <div x-show="readingValidation.error" class="mt-1 text-sm text-red-600" x-text="readingValidation.message"></div>
                                <div x-show="!readingValidation.error && form.meter_reading_liters" class="mt-1 text-sm text-emerald-600">
                                    <i class="fas fa-check-circle mr-1"></i>Valid precision (0.001L)
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Entry Notes <span class="text-slate-400">(Optional)</span>
                                </label>
                                <textarea x-model="form.entry_notes"
                                          rows="3"
                                          maxlength="1000"
                                          placeholder="Any observations or notes about this reading..."
                                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"></textarea>
                                <div class="mt-1 text-xs text-slate-500">
                                    <span x-text="form.entry_notes ? form.entry_notes.length : 0"></span>/1000 characters
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Confirm -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Confirm Reading</h3>
                        <p class="text-sm text-slate-600">Review all details before submitting</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Summary Card -->
                        <div class="bg-slate-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-semibold text-slate-900 mb-3">Pump Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Station:</span>
                                            <span class="font-medium text-slate-900" x-text="selectedPump?.station_name"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Pump Number:</span>
                                            <span class="font-medium text-slate-900" x-text="selectedPump?.pump_number"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Product:</span>
                                            <span class="font-medium text-slate-900" x-text="selectedPump?.product_name"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Tank:</span>
                                            <span class="font-medium text-slate-900" x-text="selectedPump?.tank_number"></span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900 mb-3">Reading Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Date:</span>
                                            <span class="font-medium text-slate-900" x-text="form.reading_date"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Shift:</span>
                                            <span class="font-medium text-slate-900" x-text="form.reading_shift"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-600">Reading:</span>
                                            <span class="font-bold text-lg text-blue-600" x-text="parseFloat(form.meter_reading_liters).toFixed(3) + 'L'"></span>
                                        </div>
                                        <div x-show="form.entry_notes" class="pt-2">
                                            <span class="text-slate-600 block mb-1">Notes:</span>
                                            <span class="font-medium text-slate-900 text-xs" x-text="form.entry_notes"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Validations -->
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-shield-check text-emerald-600 mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-emerald-800">System Validations Passed</h4>
                                    <ul class="text-sm text-emerald-700 mt-2 space-y-1">
                                        <li><i class="fas fa-check mr-2"></i>Mathematical precision (0.001L) verified</li>
                                        <li><i class="fas fa-check mr-2"></i>FIFO automation system ready</li>
                                        <li><i class="fas fa-check mr-2"></i>Database triggers will auto-process</li>
                                        @if($isAutoApproved)
                                            <li><i class="fas fa-check mr-2"></i>Auto-approval enabled for your role</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between pt-6 border-t border-slate-200">
                    <button @click="previousStep"
                            x-show="currentStep > 1"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Previous
                    </button>
                    <div x-show="currentStep === 1"></div>

                    <button @click="nextStep"
                            x-show="currentStep < 3"
                            :disabled="!canProceed"
                            :class="canProceed ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                            class="px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <button @click="submitReading"
                            x-show="currentStep === 3"
                            :disabled="submitting || !canSubmit"
                            :class="submitting || !canSubmit ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                            class="px-6 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors duration-200">
                        <span x-show="!submitting">
                            <i class="fas fa-save mr-2"></i>Submit Reading
                        </span>
                        <span x-show="submitting">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function meterReadingForm() {
    return {
        currentStep: 1,
        submitting: false,
        selectedPump: null,
        form: {
            pump_id: '',
            reading_date: new Date().toISOString().split('T')[0],
            reading_shift: '{{ $currentShift }}',
            meter_reading_liters: '',
            entry_notes: ''
        },
        readingValidation: {
            error: false,
            message: ''
        },

        get canProceed() {
            switch(this.currentStep) {
                case 1:
                    return this.selectedPump !== null;
                case 2:
                    return this.form.pump_id &&
                           this.form.reading_date &&
                           this.form.reading_shift &&
                           this.form.meter_reading_liters &&
                           !this.readingValidation.error;
                case 3:
                    return true;
                default:
                    return false;
            }
        },

        get canSubmit() {
            return this.selectedPump &&
                   this.form.pump_id &&
                   this.form.reading_date &&
                   this.form.reading_shift &&
                   this.form.meter_reading_liters &&
                   !this.readingValidation.error;
        },

        init() {
            // Initialize today's date
            this.form.reading_date = new Date().toISOString().split('T')[0];
        },

        selectPump(pump) {
            this.selectedPump = pump;
            this.form.pump_id = pump.pump_id;

            // Check for baseline requirement warning
            if (pump.requires_morning_baseline) {
                Swal.fire({
                    title: 'Morning Baseline Required',
                    text: 'This pump requires a morning baseline reading before meter readings can be entered.',
                    icon: 'warning',
                    confirmButtonText: 'Continue Anyway',
                    confirmButtonColor: '#3b82f6',
                    showCancelButton: true,
                    cancelButtonText: 'Select Different Pump'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        this.selectedPump = null;
                        this.form.pump_id = '';
                    }
                });
            }
        },

        validateMeterReading() {
            const reading = this.form.meter_reading_liters;

            if (!reading) {
                this.readingValidation = { error: false, message: '' };
                return;
            }

            // Check decimal precision (exactly 3 decimal places or whole number)
            const precisionRegex = /^\d+(\.\d{3})?$/;
            if (!precisionRegex.test(reading)) {
                this.readingValidation = {
                    error: true,
                    message: 'Reading must have exactly 3 decimal places (e.g., 1234.567) or be a whole number'
                };
                return;
            }

            const numericReading = parseFloat(reading);

            // Check minimum value
            if (numericReading < 0) {
                this.readingValidation = {
                    error: true,
                    message: 'Reading cannot be negative'
                };
                return;
            }

            // Check maximum value
            if (numericReading > 999999999999.999) {
                this.readingValidation = {
                    error: true,
                    message: 'Reading exceeds maximum allowed value'
                };
                return;
            }

            // Check against pump maximum if available
            if (this.selectedPump?.meter_maximum_reading && numericReading > this.selectedPump.meter_maximum_reading) {
                this.readingValidation = {
                    error: true,
                    message: `Reading exceeds pump maximum (${this.selectedPump.meter_maximum_reading.toLocaleString()}L)`
                };
                return;
            }

            this.readingValidation = { error: false, message: '' };
        },

        nextStep() {
            if (!this.canProceed) return;

            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        submitReading() {
            if (!this.canSubmit || this.submitting) return;

            // Final validation
            if (!this.selectedPump || !this.form.pump_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Selection',
                    text: 'Please select a valid pump.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            this.submitting = true;

            fetch('/continuous-meter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.form)
            })
            .then(response => response.json())
            .then(data => {
                this.submitting = false;

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reading Submitted',
                        text: data.message || 'Meter reading has been successfully processed.',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.href = '/continuous-meter';
                    });
                } else {
                    throw new Error(data.message || 'Submission failed');
                }
            })
            .catch(error => {
                this.submitting = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: error.message || 'An error occurred while submitting the reading.',
                    confirmButtonColor: '#3b82f6'
                });
            });
        }
    };
}
</script>
@endsection
