@extends('layouts.app')

@section('title', 'Edit Morning Reading - FUEL_ERP')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-black text-white p-2 rounded-lg">
                        <i class="fas fa-edit text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Morning Reading</h1>
                        <p class="text-sm text-gray-600">
                            Tank {{ $reading->tank_number }} | {{ $reading->station_name }} |
                            {{ $reading->reading_date }}
                        </p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('morning.readings.index') }}"
                       class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <!-- System Health Alert -->
        @if(!$systemHealth['healthy'])
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">System Health Warning</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        {{ $systemHealth['critical_issues'] ?? 0 }} critical issues detected. Proceed with caution.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- CEO/Admin Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-shield-alt text-blue-400 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Administrative Override</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        You are editing as {{ auth()->user()->role }} with auto-approval privileges. All changes will be audited.
                    </p>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Reading Details</h3>
                <p class="text-sm text-gray-500 mt-1">Modify the dip reading data with correction justification</p>
            </div>

            <form id="editForm" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Values Display -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4">
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Current Reading</p>
                        <p class="text-lg font-semibold">{{ number_format($reading->dip_reading_mm, 0) }}mm</p>
                        <p class="text-sm text-gray-600">{{ number_format($reading->dip_reading_liters, 1) }}L</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Temperature</p>
                        <p class="text-lg font-semibold">{{ $reading->temperature_celsius }}°C</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Water Level</p>
                        <p class="text-lg font-semibold">{{ number_format($reading->water_level_mm, 1) }}mm</p>
                    </div>
                </div>

                <!-- New Values Input -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dip Reading (mm) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="dip_reading_mm"
                               name="dip_reading_mm"
                               value="{{ $reading->dip_reading_mm }}"
                               step="1"
                               min="0"
                               max="10000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black focus:border-transparent transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Enter measurement in millimeters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Temperature (°C) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="temperature_celsius"
                               name="temperature_celsius"
                               value="{{ $reading->temperature_celsius }}"
                               step="0.1"
                               min="-10"
                               max="60"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black focus:border-transparent transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Temperature at time of reading</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Water Level (mm) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="water_level_mm"
                               name="water_level_mm"
                               value="{{ $reading->water_level_mm }}"
                               step="0.1"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black focus:border-transparent transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Water contamination level</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Correction Reason <span class="text-red-500">*</span>
                        </label>
                        <select id="correction_reason"
                                name="correction_reason"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black focus:border-transparent transition-all"
                                required>
                            <option value="">Select reason...</option>
                            <option value="MEASUREMENT_ERROR">Measurement Error</option>
                            <option value="EQUIPMENT_CALIBRATION">Equipment Calibration</option>
                            <option value="TEMPERATURE_CORRECTION">Temperature Correction</option>
                            <option value="DATA_ENTRY_ERROR">Data Entry Error</option>
                            <option value="AUDIT_ADJUSTMENT">Audit Adjustment</option>
                            <option value="REGULATORY_COMPLIANCE">Regulatory Compliance</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Justification for the correction</p>
                    </div>
                </div>

                <!-- Reading Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea id="validation_notes"
                              name="validation_notes"
                              rows="3"
                              maxlength="500"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black focus:border-transparent transition-all"
                              placeholder="Optional: Additional details about this correction...">{{ $reading->validation_notes }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                </div>

                <!-- Calculated Values Preview -->
                <div id="calculatedPreview" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">
                        <i class="fas fa-calculator mr-2"></i>Calculated Values Preview
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-blue-600">Corrected Volume:</span>
                            <span id="previewVolume" class="font-semibold ml-2">0.0L</span>
                        </div>
                        <div>
                            <span class="text-blue-600">Volume Change:</span>
                            <span id="previewChange" class="font-semibold ml-2">0.0L</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button"
                            onclick="window.location.href='{{ route('morning.readings.index') }}'"
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            id="submitBtn"
                            class="px-6 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-black transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Update Reading
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
const READING_ID = {{ $reading->id }};
const UPDATE_URL = `{{ route('morning.readings.update', ['id' => $reading->id]) }}`;
const INDEX_URL = '{{ route('morning.readings.index') }}';

// Form elements
const form = document.getElementById('editForm');
const submitBtn = document.getElementById('submitBtn');
const calculatedPreview = document.getElementById('calculatedPreview');

// Input change handlers for real-time validation
['dip_reading_mm', 'temperature_celsius'].forEach(id => {
    document.getElementById(id).addEventListener('input', debounce(calculatePreview, 300));
});

// Form submission handler
form.addEventListener('submit', handleSubmit);

async function handleSubmit(e) {
    e.preventDefault();

    if (!validateForm()) return;

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Show loading state
    setLoadingState(true);

    try {
        const response = await fetch(UPDATE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Reading Updated Successfully',
                html: `
                    <div class="text-left">
                        <p><strong>Old Volume:</strong> ${result.data.old_volume}L</p>
                        <p><strong>New Volume:</strong> ${result.data.corrected_volume}L</p>
                        <p><strong>Auto-approved by:</strong> ${result.approved_by_role}</p>
                    </div>
                `,
                confirmButtonColor: '#000000',
                confirmButtonText: 'View Dashboard'
            });

            window.location.href = INDEX_URL;
        } else {
            throw new Error(result.error || 'Update failed');
        }

    } catch (error) {
        console.error('Update failed:', error);

        await Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: error.message || 'An unexpected error occurred. Please try again.',
            confirmButtonColor: '#dc2626'
        });
    } finally {
        setLoadingState(false);
    }
}

