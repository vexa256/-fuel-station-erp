@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold text-foreground">Daily Readings - {{ $station->station_name }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ $station->station_code }} • {{
            Carbon\Carbon::parse($date)->format('l, F j, Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <input type="date" value="{{ $date }}"
            onchange="window.location.href='{{ route('readings.daily', $station->id) }}/' + this.value"
            class="input text-sm">
        <a href="{{ route('readings.index') }}" class="btn btn-outline btn-sm">Back to Dashboard</a>
    </div>
</div>
@endsection

@section('content')
@php
$hasVirtualApproval = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);
$morningReadings = $existingReadings->where('reading_shift', 'MORNING');
$eveningReadings = $existingReadings->where('reading_shift', 'EVENING');
$currentHour = now()->hour;
$defaultShift = $currentHour < 14 ? 'MORNING' : 'EVENING' ; @endphp <div class="space-y-6" x-data="readingsForm()">
    <!-- Shift Selection & Status -->
    <div class="card">
        <div class="card-content p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium">Reading Shift:</label>
                        <select x-model="selectedShift" @change="loadExistingReadings()" class="select w-32">
                            <option value="MORNING">Morning</option>
                            <option value="EVENING">Evening</option>
                        </select>
                    </div>
                    <div class="h-4 w-px bg-border"></div>
                    <div class="text-sm text-muted-foreground">
                        Current Time: {{ now()->format('H:i') }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="morningComplete">
                        <span class="badge badge-default">Morning ✓</span>
                    </template>
                    <template x-if="eveningComplete">
                        <span class="badge badge-default">Evening ✓</span>
                    </template>
                    @if($hasVirtualApproval)
                    <span class="badge badge-outline text-xs">Auto-Approval Active</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tank Readings Form -->
    <form @submit.prevent="submitReadings()" class="space-y-4">
        @csrf
        <input type="hidden" name="station_id" value="{{ $station->id }}">
        <input type="hidden" name="reading_date" value="{{ $date }}">
        <input type="hidden" name="reading_shift" x-model="selectedShift">

        @foreach($tanks as $tank)
        @php
        $existingReading = $existingReadings->where('tank_id', $tank->id)
        ->where('reading_shift', $defaultShift)
        ->first();
        $previousReading = $previousReadings->where('tank_id', $tank->id)->first();
        @endphp

        <div class="card" x-data="{
            tankId: {{ $tank->id }},
            dipReading: '{{ $existingReading->dip_reading_mm ?? '' }}',
            meterReading: '{{ $existingReading->meter_reading ?? '' }}',
            calculatedVolume: {{ $existingReading->calculated_volume_liters ?? 0 }},
            isValidating: false,
            errors: {},
            previousDip: {{ $previousReading->dip_reading_mm ?? 0 }},
            previousMeter: {{ $previousReading->meter_reading ?? 0 }}
        }">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">Tank {{ $tank->tank_number }} - {{ $tank->product_name }}</h3>
                        <p class="text-sm text-muted-foreground">Capacity: {{ number_format($tank->capacity_liters) }}L
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($existingReading)
                        <span class="badge badge-default">Recorded</span>
                        @endif
                        <template x-if="calculatedVolume > 0">
                            <span class="badge badge-outline"
                                x-text="Math.round(calculatedVolume).toLocaleString() + 'L'"></span>
                        </template>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <!-- Previous Reading Context -->
                @if($previousReading)
                <div class="mb-4 p-3 bg-muted/30 rounded-md">
                    <div class="text-xs font-medium text-muted-foreground mb-2">Previous Reading ({{
                        Carbon\Carbon::parse($previousReading->reading_date)->format('M j') }})</div>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Dip:</span>
                            <span class="font-medium ml-2">{{ number_format($previousReading->dip_reading_mm)
                                }}mm</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Meter:</span>
                            <span class="font-medium ml-2">{{ number_format($previousReading->meter_reading) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Volume:</span>
                            <span class="font-medium ml-2">{{ number_format($previousReading->calculated_volume_liters)
                                }}L</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Reading Input Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Dip Reading -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Dip Reading (mm)</label>
                        <div class="relative">
                            <input type="number" step="1" min="0" max="{{ $tank->maximum_dip_mm }}" x-model="dipReading"
                                @input.debounce.500ms="validateDipReading()" name="tanks[{{ $tank->id }}][dip_reading]"
                                class="input w-full pr-12" :class="{ 'border-destructive': errors.dip }"
                                placeholder="Enter dip reading">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <template x-if="isValidating">
                                    <div
                                        class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin">
                                    </div>
                                </template>
                            </div>
                        </div>
                        <template x-if="errors.dip">
                            <p class="text-xs text-destructive" x-text="errors.dip"></p>
                        </template>
                        @if($previousReading)
                        <p class="text-xs text-muted-foreground">
                            Previous: {{ number_format($previousReading->dip_reading_mm) }}mm
                        </p>
                        @endif
                    </div>

                    <!-- Meter Reading -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Meter Reading</label>
                        <input type="number" step="0.01" min="{{ $previousReading->meter_reading ?? 0 }}"
                            x-model="meterReading" @input.debounce.500ms="validateMeterReading()"
                            name="tanks[{{ $tank->id }}][meter_reading]" class="input w-full"
                            :class="{ 'border-destructive': errors.meter }" placeholder="Enter meter reading">
                        <template x-if="errors.meter">
                            <p class="text-xs text-destructive" x-text="errors.meter"></p>
                        </template>
                        @if($previousReading)
                        <p class="text-xs text-muted-foreground">
                            Previous: {{ number_format($previousReading->meter_reading, 2) }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Calculated Volume Display -->
                <template x-if="calculatedVolume > 0">
                    <div class="mt-4 p-3 bg-primary/5 rounded-md">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Calculated Volume</span>
                            <span class="text-lg font-semibold text-primary"
                                x-text="Math.round(calculatedVolume).toLocaleString() + ' Liters'"></span>
                        </div>
                        <template x-if="previousReading">
                            <div class="text-xs text-muted-foreground mt-1">
                                <template
                                    x-if="calculatedVolume > {{ $previousReading->calculated_volume_liters ?? 0 }}">
                                    <span class="text-green-600">
                                        +{{ Math.round(calculatedVolume - {{ $previousReading->calculated_volume_liters
                                        ?? 0 }}).toLocaleString() }}L from previous
                                    </span>
                                </template>
                                <template
                                    x-if="calculatedVolume < {{ $previousReading->calculated_volume_liters ?? 0 }}">
                                    <span class="text-red-600">
                                        {{ Math.round(calculatedVolume - {{ $previousReading->calculated_volume_liters
                                        ?? 0 }}).toLocaleString() }}L from previous
                                    </span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <input type="hidden" name="tanks[{{ $tank->id }}][calculated_volume]" x-model="calculatedVolume">
            </div>
        </div>
        @endforeach

        <!-- Submit Section -->
        <div class="card">
            <div class="card-content p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        <template x-if="!allTanksComplete()">
                            <span>Complete all tank readings to save</span>
                        </template>
                        <template x-if="allTanksComplete()">
                            <span>All readings validated and ready to save</span>
                        </template>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="validateAllReadings()" class="btn btn-outline btn-sm">
                            Validate All
                        </button>
                        <button type="submit" :disabled="!allTanksComplete() || isSubmitting" class="btn btn-primary"
                            :class="{ 'opacity-50 cursor-not-allowed': !allTanksComplete() || isSubmitting }">
                            <template x-if="isSubmitting">
                                <span>Saving...</span>
                            </template>
                            <template x-if="!isSubmitting">
                                <span>Save Readings</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>

    <script>
        function readingsForm() {
    return {
        selectedShift: '{{ $defaultShift }}',
        isSubmitting: false,
        morningComplete: {{ $morningReadings->count() === $tanks->count() ? 'true' : 'false' }},
        eveningComplete: {{ $eveningReadings->count() === $tanks->count() ? 'true' : 'false' }},

        validateDipReading() {
            if (!this.dipReading) return;

            this.isValidating = true;
            fetch('{{ route("readings.validateDip") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tank_id: this.tankId,
                    dip_reading: this.dipReading,
                    reading_date: '{{ $date }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                this.isValidating = false;
                if (data.success) {
                    this.calculatedVolume = data.calculated_volume;
                    delete this.errors.dip;
                } else {
                    this.errors.dip = data.message;
                    this.calculatedVolume = 0;
                }
            })
            .catch(error => {
                this.isValidating = false;
                this.errors.dip = 'Validation failed';
            });
        },

        validateMeterReading() {
            if (!this.meterReading) return;

            fetch('{{ route("readings.validateMeter") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tank_id: this.tankId,
                    meter_reading: this.meterReading,
                    previous_reading: this.previousMeter
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    delete this.errors.meter;
                } else {
                    this.errors.meter = data.message;
                }
            })
            .catch(error => {
                this.errors.meter = 'Validation failed';
            });
        },

        validateAllReadings() {
            document.querySelectorAll('[x-data]').forEach(el => {
                if (el._x_dataStack && el._x_dataStack[0].validateDipReading) {
                    el._x_dataStack[0].validateDipReading();
                    el._x_dataStack[0].validateMeterReading();
                }
            });
        },

        allTanksComplete() {
            const tankElements = document.querySelectorAll('[x-data*="tankId"]');
            for (let el of tankElements) {
                const data = el._x_dataStack[0];
                if (!data.dipReading || !data.meterReading || data.calculatedVolume <= 0) {
                    return false;
                }
            }
            return tankElements.length > 0;
        },

        submitReadings() {
            this.isSubmitting = true;

            const form = document.querySelector('form');
            const formData = new FormData(form);

            fetch('{{ route("readings.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Readings Saved',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Save failed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    text: error.message || 'Please check your readings and try again'
                });
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        loadExistingReadings() {
            window.location.href = '{{ route("readings.daily", [$station->id, $date]) }}';
        }
    }
}
    </script>
    @endsection
