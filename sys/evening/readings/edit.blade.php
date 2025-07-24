@extends('layouts.app')

@section('title', 'Edit Evening Reading')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb flex items-center space-x-2 text-sm text-gray-600">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 transition-colors">Dashboard</a>
        </li>
        <li class="breadcrumb-item text-gray-400">/</li>
        <li class="breadcrumb-item">
            <a href="{{ route('evening.readings.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">Evening Readings</a>
        </li>
        <li class="breadcrumb-item text-gray-400">/</li>
        <li class="breadcrumb-item active text-gray-900 font-medium">Edit Reading</li>
    </ol>
</nav>
@endsection

@section('page-header')
<div class="page-header bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center space-x-4">
            <div class="bg-orange-500 rounded-full p-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Evening Reading</h1>
                <p class="text-gray-600">
                    {{ $reading->station_name ?? 'Unknown Station' }} - Tank {{ $reading->tank_number ?? 'N/A' }}
                    ({{ $reading->product_name ?? 'Unknown Product' }}) - {{ date('F d, Y', strtotime($reading->reading_date)) }}
                </p>
            </div>
        </div>

        <div class="mt-4 lg:mt-0 flex items-center space-x-3">
            <!-- CEO Auto-Approval Status -->
            @if($isAutoApproved ?? false)
                <div class="flex items-center space-x-2 bg-amber-100 px-3 py-2 rounded-full">
                    <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                    <span class="text-amber-800 font-medium text-sm">👑 CEO Edit Access</span>
                </div>
            @else
                <div class="flex items-center space-x-2 bg-red-100 px-3 py-2 rounded-full">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-red-800 font-medium text-sm">🔒 Restricted Edit</span>
                </div>
            @endif

            <!-- Reading Status -->
            <div class="flex items-center space-x-2 bg-blue-100 px-3 py-2 rounded-full">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <span class="text-blue-800 font-medium text-sm">
                    Status: {{ ucfirst(strtolower($reading->reading_status ?? 'COMPLETED')) }}
                </span>
            </div>

            <!-- Back Button -->
            <a href="{{ route('evening.readings.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <!-- CEO Auto-Approval Notice -->
    @if($isAutoApproved ?? false)
        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-400 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <div class="bg-amber-400 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-amber-800 font-medium"> Executive Edit Access Active</p>
                    <p class="text-amber-700 text-sm">Your {{ auth()->user()->role }} role enables editing of evening readings with automatic approval.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Permission Restriction Notice -->
    @if(!($isAutoApproved ?? false))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <div class="bg-red-400 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-red-800 font-medium">🔒 Restricted Edit Access</p>
                    <p class="text-red-700 text-sm">Only CEO and SYSTEM_ADMIN roles can edit evening readings. All changes require approval.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Original Reading Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Original Reading Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="bg-blue-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Original Reading Details</h3>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Reading ID</p>
                    <p class="font-medium text-gray-900">#{{ $reading->id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Reading Date</p>
                    <p class="font-medium text-gray-900">{{ date('M d, Y', strtotime($reading->reading_date)) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Reading Time</p>
                    <p class="font-medium text-gray-900">{{ date('g:i A', strtotime($reading->reading_timestamp)) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Entered By</p>
                    <p class="font-medium text-gray-900">User #{{ $reading->entered_by ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Original Dip</p>
                    <p class="font-medium text-gray-900">{{ number_format($reading->dip_mm ?? 0, 2) }} mm</p>
                </div>
                <div>
                    <p class="text-gray-600">Original Volume</p>
                    <p class="font-medium text-gray-900">{{ number_format($reading->volume_liters ?? 0, 3) }} L</p>
                </div>
                <div>
                    <p class="text-gray-600">Original Temperature</p>
                    <p class="font-medium text-gray-900">{{ number_format($reading->temperature_celsius ?? 0, 1) }}°C</p>
                </div>
                <div>
                    <p class="text-gray-600">Original Water Level</p>
                    <p class="font-medium text-gray-900">{{ number_format($reading->water_level_mm ?? 0, 2) }} mm</p>
                </div>
            </div>
        </div>

        <!-- Tank Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="bg-green-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Tank Information</h3>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Station</p>
                    <p class="font-medium text-gray-900">{{ $reading->station_name ?? 'Unknown Station' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Station Code</p>
                    <p class="font-medium text-gray-900">{{ $reading->station_code ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Tank Number</p>
                    <p class="font-medium text-gray-900">{{ $reading->tank_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Product</p>
                    <p class="font-medium text-gray-900">{{ $reading->product_name ?? 'Unknown Product' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Product Code</p>
                    <p class="font-medium text-gray-900">{{ $reading->product_code ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Tank Capacity</p>
                    <p class="font-medium text-gray-900">{{ number_format($reading->capacity_liters ?? 0, 0) }} L</p>
                </div>
                <div>
                    <p class="text-gray-600">Fuel Density</p>
                    <p class="font-medium text-gray-900">{{ number_format($fuelConstants->density_15c ?? 0.7500, 4) }} kg/L</p>
                </div>
                <div>
                    <p class="text-gray-600">Expansion Coefficient</p>
                    <p class="font-medium text-gray-900">{{ number_format($fuelConstants->thermal_expansion_coefficient ?? 0.001200, 6) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Reconciliation Information -->
    @if($reconciliation ?? false)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="bg-indigo-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Daily Reconciliation Impact</h3>
            </div>

            <div class="grid grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Opening Stock</p>
                    <p class="font-medium text-gray-900">{{ number_format($reconciliation->opening_stock_liters ?? 0, 3) }} L</p>
                </div>
                <div>
                    <p class="text-gray-600">Closing Stock</p>
                    <p class="font-medium text-gray-900">{{ number_format($reconciliation->closing_stock_liters ?? 0, 3) }} L</p>
                </div>
                <div>
                    <p class="text-gray-600">Calculated Sales</p>
                    <p class="font-medium text-gray-900">{{ number_format($reconciliation->calculated_sales_liters ?? 0, 3) }} L</p>
                </div>
                <div>
                    <p class="text-gray-600">Variance</p>
                    <p class="font-medium text-{{ abs($reconciliation->variance_percentage ?? 0) > 2 ? 'red' : 'green' }}-900">
                        {{ number_format($reconciliation->variance_percentage ?? 0, 2) }}%
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Form -->
    <div class="max-w-4xl mx-auto">
        <form id="eveningReadingEditForm" action="{{ route('evening.readings.update', $reading->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Progress Indicator -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Evening Reading Progress</h2>
                    <span id="progressText" class="text-sm text-gray-600">0 of 5 fields completed</span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progressBar" class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>

                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    <span>Dip</span>
                    <span>Temperature</span>
                    <span>Water Level</span>
                    <span>Notes</span>
                    <span>Reason</span>
                </div>
            </div>

            <!-- Form Fields Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Left Column - Measurements -->
                <div class="space-y-6">

                    <!-- Dip Measurement -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-blue-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Dip Measurement</h3>
                        </div>

                        <div class="form-group">
                            <label for="dip_mm" class="block text-sm font-medium text-gray-700 mb-2">
                                Dip Reading (mm) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="dip_mm" name="dip_mm"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200"
                                        required>
                                    <option value="">Select dip reading...</option>
                                    <!-- 0-100mm: Every 1mm -->
                                    @for($i = 0; $i <= 100; $i++)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    <!-- 101-500mm: Every 5mm -->
                                    @for($i = 105; $i <= 500; $i += 5)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    <!-- 501-1000mm: Every 10mm -->
                                    @for($i = 510; $i <= 1000; $i += 10)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    <!-- 1001-2000mm: Every 25mm -->
                                    @for($i = 1025; $i <= 2000; $i += 25)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    <!-- 2001-3000mm: Every 50mm -->
                                    @for($i = 2050; $i <= 3000; $i += 50)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    <!-- 3001-4000mm: Every 100mm -->
                                    @for($i = 3100; $i <= 4000; $i += 100)
                                        <option value="{{ $i }}" {{ $i == ($reading->dip_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                </select>
                            </div>
                            <div id="dip_mm_error" class="mt-1 text-sm text-red-600 hidden"></div>

                            <!-- Original vs New Comparison -->
                            <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-900">Original vs New</span>
                                </div>
                                <p class="text-sm text-yellow-800 mt-1">
                                    Original: {{ number_format($reading->dip_mm ?? 0, 2) }} mm → New: <span id="newDipValue">{{ number_format($reading->dip_mm ?? 0, 2) }} mm</span>
                                </p>
                            </div>

                            <!-- Volume Calculation Display -->
                            <div id="volumeCalculation" class="mt-3 p-3 bg-orange-50 rounded-lg hidden">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-orange-900">New Calculated Volume</span>
                                </div>
                                <p id="calculatedVolume" class="text-lg font-bold text-orange-900 mt-1">{{ number_format($reading->volume_liters ?? 0, 3) }} L</p>
                                <p id="calculatedDetails" class="text-xs text-orange-700 mt-1">Temperature corrected using fuel constants</p>
                            </div>
                        </div>
                    </div>

                    <!-- Temperature -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-red-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Temperature</h3>
                        </div>

                        <div class="form-group">
                            <label for="temperature_celsius" class="block text-sm font-medium text-gray-700 mb-2">
                                Temperature (°C) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="temperature_celsius" name="temperature_celsius"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200"
                                        required>
                                    <option value="">Select temperature...</option>
                                    <!-- -10°C to 10°C: Every 0.5°C -->
                                    @for($i = -10; $i <= 10; $i += 0.5)
                                        <option value="{{ $i }}" {{ $i == ($reading->temperature_celsius ?? 0) ? 'selected' : '' }}>{{ $i }}°C</option>
                                    @endfor
                                    <!-- 10.1°C to 40°C: Every 0.1°C -->
                                    @for($i = 10.1; $i <= 40; $i += 0.1)
                                        <option value="{{ number_format($i, 1) }}" {{ number_format($i, 1) == number_format($reading->temperature_celsius ?? 25.0, 1) ? 'selected' : '' }}>{{ number_format($i, 1) }}°C</option>
                                    @endfor
                                    <!-- 40.1°C to 60°C: Every 0.5°C -->
                                    @for($i = 40.5; $i <= 60; $i += 0.5)
                                        <option value="{{ $i }}" {{ $i == ($reading->temperature_celsius ?? 0) ? 'selected' : '' }}>{{ $i }}°C</option>
                                    @endfor
                                </select>
                            </div>
                            <div id="temperature_celsius_error" class="mt-1 text-sm text-red-600 hidden"></div>

                            <!-- Original vs New Comparison -->
                            <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-900">Original vs New</span>
                                </div>
                                <p class="text-sm text-yellow-800 mt-1">
                                    Original: {{ number_format($reading->temperature_celsius ?? 0, 1) }}°C → New: <span id="newTempValue">{{ number_format($reading->temperature_celsius ?? 0, 1) }}°C</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Water Level -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-cyan-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Water Level</h3>
                        </div>

                        <div class="form-group">
                            <label for="water_level_mm" class="block text-sm font-medium text-gray-700 mb-2">
                                Water Level (mm) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="water_level_mm" name="water_level_mm"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200"
                                        required>
                                    <option value="0" {{ ($reading->water_level_mm ?? 0) == 0 ? 'selected' : '' }}>0 mm (No water detected)</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ $i == ($reading->water_level_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    @for($i = 15; $i <= 50; $i += 5)
                                        <option value="{{ $i }}" {{ $i == ($reading->water_level_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    @for($i = 60; $i <= 100; $i += 10)
                                        <option value="{{ $i }}" {{ $i == ($reading->water_level_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    @for($i = 120; $i <= 200; $i += 20)
                                        <option value="{{ $i }}" {{ $i == ($reading->water_level_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                    @for($i = 250; $i <= 500; $i += 50)
                                        <option value="{{ $i }}" {{ $i == ($reading->water_level_mm ?? 0) ? 'selected' : '' }}>{{ $i }} mm</option>
                                    @endfor
                                </select>
                            </div>
                            <div id="water_level_mm_error" class="mt-1 text-sm text-red-600 hidden"></div>

                            <!-- Original vs New Comparison -->
                            <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-900">Original vs New</span>
                                </div>
                                <p class="text-sm text-yellow-800 mt-1">
                                    Original: {{ number_format($reading->water_level_mm ?? 0, 2) }} mm → New: <span id="newWaterValue">{{ number_format($reading->water_level_mm ?? 0, 2) }} mm</span>
                                </p>
                            </div>

                            <!-- Water Level Status -->
                            <div id="waterLevelStatus" class="mt-3 p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span id="waterLevelText" class="text-sm font-medium text-green-800"> Normal - No water detected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Notes and Correction -->
                <div class="space-y-6">

                    <!-- Reading Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-purple-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Reading Notes</h3>
                        </div>

                        <div class="form-group">
                            <label for="validation_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Reading Notes (Optional)
                            </label>
                            <textarea id="validation_notes" name="validation_notes" rows="4"
                                      maxlength="500"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 resize-none"
                                      placeholder="Update any observations or notes about this evening reading...">{{ $reading->validation_notes ?? '' }}</textarea>
                            <div class="flex justify-between mt-1">
                                <div id="validation_notes_error" class="text-sm text-red-600 hidden"></div>
                                <div class="text-sm text-gray-500">
                                    <span id="notesCount">{{ strlen($reading->validation_notes ?? '') }}</span>/500 characters
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Correction Reason (Required) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-red-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Correction Reason</h3>
                            <div class="bg-red-50 px-2 py-1 rounded text-xs text-red-700">
                                Required for audit trail
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="correction_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Correction <span class="text-red-500">*</span>
                            </label>
                            <select id="correction_reason" name="correction_reason"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200"
                                    required>
                                <option value="">Select correction reason...</option>
                                <option value="MEASUREMENT_ERROR">Measurement Error</option>
                                <option value="EQUIPMENT_CALIBRATION">Equipment Calibration Issue</option>
                                <option value="ENVIRONMENTAL_FACTOR">Environmental Factor</option>
                                <option value="HUMAN_ERROR">Human Error</option>
                                <option value="TEMPERATURE_CORRECTION">Temperature Correction</option>
                                <option value="WATER_LEVEL_ADJUSTMENT">Water Level Adjustment</option>
                                <option value="FUEL_DENSITY_CORRECTION">Fuel Density Correction</option>
                                <option value="RECONCILIATION_REQUIREMENT">Reconciliation Requirement</option>
                                <option value="AUDIT_FINDING">Audit Finding</option>
                                <option value="SUPERVISOR_INSTRUCTION">Supervisor Instruction</option>
                                <option value="SYSTEM_DISCREPANCY">System Discrepancy</option>
                                <option value="OTHER">Other (specify in notes)</option>
                            </select>
                            <div id="correction_reason_error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                    </div>

                    <!-- Change Impact Summary -->
                    <div id="changeImpactSummary" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-yellow-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Change Impact Summary</h3>
                        </div>

                        <div id="impactResults" class="space-y-3">
                            <!-- Impact results will be populated here -->
                        </div>
                    </div>

                    <!-- Validation Warnings -->
                    <div id="validationWarnings" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-red-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Validation Warnings</h3>
                        </div>

                        <div id="warningResults" class="space-y-3">
                            <!-- Warning results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-orange-100 rounded-full p-2">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Update Evening Reading</h3>
                            <p class="text-sm text-gray-600">Review and save your evening reading corrections</p>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" id="cancelBtn"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Update Evening Reading</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Controller data from EveningReadingController@edit
const reading = @json($reading ?? null);
const fuelConstants = @json($fuelConstants ?? null);
const reconciliation = @json($reconciliation ?? null);
const isAutoApproved = @json($isAutoApproved ?? false);

// Form elements
const form = document.getElementById('eveningReadingEditForm');
const dipSelect = document.getElementById('dip_mm');
const temperatureSelect = document.getElementById('temperature_celsius');
const waterLevelSelect = document.getElementById('water_level_mm');
const notesTextarea = document.getElementById('validation_notes');
const correctionReasonSelect = document.getElementById('correction_reason');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');

// Progress tracking
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
let completedFields = 0;

// Original values for comparison
const originalValues = {
    dip_mm: parseFloat(reading?.dip_mm || 0),
    temperature_celsius: parseFloat(reading?.temperature_celsius || 0),
    water_level_mm: parseFloat(reading?.water_level_mm || 0),
    volume_liters: parseFloat(reading?.volume_liters || 0),
    validation_notes: reading?.validation_notes || ''
};

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    setupEventListeners();
    setupKeyboardNavigation();
    calculateVolumeRealTime();
});

// Setup event listeners
function setupEventListeners() {
    dipSelect.addEventListener('change', handleFieldChange);
    temperatureSelect.addEventListener('change', handleFieldChange);
    waterLevelSelect.addEventListener('change', handleFieldChange);
    correctionReasonSelect.addEventListener('change', handleFieldChange);
    notesTextarea.addEventListener('input', updateNotesCount);
    form.addEventListener('submit', handleFormSubmit);
    cancelBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
            window.location.href = '{{ route("evening.readings.index") }}';
        }
    });
}

// Handle field changes with real-time calculation
function handleFieldChange() {
    updateProgress();
    calculateVolumeRealTime();
    updateComparisonValues();
    calculateChangeImpact();
    validateChanges();
}

// Calculate volume in real-time using FUEL_ERP schema-compliant method
function calculateVolumeRealTime() {
    const dipValue = parseFloat(dipSelect.value);
    const temperatureValue = parseFloat(temperatureSelect.value);
    const waterLevelValue = parseFloat(waterLevelSelect.value);

    if (!dipValue || !temperatureValue || !fuelConstants) {
        document.getElementById('volumeCalculation').classList.add('hidden');
        return;
    }

    // STEP 1: Get base volume using tank calibration table simulation
    // Since we don't have direct access to tank_calibration_tables in frontend,
    // we'll use the controller's temperature correction method logic
    const baseVolume = interpolateVolumeFromDip(dipValue);

    if (baseVolume === 0) {
        document.getElementById('volumeCalculation').classList.add('hidden');
        return;
    }

    // STEP 2: Apply temperature correction using fuel constants
    const tempDifference = temperatureValue - (fuelConstants.temperature_reference_celsius || 15);
    const correctionFactor = 1 - ((fuelConstants.thermal_expansion_coefficient || 0.001200) * tempDifference);

    // STEP 3: Apply vapor pressure correction
    const correctedVolume = baseVolume * correctionFactor * (fuelConstants.vapor_pressure_correction || 0.980);

    // STEP 4: Subtract water volume (simplified calculation)
    const waterVolume = waterLevelValue * 10; // Approximate water displacement
    const finalVolume = Math.max(0, correctedVolume - waterVolume);

    // Display results
    document.getElementById('calculatedVolume').textContent = formatNumber(finalVolume) + ' L';
    document.getElementById('calculatedDetails').textContent = `Temp correction: ${tempDifference.toFixed(1)}°C, Water: ${waterLevelValue}mm`;
    document.getElementById('volumeCalculation').classList.remove('hidden');

    updateWaterLevelStatus(waterLevelValue);
}

// Interpolate volume from dip using tank capacity and approximate calibration
function interpolateVolumeFromDip(dipMm) {
    const tankCapacity = parseFloat(reading?.capacity_liters || 0);

    if (!tankCapacity || dipMm <= 0) return 0;

    // FUEL_ERP calibration approach: Use standard tank geometry
    // Based on the tank_calibration_tables data pattern in the schema
    const calibrationPoints = [
        { dip: 34.62, volume: 1000 },
        { dip: 60.00, volume: 2500 },
        { dip: 116.84, volume: 5000 },
        { dip: 173.68, volume: 7500 },
        { dip: 230.53, volume: 10000 },
        { dip: 287.37, volume: 12500 },
        { dip: 344.21, volume: 15000 },
        { dip: 457.89, volume: 20000 },
        { dip: 571.58, volume: 25000 },
        { dip: 685.26, volume: 30000 },
        { dip: 798.95, volume: 35000 },
        { dip: 912.63, volume: 40000 }
    ];

    // Scale calibration points based on actual tank capacity
    const scaleFactor = tankCapacity / 40000; // 40000L is the reference capacity
    const scaledPoints = calibrationPoints.map(point => ({
        dip: point.dip * Math.sqrt(scaleFactor), // Scale dip proportionally
        volume: point.volume * scaleFactor
    }));

    // Find interpolation points
    let lowerPoint = scaledPoints[0];
    let upperPoint = scaledPoints[scaledPoints.length - 1];

    for (let i = 0; i < scaledPoints.length - 1; i++) {
        if (dipMm >= scaledPoints[i].dip && dipMm <= scaledPoints[i + 1].dip) {
            lowerPoint = scaledPoints[i];
            upperPoint = scaledPoints[i + 1];
            break;
        }
    }

    // Linear interpolation
    if (dipMm <= lowerPoint.dip) {
        return lowerPoint.volume;
    } else if (dipMm >= upperPoint.dip) {
        return upperPoint.volume;
    } else {
        const dipRange = upperPoint.dip - lowerPoint.dip;
        const volumeRange = upperPoint.volume - lowerPoint.volume;
        const dipRatio = (dipMm - lowerPoint.dip) / dipRange;
        return lowerPoint.volume + (volumeRange * dipRatio);
    }
}

// Update comparison values
function updateComparisonValues() {
    const dipValue = parseFloat(dipSelect.value) || originalValues.dip_mm;
    const temperatureValue = parseFloat(temperatureSelect.value) || originalValues.temperature_celsius;
    const waterLevelValue = parseFloat(waterLevelSelect.value) || originalValues.water_level_mm;

    document.getElementById('newDipValue').textContent = formatNumber(dipValue) + ' mm';
    document.getElementById('newTempValue').textContent = formatNumber(temperatureValue) + '°C';
    document.getElementById('newWaterValue').textContent = formatNumber(waterLevelValue) + ' mm';
}

// Calculate change impact
function calculateChangeImpact() {
    const dipValue = parseFloat(dipSelect.value);
    const temperatureValue = parseFloat(temperatureSelect.value);
    const waterLevelValue = parseFloat(waterLevelSelect.value);

    if (!dipValue || !temperatureValue || isNaN(waterLevelValue)) return;

    const changes = [];

    // Dip change
    const dipChange = dipValue - originalValues.dip_mm;
    if (Math.abs(dipChange) > 0.1) {
        changes.push({
            type: Math.abs(dipChange) > 100 ? 'error' : 'warning',
            field: 'Dip Reading',
            change: `${dipChange > 0 ? '+' : ''}${formatNumber(dipChange)} mm`,
            impact: `Volume will ${dipChange > 0 ? 'increase' : 'decrease'} significantly`
        });
    }

    // Temperature change
    const tempChange = temperatureValue - originalValues.temperature_celsius;
    if (Math.abs(tempChange) > 0.1) {
        changes.push({
            type: Math.abs(tempChange) > 10 ? 'error' : 'warning',
            field: 'Temperature',
            change: `${tempChange > 0 ? '+' : ''}${formatNumber(tempChange)}°C`,
            impact: `Thermal expansion ${tempChange > 0 ? 'increases' : 'decreases'} volume`
        });
    }

    // Water level change
    const waterChange = waterLevelValue - originalValues.water_level_mm;
    if (Math.abs(waterChange) > 0.1) {
        changes.push({
            type: waterLevelValue > 50 ? 'error' : 'warning',
            field: 'Water Level',
            change: `${waterChange > 0 ? '+' : ''}${formatNumber(waterChange)} mm`,
            impact: `Water contamination ${waterChange > 0 ? 'increased' : 'decreased'}`
        });
    }

    // Display changes
    const impactDiv = document.getElementById('changeImpactSummary');
    const resultsDiv = document.getElementById('impactResults');

    if (changes.length > 0) {
        resultsDiv.innerHTML = changes.map(change => `
            <div class="${change.type === 'error' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'} border p-4 rounded-lg">
                <div class="flex items-center space-x-2">
                    <span class="text-lg">${change.type === 'error' ? '🚨' : '⚠️'}</span>
                    <span class="font-medium">${change.field}: ${change.change}</span>
                </div>
                <p class="mt-1 text-sm text-gray-600">${change.impact}</p>
            </div>
        `).join('');
        impactDiv.classList.remove('hidden');
    } else {
        impactDiv.classList.add('hidden');
    }
}

// Validate changes
function validateChanges() {
    const dipValue = parseFloat(dipSelect.value);
    const temperatureValue = parseFloat(temperatureSelect.value);
    const waterLevelValue = parseFloat(waterLevelSelect.value);

    if (!dipValue || !temperatureValue || isNaN(waterLevelValue)) return;

    const warnings = [];

    // Extreme dip changes
    const dipChange = Math.abs(dipValue - originalValues.dip_mm);
    if (dipChange > 500) {
        warnings.push({
            type: 'error',
            message: 'Extreme dip change detected',
            detail: `Change of ${formatNumber(dipChange)}mm is unusually large`
        });
    }

    // Temperature range check
    if (temperatureValue < 10 || temperatureValue > 40) {
        warnings.push({
            type: 'warning',
            message: 'Temperature outside normal range',
            detail: `${formatNumber(temperatureValue)}°C is outside typical range (10-40°C)`
        });
    }

    // Water level check
    if (waterLevelValue > 100) {
        warnings.push({
            type: 'error',
            message: 'High water contamination detected',
            detail: `Water level of ${formatNumber(waterLevelValue)}mm requires investigation`
        });
    }

    // Display warnings
    const warningsDiv = document.getElementById('validationWarnings');
    const resultsDiv = document.getElementById('warningResults');

    if (warnings.length > 0) {
        resultsDiv.innerHTML = warnings.map(warning => `
            <div class="${warning.type === 'error' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'} border p-4 rounded-lg">
                <div class="flex items-center space-x-2">
                    <span class="text-lg">${warning.type === 'error' ? '🚨' : '⚠️'}</span>
                    <span class="font-medium">${warning.message}</span>
                </div>
                <p class="mt-1 text-sm text-gray-600">${warning.detail}</p>
            </div>
        `).join('');
        warningsDiv.classList.remove('hidden');
    } else {
        warningsDiv.classList.add('hidden');
    }
}

// Update water level status
function updateWaterLevelStatus(waterLevel) {
    const statusDiv = document.getElementById('waterLevelStatus');
    const statusText = document.getElementById('waterLevelText');

    let statusClass, statusMessage;

    if (waterLevel === 0) {
        statusClass = 'bg-green-50';
        statusText.className = 'text-sm font-medium text-green-800';
        statusMessage = ' Normal - No water detected';
    } else if (waterLevel <= 10) {
        statusClass = 'bg-yellow-50';
        statusText.className = 'text-sm font-medium text-yellow-800';
        statusMessage = '⚠️ Low water level - Monitor closely';
    } else if (waterLevel <= 50) {
        statusClass = 'bg-orange-50';
        statusText.className = 'text-sm font-medium text-orange-800';
        statusMessage = '⚠️ Moderate water level - Attention required';
    } else {
        statusClass = 'bg-red-50';
        statusText.className = 'text-sm font-medium text-red-800';
        statusMessage = '🚨 High water level - Investigation required';
    }

    statusDiv.className = `mt-3 p-3 rounded-lg ${statusClass}`;
    statusText.textContent = statusMessage;
}

// Update progress
function updateProgress() {
    const dipValue = dipSelect.value;
    const temperatureValue = temperatureSelect.value;
    const waterLevelValue = waterLevelSelect.value;
    const notesValue = notesTextarea.value;
    const correctionReasonValue = correctionReasonSelect.value;

    const fields = [dipValue, temperatureValue, waterLevelValue, notesValue, correctionReasonValue];
    completedFields = fields.filter(field => field && field.trim() !== '').length;

    const progressPercentage = (completedFields / fields.length) * 100;
    progressBar.style.width = progressPercentage + '%';
    progressText.textContent = `${completedFields} of ${fields.length} fields completed`;

    submitBtn.disabled = !correctionReasonValue || !dipValue || !temperatureValue || waterLevelValue === '';
}

// Update notes count
function updateNotesCount() {
    const count = notesTextarea.value.length;
    document.getElementById('notesCount').textContent = count;
}

// Handle form submission
function handleFormSubmit(e) {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Updating...
    `;

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isAutoApproved) {
                showAutoApproval(data.message);
            } else {
                showSuccess(data.message);
            }
            setTimeout(() => {
                window.location.href = '{{ route("evening.readings.index") }}';
            }, 1500);
        } else {
            showError(data.error || 'An error occurred while updating the evening reading.');
            resetSubmitButton();
        }
    })
    .catch(error => {
        showError('Network error. Please try again.');
        resetSubmitButton();
    });
}

// Reset submit button
function resetSubmitButton() {
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>Update Evening Reading</span>
    `;
}

// Setup keyboard navigation
function setupKeyboardNavigation() {
    const fields = [dipSelect, temperatureSelect, waterLevelSelect, notesTextarea, correctionReasonSelect];

    fields.forEach((field, index) => {
        field.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && field.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const nextField = fields[index + 1];
                if (nextField) {
                    nextField.focus();
                } else {
                    submitBtn.focus();
                }
            }
        });
    });
}

// Utility functions
function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
}

// CEO/SYSTEM_ADMIN users see auto-approval messages
@if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
function showAutoApproval(message) {
    Swal.fire({
        icon: 'success',
        title: ' Action Completed — Auto-Approved by Role',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}
@endif

// Success/Error notification functions
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

// Handle flash messages
@if(session('success'))
    showSuccess('{{ session('success') }}');
@endif

@if(session('error'))
    showError('{{ session('error') }}');
@endif
</script>
@endsection
