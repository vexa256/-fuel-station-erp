@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50" x-data="deliveryShowData()">

    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('deliveries.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-truck text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-slate-900">Delivery {{ $delivery->delivery_note_number }}</h1>
                            <p class="text-sm text-slate-500">{{ $delivery->station_name }} - Tank {{ $delivery->tank_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          :class="{
                              'bg-green-100 text-green-800': '{{ $delivery->delivery_status }}' === 'COMPLETED',
                              'bg-amber-100 text-amber-800': '{{ $delivery->delivery_status }}' === 'PENDING',
                              'bg-red-100 text-red-800': '{{ $delivery->delivery_status }}' === 'REJECTED',
                              'bg-blue-100 text-blue-800': '{{ $delivery->delivery_status }}' === 'IN_TRANSIT'
                          }">
                        <i class="fas fa-circle mr-2 text-xs"></i>
                        {{ ucfirst(strtolower(str_replace('_', ' ', $delivery->delivery_status))) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Delivery Details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Basic Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Delivery Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Note Number</label>
                                <p class="text-slate-900 font-mono">{{ $delivery->delivery_note_number }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Supplier</label>
                                <p class="text-slate-900">{{ $delivery->company_name }}</p>
                                <p class="text-sm text-slate-500">Code: {{ $delivery->supplier_code }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Date & Time</label>
                                <p class="text-slate-900">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('M j, Y') }}</p>
                                <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($delivery->delivery_time)->format('g:i A') }}</p>
                            </div>

                            @if($delivery->scheduled_date)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date & Time</label>
                                <p class="text-slate-900">{{ \Carbon\Carbon::parse($delivery->scheduled_date)->format('M j, Y') }}</p>
                                @if($delivery->scheduled_time)
                                <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($delivery->scheduled_time)->format('g:i A') }}</p>
                                @endif
                            </div>
                            @endif

                            @if($delivery->po_number)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Order</label>
                                <p class="text-slate-900">{{ $delivery->po_number }}</p>
                            </div>
                            @endif

                            @if($delivery->supplier_invoice_reference)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Supplier Invoice Reference</label>
                                <p class="text-slate-900 font-mono">{{ $delivery->supplier_invoice_reference }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quantity & Financial Details -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Quantity & Financial Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-700 mb-1">Ordered Quantity</p>
                                <p class="text-2xl font-bold text-blue-900">{{ number_format($delivery->quantity_ordered_liters, 3) }}</p>
                                <p class="text-sm text-blue-600">Liters</p>
                            </div>

                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-sm font-medium text-green-700 mb-1">Delivered Quantity</p>
                                <p class="text-2xl font-bold text-green-900">{{ number_format($delivery->quantity_delivered_liters, 3) }}</p>
                                <p class="text-sm text-green-600">Liters</p>
                            </div>

                            <div class="text-center p-4 rounded-lg"
                                 :class="{
                                     'bg-red-50': Math.abs({{ $delivery->variance_percentage }}) > 1,
                                     'bg-amber-50': Math.abs({{ $delivery->variance_percentage }}) > 0.5 && Math.abs({{ $delivery->variance_percentage }}) <= 1,
                                     'bg-green-50': Math.abs({{ $delivery->variance_percentage }}) <= 0.5
                                 }">
                                <p class="text-sm font-medium mb-1"
                                   :class="{
                                       'text-red-700': Math.abs({{ $delivery->variance_percentage }}) > 1,
                                       'text-amber-700': Math.abs({{ $delivery->variance_percentage }}) > 0.5 && Math.abs({{ $delivery->variance_percentage }}) <= 1,
                                       'text-green-700': Math.abs({{ $delivery->variance_percentage }}) <= 0.5
                                   }">Variance</p>
                                <p class="text-2xl font-bold"
                                   :class="{
                                       'text-red-900': Math.abs({{ $delivery->variance_percentage }}) > 1,
                                       'text-amber-900': Math.abs({{ $delivery->variance_percentage }}) > 0.5 && Math.abs({{ $delivery->variance_percentage }}) <= 1,
                                       'text-green-900': Math.abs({{ $delivery->variance_percentage }}) <= 0.5
                                   }">{{ number_format($delivery->variance_percentage, 3) }}%</p>
                                <p class="text-sm"
                                   :class="{
                                       'text-red-600': Math.abs({{ $delivery->variance_percentage }}) > 1,
                                       'text-amber-600': Math.abs({{ $delivery->variance_percentage }}) > 0.5 && Math.abs({{ $delivery->variance_percentage }}) <= 1,
                                       'text-green-600': Math.abs({{ $delivery->variance_percentage }}) <= 0.5
                                   }">{{ number_format($delivery->quantity_variance_liters, 3) }}L</p>
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <h3 class="text-sm font-medium text-slate-700 mb-3">Cost Breakdown</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-slate-600">Base Cost per Liter</p>
                                    <p class="text-lg font-semibold text-slate-900">UGX {{ number_format($delivery->cost_per_liter, 4) }}</p>
                                </div>

                                @if($delivery->transport_cost_per_liter > 0)
                                <div>
                                    <p class="text-sm text-slate-600">Transport Cost per Liter</p>
                                    <p class="text-lg font-semibold text-slate-900">UGX {{ number_format($delivery->transport_cost_per_liter, 4) }}</p>
                                </div>
                                @endif

                                @if($delivery->handling_cost_per_liter > 0)
                                <div>
                                    <p class="text-sm text-slate-600">Handling Cost per Liter</p>
                                    <p class="text-lg font-semibold text-slate-900">UGX {{ number_format($delivery->handling_cost_per_liter, 4) }}</p>
                                </div>
                                @endif

                                <div>
                                    <p class="text-sm text-slate-600">Total Delivery Cost</p>
                                    <p class="text-xl font-bold text-green-700">UGX {{ number_format($delivery->total_delivery_cost, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle & Driver Information -->
                @if($delivery->driver_name || $delivery->vehicle_registration)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Vehicle & Driver Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($delivery->driver_name)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Driver Name</label>
                                <p class="text-slate-900">{{ $delivery->driver_name }}</p>
                                @if($delivery->driver_license)
                                <p class="text-sm text-slate-500">License: {{ $delivery->driver_license }}</p>
                                @endif
                            </div>
                            @endif

                            @if($delivery->vehicle_registration)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Vehicle Registration</label>
                                <p class="text-slate-900 font-mono">{{ $delivery->vehicle_registration }}</p>
                                <p class="text-sm text-slate-500">Type: {{ ucfirst(strtolower($delivery->vehicle_type)) }}</p>
                                @if($delivery->compartment_count)
                                <p class="text-sm text-slate-500">Compartments: {{ $delivery->compartment_count }}</p>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Seal Numbers -->
                        @if($delivery->seal_number_1 || $delivery->seal_number_2 || $delivery->seal_number_3)
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <h3 class="text-sm font-medium text-slate-700 mb-3">Seal Numbers</h3>
                            <div class="flex flex-wrap gap-2">
                                @if($delivery->seal_number_1)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    <i class="fas fa-lock mr-1"></i>
                                    {{ $delivery->seal_number_1 }}
                                </span>
                                @endif
                                @if($delivery->seal_number_2)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    <i class="fas fa-lock mr-1"></i>
                                    {{ $delivery->seal_number_2 }}
                                </span>
                                @endif
                                @if($delivery->seal_number_3)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    <i class="fas fa-lock mr-1"></i>
                                    {{ $delivery->seal_number_3 }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quality & Temperature Data -->
                @if($delivery->loading_temperature_celsius || $delivery->delivery_temperature_celsius || $delivery->density_at_15c || $delivery->water_content_ppm)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Quality & Temperature Data</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if($delivery->loading_temperature_celsius)
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-700">Loading Temp</p>
                                <p class="text-lg font-bold text-blue-900">{{ $delivery->loading_temperature_celsius }}°C</p>
                            </div>
                            @endif

                            @if($delivery->delivery_temperature_celsius)
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-sm font-medium text-green-700">Delivery Temp</p>
                                <p class="text-lg font-bold text-green-900">{{ $delivery->delivery_temperature_celsius }}°C</p>
                            </div>
                            @endif

                            @if($delivery->density_at_15c)
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <p class="text-sm font-medium text-purple-700">Density at 15°C</p>
                                <p class="text-lg font-bold text-purple-900">{{ $delivery->density_at_15c }}</p>
                            </div>
                            @endif

                            @if($delivery->water_content_ppm)
                            <div class="text-center p-3 bg-amber-50 rounded-lg">
                                <p class="text-sm font-medium text-amber-700">Water Content</p>
                                <p class="text-lg font-bold text-amber-900">{{ $delivery->water_content_ppm }} ppm</p>
                            </div>
                            @endif
                        </div>

                        @if($delivery->temperature_variance_celsius || $delivery->volume_correction_factor != 1 || $delivery->corrected_volume_liters)
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <h3 class="text-sm font-medium text-slate-700 mb-3">Temperature Corrections</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @if($delivery->temperature_variance_celsius)
                                <div>
                                    <p class="text-sm text-slate-600">Temperature Variance</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $delivery->temperature_variance_celsius }}°C</p>
                                </div>
                                @endif

                                @if($delivery->volume_correction_factor != 1)
                                <div>
                                    <p class="text-sm text-slate-600">Correction Factor</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $delivery->volume_correction_factor }}</p>
                                </div>
                                @endif

                                @if($delivery->corrected_volume_liters)
                                <div>
                                    <p class="text-sm text-slate-600">Corrected Volume</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ number_format($delivery->corrected_volume_liters, 3) }}L</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Compartments -->
                @if(isset($compartments) && $compartments->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Delivery Compartments</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Compartment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Temperature</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Seal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($compartments as $compartment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                        #{{ $compartment->compartment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ str_replace('_', ' ', $compartment->product_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ number_format($compartment->quantity_liters, 3) }}L
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        @if($compartment->temperature_celsius)
                                            {{ $compartment->temperature_celsius }}°C
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        @if($compartment->seal_number)
                                            <span class="font-mono">{{ $compartment->seal_number }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-800': '{{ $compartment->compartment_status }}' === 'DISCHARGED',
                                                  'bg-blue-100 text-blue-800': '{{ $compartment->compartment_status }}' === 'SEALED',
                                                  'bg-amber-100 text-amber-800': '{{ $compartment->compartment_status }}' === 'OPENED',
                                                  'bg-gray-100 text-gray-800': '{{ $compartment->compartment_status }}' === 'CLEANED'
                                              }">
                                            {{ ucfirst(strtolower($compartment->compartment_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Actions</h3>
                    <div class="space-y-3">

                        @if($delivery->delivery_status === 'COMPLETED' && $delivery->has_receipt)
                        <a href="{{ route('deliveries.receipt', $delivery->id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500/50 focus:outline-none transition-colors">
                            <i class="fas fa-receipt"></i>
                            View Receipt
                        </a>
                        @elseif($delivery->delivery_status === 'COMPLETED' && !$delivery->has_receipt)
                        <a href="{{ route('delivery-receipts.create', ['delivery_id' => $delivery->id]) }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500/50 focus:outline-none transition-colors">
                            <i class="fas fa-plus"></i>
                            Create Receipt
                        </a>
                        @elseif($delivery->delivery_status === 'PENDING')
                        <div class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-100 text-amber-800 text-sm font-medium rounded-md">
                            <i class="fas fa-clock"></i>
                            Delivery Pending
                        </div>
                        @endif

                        <button onclick="window.print()"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-slate-500/50 focus:outline-none transition-colors">
                            <i class="fas fa-print"></i>
                            Print Delivery
                        </button>

                        {{-- @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
                        <a href="{{ route('deliveries.edit', $delivery->id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-slate-500/50 focus:outline-none transition-colors">
                            <i class="fas fa-edit"></i>
                            Edit Delivery
                        </a>
                        @endif --}}
                    </div>
                </div>

                <!-- Tank & Station Info -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Destination Details</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Station</p>
                            <p class="text-slate-900">{{ $delivery->station_name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-slate-700">Tank</p>
                            <p class="text-slate-900">Tank {{ $delivery->tank_number }}</p>
                            <p class="text-sm text-slate-500">Capacity: {{ number_format($delivery->capacity_liters, 0) }}L</p>
                        </div>
                    </div>
                </div>

                <!-- Quality Status -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Quality Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Quality Test</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :class="{
                                      'bg-green-100 text-green-800': {{ $delivery->quality_test_passed ? 'true' : 'false' }},
                                      'bg-red-100 text-red-800': {{ $delivery->quality_test_passed ? 'false' : 'true' }}
                                  }">
                                {{ $delivery->quality_test_passed ? 'Passed' : 'Failed' }}
                            </span>
                        </div>

                        @if(!$delivery->quality_test_passed && $delivery->quality_failure_reason)
                        <div class="p-3 bg-red-50 rounded-lg">
                            <p class="text-sm font-medium text-red-800 mb-1">Failure Reason</p>
                            <p class="text-sm text-red-700">{{ $delivery->quality_failure_reason }}</p>
                        </div>
                        @endif

                        @if($delivery->delivery_status === 'REJECTED' && $delivery->rejection_reason)
                        <div class="p-3 bg-red-50 rounded-lg">
                            <p class="text-sm font-medium text-red-800 mb-1">Rejection Reason</p>
                            <p class="text-sm text-red-700">{{ $delivery->rejection_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Inventory Layer Info -->
                @if(isset($inventoryLayer) && $inventoryLayer)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">FIFO Layer</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Layer #</span>
                            <span class="font-semibold">{{ $inventoryLayer->layer_sequence_number }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Current Quantity</span>
                            <span class="font-semibold">{{ number_format($inventoryLayer->current_quantity_liters, 3) }}L</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Consumed</span>
                            <span class="font-semibold">{{ number_format($inventoryLayer->consumed_quantity_liters, 3) }}L</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Cost per Liter</span>
                            <span class="font-semibold">UGX {{ number_format($inventoryLayer->cost_per_liter, 4) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Remaining Value</span>
                            <span class="font-semibold text-green-600">UGX {{ number_format($inventoryLayer->remaining_layer_value, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-700">Status</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  :class="{
                                      'bg-green-100 text-green-800': '{{ $inventoryLayer->layer_status }}' === 'ACTIVE',
                                      'bg-blue-100 text-blue-800': '{{ $inventoryLayer->layer_status }}' === 'CONSUMING',
                                      'bg-gray-100 text-gray-800': '{{ $inventoryLayer->layer_status }}' === 'DEPLETED'
                                  }">
                                {{ ucfirst(strtolower($inventoryLayer->layer_status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Delivery Created</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($delivery->created_at)->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>

                        @if($delivery->delivery_status === 'COMPLETED')
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Delivery Completed</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($delivery->updated_at)->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($delivery->has_receipt && $delivery->receipt_data)
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-purple-600 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Receipt Generated</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($delivery->receipt_data->receipt_timestamp)->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Status Info (if available) -->
    @if($delivery->has_receipt && $delivery->receipt_data)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-green-800">Delivery Receipt Available</h4>
                    <p class="text-sm text-green-600">
                        Receipt generated on {{ \Carbon\Carbon::parse($delivery->receipt_data->receipt_timestamp)->format('M j, Y g:i A') }}
                        @if($delivery->receipt_data->sample_reference_number)
                            | Sample: {{ $delivery->receipt_data->sample_reference_number }}
                        @endif
                        @if($delivery->receipt_data->verified_by)
                            | Verified by authorized personnel
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function deliveryShowData() {
    return {
        delivery: @json($delivery),
        inventoryLayer: @json($inventoryLayer ?? null),
        compartments: @json($compartments ?? []),

        init() {
            // Any initialization code
        },

        formatNumber(num) {
            return parseFloat(num || 0).toLocaleString(undefined, {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });
        },

        formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
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
}
</style>
@endsection
