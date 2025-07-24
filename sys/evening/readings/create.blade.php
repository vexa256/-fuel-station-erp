@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100/50" x-data="eveningReadingWizard()">

    <!-- Premium Header -->
    <div class="sticky top-0 z-10 backdrop-blur-xl bg-white/80 border-b border-slate-200/60">
        <div class="max-w-4xl mx-auto px-6">
            <div class="flex items-center justify-between py-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('evening.readings.index') }}"
                       class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="lucide-arrow-left w-5 h-5 text-slate-600"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                            Add Evening Reading
                        </h1>
                        <p class="text-sm text-slate-600">{{ $currentDate }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $timeValidation['valid'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        <i class="lucide-clock w-3 h-3 mr-1"></i>
                        {{ $timeValidation['message'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Progress -->
    <div class="max-w-4xl mx-auto px-6 pt-6">
        <div class="flex items-center justify-center space-x-4 mb-8">
            <div :class="step >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
                 class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-colors">
                1
            </div>
            <div :class="step >= 2 ? 'bg-blue-600' : 'bg-slate-200'" class="h-1 w-16 rounded transition-colors"></div>
            <div :class="step >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
                 class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-colors">
                2
            </div>
            <div :class="step >= 3 ? 'bg-blue-600' : 'bg-slate-200'" class="h-1 w-16 rounded transition-colors"></div>
            <div :class="step >= 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
                 class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-colors">
                3
            </div>
        </div>
    </div>

    <!-- Wizard Content -->
    <div class="max-w-4xl mx-auto px-6 pb-8">

        <!-- Step 1: Tank Selection -->
        <div x-show="step === 1" x-transition class="space-y-6">
            <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">Select Tank</h2>
                @if($tanksNeedingReadings->count() > 0)
                <div class="grid gap-4">
                    @foreach($tanksNeedingReadings as $tank)
                    <div @click="selectTank({{ $tank->id }})"
                         :class="selectedTankId === {{ $tank->id }} ? 'ring-2 ring-blue-500 bg-blue-50/50' : 'hover:bg-slate-50/50'"
                         class="group p-6 border border-slate-200 rounded-xl cursor-pointer transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-xl flex items-center justify-center">
                                    <i class="lucide-droplet w-6 h-6 text-slate-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900">Tank {{ $tank->tank_number }}</h3>
                                    <p class="text-slate-600">{{ $tank->station_name }}</p>
                                    <p class="text-sm text-slate-500">{{ $tank->product_name }} • {{ number_format($tank->capacity_liters, 0) }}L</p>
                                </div>
                            </div>
                            <div x-show="selectedTankId === {{ $tank->id }}" class="text-blue-600">
                                <i class="lucide-check-circle w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-emerald-100 rounded-2xl flex items-center justify-center mb-4">
                        <i class="lucide-check-circle w-8 h-8 text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">All Complete</h3>
                    <p class="text-slate-600">All tanks have evening readings for today.</p>
                </div>
                @endif
            </div>

            @if($tanksNeedingReadings->count() > 0)
            <div class="flex justify-end">
                <button @click="nextStep()" :disabled="!selectedTankId"
                        :class="selectedTankId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-300 cursor-not-allowed'"
                        class="px-6 py-3 text-white font-medium rounded-xl transition-colors">
                    Continue
                    <i class="lucide-arrow-right w-4 h-4 ml-2 inline"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Step 2: Reading Entry -->
        <div x-show="step === 2" x-transition class="space-y-6">

            <!-- Tank Context Card -->
            <div x-show="selectedTankData" class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900" x-text="'Tank ' + (selectedTankData?.tank?.tank_number || '')"></h2>
                        <p class="text-slate-600" x-text="selectedTankData?.tank?.station_name + ' • ' + selectedTankData?.tank?.product_name"></p>
                    </div>
                    <button @click="step = 1" class="text-slate-500 hover:text-slate-700">
                        <i class="lucide-edit-2 w-5 h-5"></i>
                    </button>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-slate-50/50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-600 font-medium uppercase tracking-wider mb-1">Morning Stock</p>
                        <p class="text-lg font-bold text-slate-900" x-text="formatLiters(selectedTankData?.analysis?.opening_stock || 0)"></p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-600 font-medium uppercase tracking-wider mb-1">Deliveries</p>
                        <p class="text-lg font-bold text-emerald-600" x-text="formatLiters(selectedTankData?.analysis?.total_inflow || 0)"></p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-600 font-medium uppercase tracking-wider mb-1">Sales</p>
                        <p class="text-lg font-bold text-red-600" x-text="formatLiters(selectedTankData?.analysis?.total_outflow || 0)"></p>
                    </div>
                    <div class="bg-slate-50/50 rounded-lg p-4 text-center">
                        <p class="text-xs text-slate-600 font-medium uppercase tracking-wider mb-1">Expected</p>
                        <p class="text-lg font-bold text-blue-600" x-text="formatLiters(selectedTankData?.analysis?.theoretical_closing || 0)"></p>
                    </div>
                </div>
            </div>

            <!-- Reading Form -->
            <form @submit.prevent="submitReading()" class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-8">
                <h3 class="text-lg font-semibold text-slate-900 mb-6">Enter Reading</h3>

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Input Fields -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Dip Reading (mm)</label>
                            <input type="number" x-model="form.dip_reading_mm" @input="calculateReading()"
                                   step="0.01" min="0" max="9999.99" required
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <p class="text-xs text-slate-500 mt-1">Enter the dip stick reading in millimeters</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Temperature (°C)</label>
                            <input type="number" x-model="form.temperature_celsius" @input="calculateReading()"
                                   step="0.1" min="-10" max="60" required
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <p class="text-xs text-slate-500 mt-1">Fuel temperature in Celsius</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Water Level (mm)</label>
                            <input type="number" x-model="form.water_level_mm"
                                   step="0.01" min="0" max="999.99" required
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <p class="text-xs text-slate-500 mt-1">Water contamination level</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Notes (Optional)</label>
                            <textarea x-model="form.reading_notes" rows="3"
                                     class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                     placeholder="Any observations or notes..."></textarea>
                        </div>
                    </div>

                    <!-- Live Calculations -->
                    <div class="space-y-6">
                        <div class="bg-slate-50/50 rounded-xl p-6">
                            <h4 class="font-semibold text-slate-900 mb-4">Live Calculations</h4>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Corrected Volume</span>
                                    <span class="font-mono font-semibold text-slate-900" x-text="formatLiters(calculations?.corrected_volume || 0)"></span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Expected Reading</span>
                                    <span class="font-mono font-semibold text-slate-900" x-text="formatLiters(calculations?.expected_reading || 0)"></span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Variance</span>
                                    <span class="font-mono font-semibold"
                                          :class="getVarianceColor(calculations?.variance_percentage || 0)"
                                          x-text="formatPercentage(calculations?.variance_percentage || 0)"></span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Stock Change</span>
                                    <span class="font-mono font-semibold"
                                          :class="(calculations?.stock_change || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'"
                                          x-text="formatLiters(calculations?.stock_change || 0, true)"></span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Capacity Utilization</span>
                                    <span class="font-mono font-semibold text-slate-900" x-text="formatPercentage(calculations?.capacity_utilization || 0)"></span>
                                </div>
                            </div>

                            <!-- Status Indicator -->
                            <div x-show="calculations?.reading_status" class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-slate-600">Status:</span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getStatusColor(calculations?.reading_status)"
                                          x-text="calculations?.reading_status"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Alerts -->
                        <div x-show="validationErrors.length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-center mb-2">
                                <i class="lucide-alert-triangle w-5 h-5 text-red-600 mr-2"></i>
                                <h5 class="font-medium text-red-900">Validation Issues</h5>
                            </div>
                            <ul class="text-sm text-red-700 space-y-1">
                                <template x-for="error in validationErrors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-slate-200">
                    <button type="button" @click="step = 1"
                            class="px-6 py-3 text-slate-600 font-medium hover:text-slate-900 transition-colors">
                        <i class="lucide-arrow-left w-4 h-4 mr-2 inline"></i>
                        Back
                    </button>

                    <button type="submit" :disabled="!canSubmit"
                            :class="canSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-300 cursor-not-allowed'"
                            class="px-8 py-3 text-white font-medium rounded-xl transition-colors">
                        <span x-show="!submitting">Submit Reading</span>
                        <span x-show="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function eveningReadingWizard() {
    return {
        step: 1,
        selectedTankId: null,
        selectedTankData: null,
        submitting: false,

        form: {
            tank_id: '',
            reading_date: '{{ $currentDate }}',
            dip_reading_mm: '',
            temperature_celsius: 25,
            water_level_mm: 0,
            reading_notes: ''
        },

        calculations: null,
        validationErrors: [],

        tankDetails: @json($tankDetails),
        varianceThresholds: @json($varianceThresholds),

       get canSubmit() {
    return this.form.dip_reading_mm &&
           this.form.temperature_celsius &&
           this.form.water_level_mm !== '' &&
           this.calculations?.is_valid &&
           !this.submitting;
    // REMOVED: this.validationErrors.length === 0 (CEO can override variances)
},

        selectTank(tankId) {
            this.selectedTankId = tankId;
            this.form.tank_id = tankId;
            this.selectedTankData = this.tankDetails[tankId] || null;
        },

        nextStep() {
            if (this.selectedTankId) {
                this.step = 2;
            }
        },

        async calculateReading() {
            if (!this.form.dip_reading_mm || !this.form.temperature_celsius || !this.form.tank_id) {
                this.calculations = null;
                return;
            }

            try {
                const response = await fetch('{{ route("evening.readings.calculate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tank_id: this.form.tank_id,
                        dip_reading_mm: parseFloat(this.form.dip_reading_mm),
                        temperature_celsius: parseFloat(this.form.temperature_celsius),
                        reading_date: this.form.reading_date
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.calculations = data.calculations;
                    this.validateReading();
                } else {
                    this.calculations = null;
                    this.validationErrors = [data.error || 'Calculation failed'];
                }
            } catch (error) {
                console.error('Calculation error:', error);
                this.calculations = null;
                this.validationErrors = ['Network error during calculation'];
            }
        },

        validateReading() {
            this.validationErrors = [];

            if (!this.calculations) return;

            // Check for impossible values
            if (!this.calculations.is_valid) {
                this.validationErrors.push('Reading exceeds tank capacity or is invalid');
            }

            // Check variance thresholds
            const variance = Math.abs(this.calculations.variance_percentage || 0);
            if (variance > this.varianceThresholds.critical) {
                this.validationErrors.push(`Critical variance detected (${variance.toFixed(2)}%). CEO approval required.`);
            } else if (variance > this.varianceThresholds.significant) {
                this.validationErrors.push(`Significant variance detected (${variance.toFixed(2)}%). Manager approval required.`);
            }

            // Check water contamination
            if (parseFloat(this.form.water_level_mm) > 10) {
                this.validationErrors.push('High water contamination detected. Consider tank inspection.');
            }
        },

        async submitReading() {
            if (!this.canSubmit) return;

            this.submitting = true;

            try {
                const response = await fetch('{{ route("evening.readings.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reading Submitted',
                        text: data.message,
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.href = '{{ route("evening.readings.index") }}';
                    });
                } else {
                    throw new Error(data.error || 'Submission failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: error.message,
                    confirmButtonColor: '#dc2626'
                });
            }

            this.submitting = false;
        },

        formatLiters(value, showSign = false) {
            const num = parseFloat(value) || 0;
            const formatted = Math.abs(num).toLocaleString('en-US', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });

            if (showSign && num !== 0) {
                return (num > 0 ? '+' : '-') + formatted + 'L';
            }
            return formatted + 'L';
        },

        formatPercentage(value) {
            const num = parseFloat(value) || 0;
            return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
        },

        getVarianceColor(percentage) {
            const abs = Math.abs(percentage);
            if (abs <= this.varianceThresholds.minor) return 'text-emerald-600';
            if (abs <= this.varianceThresholds.moderate) return 'text-amber-600';
            if (abs <= this.varianceThresholds.significant) return 'text-orange-600';
            return 'text-red-600';
        },

        getStatusColor(status) {
            switch (status) {
                case 'VALIDATED': return 'bg-emerald-100 text-emerald-700';
                case 'PENDING': return 'bg-amber-100 text-amber-700';
                case 'FLAGGED': return 'bg-red-100 text-red-700';
                default: return 'bg-slate-100 text-slate-700';
            }
        }
    }
}
</script>
@endsection