function validateForm() {
    const required = ['dip_reading_mm', 'temperature_celsius', 'water_level_mm', 'correction_reason'];
    let isValid = true;

    required.forEach(id => {
        const element = document.getElementById(id);
        const value = element.value.trim();

        if (!value) {
            element.classList.add('border-red-500');
            isValid = false;
        } else {
            element.classList.remove('border-red-500');
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please fill in all required fields.',
            confirmButtonColor: '#f59e0b'
        });
    }

    return isValid;
}

async function calculatePreview() {
    const dipMm = document.getElementById('dip_reading_mm').value;
    const temp = document.getElementById('temperature_celsius').value;

    if (!dipMm || !temp) {
        calculatedPreview.classList.add('hidden');
        return;
    }

    try {
        const response = await fetch('/api/tank-calibration?' + new URLSearchParams({
            tank_id: {{ $reading->tank_id }},
            dip_mm: dipMm,
            temperature: temp
        }));

        if (response.ok) {
            const data = await response.json();
            const currentVolume = {{ $reading->dip_reading_liters }};
            const newVolume = data.corrected_volume;
            const change = newVolume - currentVolume;

            document.getElementById('previewVolume').textContent = `${newVolume.toFixed(1)}L`;
            document.getElementById('previewChange').textContent = `${change >= 0 ? '+' : ''}${change.toFixed(1)}L`;
            document.getElementById('previewChange').className = `font-semibold ml-2 ${change >= 0 ? 'text-green-600' : 'text-red-600'}`;

            calculatedPreview.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Preview calculation failed:', error);
    }
}

function setLoadingState(loading) {
    submitBtn.disabled = loading;
    submitBtn.innerHTML = loading ?
        '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...' :
        '<i class="fas fa-save mr-2"></i>Update Reading';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize preview calculation
document.addEventListener('DOMContentLoaded', () => {
    calculatePreview();
});
</script>

<style>
/* Focus states and transitions */
.focus\:ring-2:focus {
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

/* Input validation states */
.border-red-500 {
    border-color: #ef4444;
    box-shadow: 0 0 0 1px #ef4444;
}

/* Smooth transitions */
input, select, textarea {
    transition: all 0.15s ease-in-out;
}

/* Button hover effects */
button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Loading animation */
.fa-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-1.md\:grid-cols-2 {
        grid-template-columns: 1fr;
    }

    .grid-cols-1.md\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
