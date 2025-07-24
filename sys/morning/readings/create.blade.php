@extends('layouts.app')

@section('title', 'Morning Reading Entry - FUEL_ERP')

@section('content')
<div class="min-h-screen bg-zinc-50">
    <!-- Header -->
    <div class="bg-white border-b border-zinc-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-zinc-900 text-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-thermometer-half text-sm"></i>
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-zinc-900">Morning Reading Entry</h1>
                    <p class="text-sm text-zinc-600">{{ date('Y-m-d') }} | {{ $timeValidation['message'] }}</p>
                </div>
            </div>
            <a href="{{ route('morning.readings.index') }}"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-md hover:bg-zinc-50">
                <i class="fas fa-arrow-left mr-2 text-xs"></i>Back
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-8">
        <div x-data="morningReadingForm()" class="space-y-6">

            <!-- Progress Steps -->
            <div class="bg-white rounded-lg border border-zinc-200 p-4">
                <div class="flex items-center justify-between">
                    <div :class="step >= 1 ? 'text-emerald-600' : 'text-zinc-400'" class="flex items-center">
                        <div :class="step >= 1 ? 'bg-emerald-600 text-white' : 'bg-zinc-300 text-zinc-500'"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-2">1
                        </div>
                        <span class="text-sm font-medium">Select Tank</span>
                    </div>
                    <div class="flex-1 h-px bg-zinc-200 mx-4"></div>
                    <div :class="step >= 2 ? 'text-emerald-600' : 'text-zinc-400'" class="flex items-center">
                        <div :class="step >= 2 ? 'bg-emerald-600 text-white' : 'bg-zinc-300 text-zinc-500'"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-2">2
                        </div>
                        <span class="text-sm font-medium">Enter Reading</span>
                    </div>
                    <div class="flex-1 h-px bg-zinc-200 mx-4"></div>
                    <div :class="step >= 3 ? 'text-emerald-600' : 'text-zinc-400'" class="flex items-center">
                        <div :class="step >= 3 ? 'bg-emerald-600 text-white' : 'bg-zinc-300 text-zinc-500'"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-2">3
                        </div>
                        <span class="text-sm font-medium">Confirm</span>
                    </div>
                </div>
            </div>

            <!-- Step 1: Tank Selection -->
            <div x-show="step === 1" class="bg-white rounded-lg border border-zinc-200">
                <div class="p-6 border-b border-zinc-200">
                    <h2 class="text-lg font-semibold text-zinc-900">Select Tank</h2>
                    <p class="text-sm text-zinc-600 mt-1">Choose the tank for morning dip reading</p>
                </div>
                <div class="p-6">
                    <div class="grid gap-3">
                        @foreach($tanks as $tank)
                        <label class="relative">
                            <input type="radio" name="tank_id" value="{{ $tank->id }}" x-model="form.tank_id"
                                class="sr-only">
                            <div :class="form.tank_id == {{ $tank->id }} ? 'border-emerald-500 bg-emerald-50' : 'border-zinc-200 hover:border-zinc-300'"
                                class="p-4 border-2 rounded-lg cursor-pointer transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div :class="form.tank_id == {{ $tank->id }} ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-700'"
                                            class="w-10 h-10 rounded-lg flex items-center justify-center font-medium">
                                            {{ $tank->tank_number }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900">{{ $tank->station_name }} - Tank {{
                                                $tank->tank_number }}</div>
                                            <div class="text-sm text-zinc-600">{{ $tank->product_name }} | {{
                                                number_format($tank->capacity_liters) }}L</div>
                                        </div>
                                    </div>
                                    @if(isset($previousEveningReadings[$tank->id]))
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-zinc-900">{{
                                            number_format($previousEveningReadings[$tank->id]->volume_liters, 1) }}L
                                        </div>
                                        <div class="text-xs text-zinc-500">Previous evening</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button @click="nextStep()" :disabled="!form.tank_id"
                            class="px-4 py-2 bg-zinc-900 text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-zinc-800">
                            Continue <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Reading Entry -->
            <div x-show="step === 2" class="bg-white rounded-lg border border-zinc-200">
                <div class="p-6 border-b border-zinc-200">
                    <h2 class="text-lg font-semibold text-zinc-900">Enter Reading</h2>
                    <p class="text-sm text-zinc-600 mt-1">Record dip stick measurement and temperature</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reading Inputs -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-2">Dip Reading (mm)</label>
                                <input type="number" step="0.01" min="0" max="10000" x-model="form.dip_reading_mm"
                                    @input="calculateVolume()"
                                    class="w-full px-3 py-2 border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <div x-show="errors.dip_reading_mm" class="mt-1 text-sm text-red-600"
                                    x-text="errors.dip_reading_mm"></div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-2">Temperature (°C)</label>
                                <input type="number" step="0.1" min="-10" max="60" x-model="form.temperature_celsius"
                                    @input="calculateVolume()"
                                    class="w-full px-3 py-2 border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <div x-show="errors.temperature_celsius" class="mt-1 text-sm text-red-600"
                                    x-text="errors.temperature_celsius"></div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-2">Water Level (mm)</label>
                                <input type="number" step="0.01" min="0" x-model="form.water_level_mm"
                                    class="w-full px-3 py-2 border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <div x-show="errors.water_level_mm" class="mt-1 text-sm text-red-600"
                                    x-text="errors.water_level_mm"></div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-2">Notes (Optional)</label>
                                <textarea x-model="form.reading_notes" rows="3"
                                    class="w-full px-3 py-2 border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            </div>
                        </div>

                        <!-- Live Calculations & Previous Data -->
                        <div class="space-y-4">
                            <div class="bg-zinc-50 rounded-lg p-4">
                                <h3 class="font-medium text-zinc-900 mb-3">Calculated Volume</h3>
                                <div class="text-2xl font-bold text-emerald-600" x-text="calculatedVolume + ' L'"></div>
                                <div class="text-sm text-zinc-600 mt-1">Temperature corrected</div>
                            </div>

                            <div x-show="selectedTank" class="bg-zinc-50 rounded-lg p-4">
                                <h3 class="font-medium text-zinc-900 mb-3">Tank Information</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600">Capacity:</span>
                                        <span class="font-medium"
                                            x-text="selectedTank ? selectedTank.capacity_liters.toLocaleString() + ' L' : ''"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-600">Product:</span>
                                        <span class="font-medium"
                                            x-text="selectedTank ? selectedTank.product_name : ''"></span>
                                    </div>
                                    <div x-show="previousReading" class="flex justify-between">
                                        <span class="text-zinc-600">Previous Evening:</span>
                                        <span class="font-medium"
                                            x-text="previousReading ? previousReading.volume_liters + ' L' : ''"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Historical Readings -->
                            <div x-show="selectedTank && getHistoricalData()"
                                class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <h3 class="font-medium text-blue-900 mb-3 flex items-center">
                                    <i class="fas fa-history mr-2 text-sm"></i>Recent Readings (14 days)
                                </h3>
                                <div class="space-y-2 max-h-40 overflow-y-auto">
                                    <template x-for="reading in getHistoricalData()"
                                        :key="reading.reading_date + reading.reading_shift">
                                        <div
                                            class="flex justify-between items-center py-1 px-2 bg-white rounded text-xs">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-blue-900"
                                                    x-text="reading.reading_date"></span>
                                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded"
                                                    x-text="reading.reading_shift"></span>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <span class="text-zinc-600"
                                                    x-text="reading.dip_reading_mm + 'mm'"></span>
                                                <span class="font-medium text-blue-900"
                                                    x-text="reading.dip_reading_liters + 'L'"></span>
                                                <span class="text-zinc-500"
                                                    x-text="reading.temperature_celsius + '°C'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- In Step 2 variance analysis: -->
                            <div x-show="variance !== null && variance !== undefined" class="rounded-lg p-4"
                                :class="Math.abs(variance || 0) > 2 ? 'bg-red-50 border border-red-200' : Math.abs(variance || 0) > 1 ? 'bg-yellow-50 border border-yellow-200' : 'bg-emerald-50 border border-emerald-200'">
                                <h3 class="font-medium mb-2"
                                    :class="Math.abs(variance || 0) > 2 ? 'text-red-900' : Math.abs(variance || 0) > 1 ? 'text-yellow-900' : 'text-emerald-900'">
                                    Variance Analysis</h3>
                                <div class="text-sm">
                                    <div class="flex justify-between">
                                        <span>Overnight change:</span>
                                        <span class="font-medium"
                                            x-text="variance !== null ? variance.toFixed(2) + '%' : 'N/A'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button @click="prevStep()"
                            class="px-4 py-2 text-zinc-700 bg-white border border-zinc-300 rounded-md text-sm font-medium hover:bg-zinc-50">
                            <i class="fas fa-arrow-left mr-2 text-xs"></i>Back
                        </button>
                        <button @click="nextStep()" :disabled="!isStep2Valid()"
                            class="px-4 py-2 bg-zinc-900 text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-zinc-800">
                            Review <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Confirmation -->
            <div x-show="step === 3" class="bg-white rounded-lg border border-zinc-200">
                <div class="p-6 border-b border-zinc-200">
                    <h2 class="text-lg font-semibold text-zinc-900">Confirm Reading</h2>
                    <p class="text-sm text-zinc-600 mt-1">Review and submit morning reading</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between py-2 border-b border-zinc-100">
                                <span class="text-zinc-600">Tank:</span>
                                <span class="font-medium"
                                    x-text="selectedTank ? selectedTank.station_name + ' - Tank ' + selectedTank.tank_number : ''"></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-zinc-100">
                                <span class="text-zinc-600">Product:</span>
                                <span class="font-medium" x-text="selectedTank ? selectedTank.product_name : ''"></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-zinc-100">
                                <span class="text-zinc-600">Dip Reading:</span>
                                <span class="font-medium" x-text="form.dip_reading_mm + ' mm'"></span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-zinc-100">
                                <span class="text-zinc-600">Temperature:</span>
                                <span class="font-medium" x-text="form.temperature_celsius + ' °C'"></span>
                            </div>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-4">
                            <h3 class="font-medium text-zinc-900 mb-3">Final Volume</h3>
                            <div class="text-3xl font-bold text-emerald-600 mb-2" x-text="calculatedVolume + ' L'">
                            </div>
                            <div class="text-sm text-zinc-600">Temperature corrected volume</div>

                            <!-- In Step 3 variance alert: -->
                            <div x-show="variance !== null && Math.abs(variance || 0) > 1" class="mt-4 p-3 rounded-md"
                                :class="Math.abs(variance || 0) > 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span class="text-sm font-medium">Variance Alert: <span
                                            x-text="variance !== null ? variance.toFixed(2) + '%' : 'N/A'"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button @click="prevStep()"
                            class="px-4 py-2 text-zinc-700 bg-white border border-zinc-300 rounded-md text-sm font-medium hover:bg-zinc-50">
                            <i class="fas fa-arrow-left mr-2 text-xs"></i>Back
                        </button>
                        <button @click="submitReading()" :disabled="submitting"
                            class="px-6 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-emerald-700">
                            <span x-show="!submitting">
                                <i class="fas fa-check mr-2 text-xs"></i>Submit Reading
                            </span>
                            <span x-show="submitting">
                                <i class="fas fa-spinner fa-spin mr-2 text-xs"></i>Submitting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function morningReadingForm() {
    return {
        step: 1,
        submitting: false,
        form: {
            tank_id: @json(request()->get('tank_id')) || '',
            reading_date: '{{ date("Y-m-d") }}',
            reading_time: '{{ date("H:i:s") }}',
            dip_reading_mm: '',
            temperature_celsius: '',
            water_level_mm: '0',
            reading_notes: ''
        },
        errors: {},
        calculatedVolume: '0.0',
        variance: null,

        tanks: @json($tanks),
        previousEveningReadings: @json($previousEveningReadings),
        historicalData: @json($historicalData),

        get selectedTank() {
            return this.tanks.find(t => t.id == this.form.tank_id);
        },

        get previousReading() {
            return this.form.tank_id ? this.previousEveningReadings[this.form.tank_id] : null;
        },

        getHistoricalData() {
            if (!this.form.tank_id || !this.historicalData[this.form.tank_id]) return null;
            return this.historicalData[this.form.tank_id].slice(0, 10); // Show last 10 readings
        },

        nextStep() {
            if (this.step === 1 && this.form.tank_id) {
                this.step = 2;
                this.calculateVolume();
            } else if (this.step === 2 && this.isStep2Valid()) {
                this.step = 3;
            }
        },

        prevStep() {
            if (this.step > 1) this.step--;
        },

        isStep2Valid() {
            return this.form.dip_reading_mm &&
                   this.form.temperature_celsius &&
                   this.form.water_level_mm !== '' &&
                   this.form.dip_reading_mm >= 0 &&
                   this.form.dip_reading_mm <= 10000 &&
                   this.form.temperature_celsius >= -10 &&
                   this.form.temperature_celsius <= 60;
        },

      // REPLACE the calculateVolume() function in your script:

calculateVolume() {
    if (!this.form.dip_reading_mm || !this.form.temperature_celsius || !this.selectedTank) {
        this.calculatedVolume = '0.0';
        this.variance = null; // ✅ FIX: Explicitly set to null
        return;
    }

    // Simple approximation - in real system this would call the backend
    const baseVolume = parseFloat(this.form.dip_reading_mm) * 0.5; // Simplified calculation
    const tempCorrection = 1 - ((parseFloat(this.form.temperature_celsius) - 15) * 0.001);
    const corrected = baseVolume * tempCorrection;
    this.calculatedVolume = Math.max(0, corrected).toFixed(1);

    // ✅ FIX: Calculate variance with null checks
    if (this.previousReading && this.previousReading.volume_liters) {
        const previousVol = parseFloat(this.previousReading.volume_liters);
        if (previousVol > 0) {
            const change = corrected - previousVol;
            this.variance = (change / previousVol) * 100;
        } else {
            this.variance = 0;
        }
    } else {
        this.variance = null; // ✅ FIX: Set to null when no previous reading
    }
},

        async submitReading() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("morning.readings.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.location.href = '{{ route("morning.readings.index") }}';
                } else {
                    throw new Error(data.error || 'Submission failed');
                }
            } catch (error) {
                await Swal.fire({
                    title: 'Error',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endsection
