@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50" x-data="deliveryReceiptData()">

    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('deliveries.show', $delivery->id) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-slate-900">Delivery Receipt</h1>
                            <p class="text-sm text-slate-500">{{ $delivery->delivery_note_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button onclick="window.print()"
                            class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm bg-white hover:bg-slate-50 transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Print Receipt
                    </button>

                    <button @click="downloadPDF()"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">

            <!-- Receipt Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-8 py-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">DELIVERY RECEIPT</h1>
                        <p class="text-green-100 mt-1">Fuel Delivery Confirmation</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold">{{ $delivery->delivery_note_number }}</p>
                        <p class="text-green-100">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Company/Station Header -->
            <div class="px-8 py-6 border-b border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Delivery To:</h2>
                        <div class="space-y-1">
                            <p class="text-slate-900 font-medium">{{ $delivery->station_name }}</p>
                            <p class="text-slate-600">Tank {{ $delivery->tank_number }}</p>
                            <p class="text-slate-600">Capacity: {{ number_format($delivery->capacity_liters, 0) }} Liters</p>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Supplied By:</h2>
                        <div class="space-y-1">
                            <p class="text-slate-900 font-medium">{{ $delivery->company_name }}</p>
                            <p class="text-slate-600">Supplier Code: {{ $delivery->supplier_code }}</p>
                            @if(isset($delivery->supplier_invoice_reference) && $delivery->supplier_invoice_reference)
                            <p class="text-slate-600">Invoice: {{ $delivery->supplier_invoice_reference }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Details -->
            <div class="px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Delivery Information -->
                    <div class="bg-slate-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Delivery Details</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Date:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('M j, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Time:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($delivery->delivery_time)->format('g:i A') }}</span>
                            </div>
                            @if(isset($delivery->driver_name) && $delivery->driver_name)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Driver:</span>
                                <span class="font-medium">{{ $delivery->driver_name }}</span>
                            </div>
                            @endif
                            @if(isset($delivery->vehicle_registration) && $delivery->vehicle_registration)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Vehicle:</span>
                                <span class="font-medium">{{ $delivery->vehicle_registration }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quantity Summary -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Quantity Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Ordered:</span>
                                <span class="font-medium">{{ number_format($delivery->quantity_ordered_liters, 3) }}L</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Delivered:</span>
                                <span class="font-bold text-blue-700">{{ number_format($delivery->quantity_delivered_liters, 3) }}L</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Variance:</span>
                                <span class="font-medium"
                                      :class="{
                                          'text-red-600': Math.abs({{ $delivery->variance_percentage }}) > 1,
                                          'text-amber-600': Math.abs({{ $delivery->variance_percentage }}) > 0.5 && Math.abs({{ $delivery->variance_percentage }}) <= 1,
                                          'text-green-600': Math.abs({{ $delivery->variance_percentage }}) <= 0.5
                                      }">
                                    {{ number_format($delivery->variance_percentage, 2) }}%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Variance Vol:</span>
                                <span class="font-medium">{{ number_format($delivery->quantity_variance_liters, 3) }}L</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Financial Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Cost/Liter:</span>
                                <span class="font-medium">UGX {{ number_format($delivery->cost_per_liter, 4) }}</span>
                            </div>
                            @if(isset($delivery->transport_cost_per_liter) && $delivery->transport_cost_per_liter > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Transport:</span>
                                <span class="font-medium">UGX {{ number_format($delivery->transport_cost_per_liter, 4) }}</span>
                            </div>
                            @endif
                            @if(isset($delivery->handling_cost_per_liter) && $delivery->handling_cost_per_liter > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Handling:</span>
                                <span class="font-medium">UGX {{ number_format($delivery->handling_cost_per_liter, 4) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between border-t border-green-200 pt-2">
                                <span class="text-slate-700 font-semibold">Total Cost:</span>
                                <span class="font-bold text-green-700">UGX {{ number_format($delivery->total_delivery_cost, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Temperature & Quality Data -->
                @if((isset($delivery->loading_temperature_celsius) && $delivery->loading_temperature_celsius) ||
                    (isset($delivery->delivery_temperature_celsius) && $delivery->delivery_temperature_celsius) ||
                    (isset($delivery->density_at_15c) && $delivery->density_at_15c) ||
                    (isset($delivery->water_content_ppm) && $delivery->water_content_ppm))
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Temperature & Quality Analysis</h3>
                    <div class="bg-amber-50 rounded-lg p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if(isset($delivery->loading_temperature_celsius) && $delivery->loading_temperature_celsius)
                            <div class="text-center">
                                <p class="text-sm text-slate-600 mb-1">Loading Temperature</p>
                                <p class="text-lg font-bold text-amber-700">{{ $delivery->loading_temperature_celsius }}°C</p>
                            </div>
                            @endif

                            @if(isset($delivery->delivery_temperature_celsius) && $delivery->delivery_temperature_celsius)
                            <div class="text-center">
                                <p class="text-sm text-slate-600 mb-1">Delivery Temperature</p>
                                <p class="text-lg font-bold text-amber-700">{{ $delivery->delivery_temperature_celsius }}°C</p>
                            </div>
                            @endif

                            @if(isset($delivery->density_at_15c) && $delivery->density_at_15c)
                            <div class="text-center">
                                <p class="text-sm text-slate-600 mb-1">Density at 15°C</p>
                                <p class="text-lg font-bold text-amber-700">{{ $delivery->density_at_15c }}</p>
                            </div>
                            @endif

                            @if(isset($delivery->water_content_ppm) && $delivery->water_content_ppm)
                            <div class="text-center">
                                <p class="text-sm text-slate-600 mb-1">Water Content</p>
                                <p class="text-lg font-bold text-amber-700">{{ $delivery->water_content_ppm }} ppm</p>
                            </div>
                            @endif
                        </div>

                        @if((isset($delivery->temperature_variance_celsius) && $delivery->temperature_variance_celsius) ||
                            (isset($delivery->volume_correction_factor) && $delivery->volume_correction_factor != 1))
                        <div class="mt-4 pt-4 border-t border-amber-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if(isset($delivery->temperature_variance_celsius) && $delivery->temperature_variance_celsius)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Temperature Variance:</span>
                                    <span class="font-semibold">{{ $delivery->temperature_variance_celsius }}°C</span>
                                </div>
                                @endif

                                @if(isset($delivery->volume_correction_factor) && $delivery->volume_correction_factor != 1)
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Volume Correction Factor:</span>
                                    <span class="font-semibold">{{ $delivery->volume_correction_factor }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quality Test Results -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Quality Assessment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Quality Status -->
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-slate-700">Quality Test Status</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                      :class="{
                                          'bg-green-100 text-green-800': {{ $delivery->quality_test_passed ? 'true' : 'false' }},
                                          'bg-red-100 text-red-800': {{ $delivery->quality_test_passed ? 'false' : 'true' }}
                                      }">
                                    <i :class="{
                                        'fas fa-check-circle mr-1': {{ $delivery->quality_test_passed ? 'true' : 'false' }},
                                        'fas fa-times-circle mr-1': {{ $delivery->quality_test_passed ? 'false' : 'true' }}
                                    }"></i>
                                    {{ $delivery->quality_test_passed ? 'PASSED' : 'FAILED' }}
                                </span>
                            </div>

                            @if(!$delivery->quality_test_passed && isset($delivery->quality_failure_reason) && $delivery->quality_failure_reason)
                            <div class="mt-3 p-3 bg-red-50 rounded border-l-4 border-red-400">
                                <p class="text-sm font-medium text-red-800">Failure Reason:</p>
                                <p class="text-sm text-red-700 mt-1">{{ $delivery->quality_failure_reason }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Seal Information -->
                        @if((isset($delivery->seal_number_1) && $delivery->seal_number_1) ||
                            (isset($delivery->seal_number_2) && $delivery->seal_number_2) ||
                            (isset($delivery->seal_number_3) && $delivery->seal_number_3))
                        <div class="bg-slate-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-slate-700 mb-3">Security Seals</h4>
                            <div class="space-y-2">
                                @if(isset($delivery->seal_number_1) && $delivery->seal_number_1)
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-lock text-blue-600"></i>
                                    <span class="font-mono text-sm">{{ $delivery->seal_number_1 }}</span>
                                </div>
                                @endif
                                @if(isset($delivery->seal_number_2) && $delivery->seal_number_2)
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-lock text-blue-600"></i>
                                    <span class="font-mono text-sm">{{ $delivery->seal_number_2 }}</span>
                                </div>
                                @endif
                                @if(isset($delivery->seal_number_3) && $delivery->seal_number_3)
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-lock text-blue-600"></i>
                                    <span class="font-mono text-sm">{{ $delivery->seal_number_3 }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Certification & Signatures -->
                <div class="border-t border-slate-200 pt-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-6">Certification & Approval</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Verified By -->
                        <div class="text-center">
                            <div class="h-16 border-b-2 border-slate-300 mb-2"></div>
                            <p class="text-sm font-medium text-slate-700">VERIFIED BY</p>
                            <p class="text-xs text-slate-500 mt-1">Station Representative</p>
                            <p class="text-xs text-slate-400 mt-1">Date: {{ \Carbon\Carbon::now()->format('M j, Y') }}</p>
                        </div>

                        <!-- Witnessed By -->
                        <div class="text-center">
                            <div class="h-16 border-b-2 border-slate-300 mb-2"></div>
                            <p class="text-sm font-medium text-slate-700">WITNESSED BY</p>
                            <p class="text-xs text-slate-500 mt-1">Driver/Operator</p>
                            <p class="text-xs text-slate-400 mt-1">Date: {{ \Carbon\Carbon::now()->format('M j, Y') }}</p>
                        </div>

                        <!-- Approved By -->
                        <div class="text-center">
                            <div class="h-16 border-b-2 border-slate-300 mb-2"></div>
                            <p class="text-sm font-medium text-slate-700">APPROVED BY</p>
                            <p class="text-xs text-slate-500 mt-1">Supervisor</p>
                            <p class="text-xs text-slate-400 mt-1">Date: {{ \Carbon\Carbon::now()->format('M j, Y') }}</p>
                        </div>
                    </div>

                    <!-- Certification Statement -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                        <p class="text-sm text-blue-800 leading-relaxed">
                            <strong>CERTIFICATION:</strong> This receipt certifies that the above-mentioned quantity of fuel has been delivered to the specified tank in accordance with safety protocols and quality standards. All measurements have been verified and the delivery has been completed satisfactorily.
                        </p>
                    </div>
                </div>

                <!-- Footer Information -->
                <div class="mt-8 pt-6 border-t border-slate-200 text-center">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-500">
                        <div>
                            <p><strong>Receipt Generated:</strong> {{ \Carbon\Carbon::now()->format('M j, Y g:i A') }}</p>
                            <p><strong>System Reference:</strong> {{ $delivery->delivery_note_number }}-{{ \Carbon\Carbon::now()->format('Ymd') }}</p>
                        </div>
                        <div>
                            <p><strong>Delivery Status:</strong> {{ ucfirst(strtolower($delivery->delivery_status)) }}</p>
                            @if(isset($delivery->po_number) && $delivery->po_number)
                            <p><strong>Purchase Order:</strong> {{ $delivery->po_number }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-xs text-slate-400">
                            This is a system-generated receipt. For any discrepancies or queries, please contact the station manager within 24 hours of delivery.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deliveryReceiptData() {
    return {
        delivery: @json($delivery),

        init() {
            // Auto-focus for print if needed
        },

        downloadPDF() {
            // Trigger PDF download
            const params = new URLSearchParams();
            params.append('format', 'pdf');

            window.open(`{{ route('deliveries.receipt', $delivery->id) }}?${params.toString()}`, '_blank');
        },

        formatNumber(num) {
            return parseFloat(num || 0).toLocaleString(undefined, {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });
        }
    }
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    .print-break {
        page-break-after: always;
    }

    body {
        background: white !important;
    }

    .bg-gradient-to-r {
        background: #059669 !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection
