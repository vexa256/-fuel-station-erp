@extends('layouts.app')

@section('title', 'Meter Reading Details')

@section('breadcrumb')
<nav aria-label="breadcrumb" class="mb-8">
    <ol class="flex items-center space-x-2 text-sm text-slate-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-slate-900 transition-colors">Dashboard</a></li>
        <li class="flex items-center"><svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></li>
        <li><a href="{{ route('continuous-meter.index') }}" class="hover:text-slate-900 transition-colors">Continuous Meter</a></li>
        <li class="flex items-center"><svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></li>
        <li class="text-slate-900 font-medium">Reading Details</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Meter Reading</h1>
            <div class="flex items-center space-x-4 text-sm text-slate-600">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ $meterReading->station_name }}
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Pump {{ $meterReading->pump_number }}
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($meterReading->reading_date)->format('M j, Y') }}
                </span>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            @if($isAutoApproved)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Auto-Approved
                </span>
            @endif
            <a href="{{ route('continuous-meter.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="relative p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Reading</p>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($meterReading->meter_reading_liters, 0) }}</p>
                    <p class="text-xs text-slate-500">Liters</p>
                </div>
            </div>
        </div>

        <div class="relative p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center">
                <div class="p-2 {{ $meterReading->meter_reset_occurred ? 'bg-orange-50' : 'bg-emerald-50' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $meterReading->meter_reset_occurred ? 'text-orange-600' : 'text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($meterReading->meter_reset_occurred)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        @endif
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Status</p>
                    <p class="text-lg font-bold {{ $meterReading->meter_reset_occurred ? 'text-orange-700' : 'text-emerald-700' }}">
                        {{ $meterReading->meter_reset_occurred ? 'Reset' : 'Normal' }}
                    </p>
                    <p class="text-xs text-slate-500">{{ $meterReading->reading_shift }} Shift</p>
                </div>
            </div>
        </div>

        <div class="relative p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center">
                <div class="p-2 bg-slate-50 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Operator</p>
                    <p class="text-lg font-bold text-slate-900">{{ $meterReading->first_name }} {{ $meterReading->last_name }}</p>
                    <p class="text-xs text-slate-500">{{ $meterReading->employee_number }}</p>
                </div>
            </div>
        </div>

        <div class="relative p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center">
                <div class="p-2 bg-slate-50 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Time</p>
                    <p class="text-lg font-bold text-slate-900">{{ \Carbon\Carbon::parse($meterReading->created_at)->format('H:i') }}</p>
                    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($meterReading->created_at)->format('M j') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Tabs -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex" aria-label="Tabs">
                <button onclick="showTab('details')" id="tab-details"
                        class="tab-btn relative px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none focus:text-blue-600 border-b-2 border-transparent data-[active=true]:border-blue-600 data-[active=true]:text-blue-600 transition-all">
                    Details
                </button>
                <button onclick="showTab('sequence')" id="tab-sequence"
                        class="tab-btn relative px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none focus:text-blue-600 border-b-2 border-transparent data-[active=true]:border-blue-600 data-[active=true]:text-blue-600 transition-all">
                    Sequence
                </button>
                <button onclick="showTab('consumption')" id="tab-consumption"
                        class="tab-btn relative px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none focus:text-blue-600 border-b-2 border-transparent data-[active=true]:border-blue-600 data-[active=true]:text-blue-600 transition-all">
                    Consumption
                </button>
                <button onclick="showTab('analysis')" id="tab-analysis"
                        class="tab-btn relative px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none focus:text-blue-600 border-b-2 border-transparent data-[active=true]:border-blue-600 data-[active=true]:text-blue-600 transition-all">
                    Analysis
                </button>
                <button onclick="showTab('audit')" id="tab-audit"
                        class="tab-btn relative px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none focus:text-blue-600 border-b-2 border-transparent data-[active=true]:border-blue-600 data-[active=true]:text-blue-600 transition-all">
                    Audit
                </button>
            </nav>
        </div>

        <div class="p-8">

            <!-- Details Tab -->
            <div id="content-details" class="tab-content space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Equipment -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-slate-900">Equipment</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Station</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->station_name }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Station Code</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->station_code }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Pump</span>
                                <span class="text-sm font-medium text-slate-900">#{{ $meterReading->pump_number }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Serial</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->pump_serial_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Meter Type</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->meter_type }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Tank</span>
                                <span class="text-sm font-medium text-slate-900">#{{ $meterReading->tank_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Product & Limits -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-slate-900">Product & Limits</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Product</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ $meterReading->product_name }}
                                </span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Code</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->product_code }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Type</span>
                                <span class="text-sm font-medium text-slate-900">{{ $meterReading->product_type }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Tank Capacity</span>
                                <span class="text-sm font-medium text-slate-900">{{ number_format($meterReading->capacity_liters, 0) }}L</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Meter Maximum</span>
                                <span class="text-sm font-medium text-slate-900">{{ number_format($meterReading->meter_maximum_reading, 0) }}L</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">Reset Threshold</span>
                                <span class="text-sm font-medium text-slate-900">{{ number_format($meterReading->meter_reset_threshold, 0) }}L</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($meterReading->meter_reset_occurred)
                <div class="p-6 bg-orange-50 border border-orange-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800">Meter Reset Detected</h3>
                            <div class="mt-2 text-sm text-orange-700">
                                <p>Pre-reset reading: <span class="font-medium">{{ number_format($meterReading->pre_reset_reading, 3) }}L</span></p>
                                <p class="mt-1">The meter automatically reset when it reached the configured threshold.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sequence Tab -->
            <div id="content-sequence" class="tab-content hidden space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Previous Reading -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-slate-900">Previous Reading</h3>
                        @if($relatedData['previous_reading'])
                        <div class="p-6 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Date</span>
                                    <span class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($relatedData['previous_reading']->reading_date)->format('M j, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Shift</span>
                                    <span class="text-sm font-medium text-slate-900">{{ $relatedData['previous_reading']->reading_shift }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Reading</span>
                                    <span class="text-lg font-bold text-slate-900">{{ number_format($relatedData['previous_reading']->meter_reading_liters, 3) }}L</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Time</span>
                                    <span class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($relatedData['previous_reading']->created_at)->format('H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="p-8 text-center bg-slate-50 rounded-lg border border-slate-200">
                            <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2"></path>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">No previous reading</p>
                        </div>
                        @endif

                        <!-- Next Reading -->
                        <h3 class="text-lg font-semibold text-slate-900 pt-4">Next Reading</h3>
                        @if($relatedData['next_reading'])
                        <div class="p-6 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Date</span>
                                    <span class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($relatedData['next_reading']->reading_date)->format('M j, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Shift</span>
                                    <span class="text-sm font-medium text-slate-900">{{ $relatedData['next_reading']->reading_shift }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Reading</span>
                                    <span class="text-lg font-bold text-slate-900">{{ number_format($relatedData['next_reading']->meter_reading_liters, 3) }}L</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Time</span>
                                    <span class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($relatedData['next_reading']->created_at)->format('H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="p-8 text-center bg-slate-50 rounded-lg border border-slate-200">
                            <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2"></path>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">No next reading</p>
                        </div>
                        @endif
                    </div>

                    <!-- Dip Readings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-slate-900">Dip Readings</h3>

                        <!-- Morning Dip -->
                        @if($relatedData['morning_dip'])
                        <div class="p-6 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-900 mb-3">Morning Dip</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Dip</span>
                                    <span class="text-sm font-bold text-blue-900">{{ number_format($relatedData['morning_dip']->dip_mm, 2) }}mm</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Volume</span>
                                    <span class="text-lg font-bold text-blue-900">{{ number_format($relatedData['morning_dip']->volume_liters, 3) }}L</span>
                                </div>
                                @if($relatedData['morning_dip']->temperature_celsius)
                                <div class="flex justify-between">
                                    <span class="text-sm text-blue-700">Temperature</span>
                                    <span class="text-sm font-medium text-blue-900">{{ $relatedData['morning_dip']->temperature_celsius }}°C</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="p-6 bg-slate-50 rounded-lg border border-slate-200">
                            <h4 class="text-sm font-medium text-slate-600 mb-2">Morning Dip</h4>
                            <p class="text-sm text-slate-500">No morning dip reading</p>
                        </div>
                        @endif

                        <!-- Evening Dip -->
                        @if($relatedData['evening_dip'])
                        <div class="p-6 bg-purple-50 rounded-lg border border-purple-200">
                            <h4 class="text-sm font-medium text-purple-900 mb-3">Evening Dip</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-700">Dip</span>
                                    <span class="text-sm font-bold text-purple-900">{{ number_format($relatedData['evening_dip']->dip_mm, 2) }}mm</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-700">Volume</span>
                                    <span class="text-lg font-bold text-purple-900">{{ number_format($relatedData['evening_dip']->volume_liters, 3) }}L</span>
                                </div>
                                @if($relatedData['evening_dip']->temperature_celsius)
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-700">Temperature</span>
                                    <span class="text-sm font-medium text-purple-900">{{ $relatedData['evening_dip']->temperature_celsius }}°C</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="p-6 bg-slate-50 rounded-lg border border-slate-200">
                            <h4 class="text-sm font-medium text-slate-600 mb-2">Evening Dip</h4>
                            <p class="text-sm text-slate-500">No evening dip reading</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Consumption Tab -->
            <div id="content-consumption" class="tab-content hidden space-y-6">
                @if($relatedData['fifo_consumption'] && $relatedData['fifo_consumption']->count() > 0)
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-700">Total Consumed</p>
                                    <p class="text-2xl font-bold text-blue-900">{{ number_format($relatedData['fifo_consumption']->sum('quantity_consumed_liters'), 0) }}L</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-emerald-50 rounded-lg border border-emerald-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-emerald-100 rounded-lg">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-emerald-700">Total Cost</p>
                                    <p class="text-2xl font-bold text-emerald-900">${{ number_format($relatedData['fifo_consumption']->sum('total_cost_consumed'), 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-amber-50 rounded-lg border border-amber-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-amber-100 rounded-lg">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-amber-700">Layers Used</p>
                                    <p class="text-2xl font-bold text-amber-900">{{ $relatedData['fifo_consumption']->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FIFO Table -->
                    <div class="overflow-hidden border border-slate-200 rounded-lg">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Layer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Consumed</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Cost/L</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($relatedData['fifo_consumption'] as $consumption)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            #{{ $consumption->layer_sequence_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                        {{ $consumption->delivery_batch_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 text-right">
                                        {{ number_format($consumption->quantity_consumed_liters, 3) }}L
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 text-right">
                                        ${{ number_format($consumption->cost_per_liter, 4) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 text-right">
                                        ${{ number_format($consumption->total_cost_consumed, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 text-right">
                                        {{ number_format($consumption->layer_balance_after_liters, 3) }}L
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">No consumption data</h3>
                    <p class="mt-2 text-sm text-slate-500">No FIFO layers were consumed for this reading.</p>
                </div>
                @endif
            </div>

            <!-- Analysis Tab -->
            <div id="content-analysis" class="tab-content hidden space-y-6">
                @if($relatedData['variances'] && $relatedData['variances']->count() > 0)
                <div class="space-y-6">
                    @foreach($relatedData['variances'] as $variance)
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="p-6 bg-white">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $riskColors = [
                                            'CRITICAL' => 'bg-red-100 text-red-800 border-red-200',
                                            'HIGH' => 'bg-orange-100 text-orange-800 border-orange-200',
                                            'MEDIUM' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'LOW' => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        ];
                                        $riskColor = $riskColors[$variance->risk_level ?? 'LOW'] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $riskColor }}">
                                        {{ $variance->risk_level ?? 'Unknown' }}
                                    </span>
                                    <div>
                                        <h4 class="text-lg font-semibold text-slate-900">{{ $variance->variance_type ?? 'Variance' }}</h4>
                                        <p class="text-sm text-slate-500">{{ $variance->variance_category ?? 'Analysis' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold {{ ($variance->calculated_variance_percentage ?? 0) >= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ number_format($variance->calculated_variance_percentage ?? 0, 2) }}%
                                    </p>
                                    <p class="text-sm text-slate-500">{{ number_format(abs($variance->calculated_variance_liters ?? 0), 0) }}L</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Financial Impact -->
                                <div>
                                    <h5 class="text-sm font-semibold text-slate-900 mb-4">Financial Impact</h5>
                                    <div class="space-y-3">
                                        @if(isset($variance->financial_impact_cost))
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600">Cost Impact</span>
                                            <span class="text-sm font-medium text-slate-900">${{ number_format($variance->financial_impact_cost, 2) }}</span>
                                        </div>
                                        @endif
                                        @if(isset($variance->financial_impact_revenue))
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600">Revenue Impact</span>
                                            <span class="text-sm font-medium text-slate-900">${{ number_format($variance->financial_impact_revenue, 2) }}</span>
                                        </div>
                                        @endif
                                        @if(isset($variance->financial_impact_net))
                                        <div class="flex justify-between pt-3 border-t border-slate-200">
                                            <span class="text-sm font-medium text-slate-600">Net Impact</span>
                                            <span class="text-sm font-bold {{ $variance->financial_impact_net >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                                ${{ number_format($variance->financial_impact_net, 2) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Risk Factors -->
                                <div>
                                    <h5 class="text-sm font-semibold text-slate-900 mb-4">Risk Assessment</h5>
                                    <div class="space-y-3">
                                        @if(isset($variance->theft_probability_score))
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600">Theft Risk</span>
                                            <span class="text-sm font-medium text-slate-900">{{ number_format($variance->theft_probability_score, 1) }}%</span>
                                        </div>
                                        @endif
                                        @if(isset($variance->equipment_fault_probability))
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600">Equipment Fault</span>
                                            <span class="text-sm font-medium text-slate-900">{{ number_format($variance->equipment_fault_probability, 1) }}%</span>
                                        </div>
                                        @endif
                                        @if(isset($variance->statistical_significance))
                                        <div class="flex justify-between pt-3 border-t border-slate-200">
                                            <span class="text-sm font-medium text-slate-600">Significance</span>
                                            <span class="text-sm font-medium text-slate-900">{{ $variance->statistical_significance }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">No variances detected</h3>
                    <p class="mt-2 text-sm text-slate-500">This reading passed all variance checks.</p>
                </div>
                @endif
            </div>

            <!-- Audit Tab -->
            <div id="content-audit" class="tab-content hidden space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Reading Created</p>
                                <p class="text-xs text-slate-500">Initial meter reading entry</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($meterReading->created_at)->format('M j, Y H:i:s') }}</p>
                            <p class="text-xs text-slate-500">{{ $meterReading->first_name }} {{ $meterReading->last_name }}</p>
                        </div>
                    </div>

                    @if($meterReading->meter_reset_occurred)
                    <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg border border-orange-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-orange-800">Meter Reset Detected</p>
                                <p class="text-xs text-orange-600">Automatic validation completed</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-orange-800">{{ number_format($meterReading->pre_reset_reading, 0) }}L</p>
                            <p class="text-xs text-orange-600">Pre-reset reading</p>
                        </div>
                    </div>
                    @endif

                    @if($relatedData['fifo_consumption'] && $relatedData['fifo_consumption']->count() > 0)
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-blue-800">FIFO Consumption</p>
                                <p class="text-xs text-blue-600">{{ $relatedData['fifo_consumption']->count() }} layer(s) processed</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-blue-800">{{ number_format($relatedData['fifo_consumption']->sum('quantity_consumed_liters'), 0) }}L</p>
                            <p class="text-xs text-blue-600">${{ number_format($relatedData['fifo_consumption']->sum('total_cost_consumed'), 2) }}</p>
                        </div>
                    </div>
                    @endif

                    @if($relatedData['variances'] && $relatedData['variances']->count() > 0)
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-red-800">Variance Detection</p>
                                <p class="text-xs text-red-600">{{ $relatedData['variances']->count() }} variance(s) flagged</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-800">Investigation Required</p>
                            <p class="text-xs text-red-600">Auto-flagged for review</p>
                        </div>
                    </div>
                    @endif

                    @if($isAutoApproved)
                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-medium text-emerald-800">Auto-Approved</p>
                                <p class="text-xs text-emerald-600">{{ auth()->user()->role }} role override</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-emerald-800">Instant Approval</p>
                            <p class="text-xs text-emerald-600">No manual review required</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Reset all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.setAttribute('data-active', 'false');
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-slate-500');
    });

    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.setAttribute('data-active', 'true');
    activeTab.classList.remove('border-transparent', 'text-slate-500');
    activeTab.classList.add('border-blue-600', 'text-blue-600');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    showTab('details');
});

// Notifications
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK'
    });
}

@if($isAutoApproved)
function showAutoApproval(message) {
    Swal.fire({
        icon: 'success',
        title: ' Auto-Approved by Role',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}
@endif
</script>
@endsection
