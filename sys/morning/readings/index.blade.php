@extends('layouts.app')

@section('title', 'Morning Readings Dashboard - FUEL_ERP')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-black text-white p-2 rounded-lg">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Morning Readings Dashboard</h1>
                    <p class="text-sm text-gray-600">{{ $currentDate }} | System Status:
                        @if($systemHealth['healthy'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                Online
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1"></div>
                                Issues Detected
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                                    @if($timeValidation['valid'] || $isAutoApproved)
                    <a href="{{ route('morning.readings.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Reading
                    </a>
                @endif

                <button onclick="refreshDashboard()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors duration-200">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Time Window Status -->
        @if(!$timeValidation['valid'] && !$isAutoApproved)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            {{ $timeValidation['message'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <button onclick="switchTab('overview')" id="overview-tab"
                        class="tab-button active py-4 px-1 border-b-2 border-black font-medium text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Overview
                </button>
                <button onclick="switchTab('readings')" id="readings-tab"
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Current Readings
                </button>
                <button onclick="switchTab('missing')" id="missing-tab"
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Missing Readings
                    @if($missingReadings->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $missingReadings->count() }}
                        </span>
                    @endif
                </button>
                <button onclick="switchTab('variances')" id="variances-tab"
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Overnight Variances
                    @if($overnightVariances->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $overnightVariances->count() }}
                        </span>
                    @endif
                </button>
                <button onclick="switchTab('health')" id="health-tab"
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    System Health
                </button>
            </div>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Tab -->
        <div id="overview-content" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Readings Status Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-black text-white rounded-lg flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Readings Completed</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $morningReadingsStatus->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Missing Readings Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Missing Readings</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $missingReadings->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variances Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Variances</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $overnightVariances->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($systemHealth['healthy'])
                                    <div class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">System Health</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $systemHealth['healthy'] ? 'Healthy' : 'Issues' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Alert -->
            @if(($missingReadings ?? collect())->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Action Required</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                {{ $missingReadings->count() }} tank(s) missing morning readings. Complete readings to maintain baseline compliance.
                            </p>
                            <div class="mt-3">
                                <button onclick="switchTab('missing')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                    View Missing Readings
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Current Readings Tab -->
        <div id="readings-content" class="tab-content hidden">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg border border-gray-200">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Morning Readings ({{ $currentDate }})</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">All completed morning dip readings for today</p>
                </div>

                @if($morningReadingsStatus->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($morningReadingsStatus as $reading)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 bg-black text-white rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium">{{ $reading->tank_number }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $reading->station_name }} - Tank {{ $reading->tank_number }}
                                                </p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $reading->product_name }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-4 mt-1">
                                                <p class="text-sm text-gray-600">
                                                    <strong>{{ number_format($reading->dip_reading_liters, 1) }}L</strong>
                                                    ({{ number_format($reading->dip_reading_mm, 0) }}mm)
                                                </p>
                                                @if($reading->temperature_celsius)
                                                    <p class="text-sm text-gray-600">
                                                        Temp: {{ $reading->temperature_celsius }}°C
                                                    </p>
                                                @endif
                                                <p class="text-sm text-gray-600">
                                                    {{ date('H:i', strtotime($reading->reading_time)) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $status = $reading->reading_status;
                                        @endphp
                                        @switch($status)
                                            @case('COMPLETED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                                @break
                                            @case('VARIANCE_DETECTED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Variance
                                                </span>
                                                @break
                                            @case('APPROVED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Approved
                                                </span>
                                                @break
                                            @case('FLAGGED')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Flagged
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $status }}
                                                </span>
                                        @endswitch

                                        @if($isAutoApproved)
                                            <a href="{{ route('morning.readings.edit', $reading->id) }}"
                                               class="text-black hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black rounded-md p-1 transition-colors duration-200"
                                               title="Edit Reading">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No readings yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first morning reading.</p>
                        @if($timeValidation['valid'] || $isAutoApproved)
                            <div class="mt-6">
                                <a href="{{ route('morning.readings.create') }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add First Reading
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Missing Readings Tab -->
        <div id="missing-content" class="tab-content hidden">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg border border-gray-200">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Missing Morning Readings</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Tanks requiring morning readings for {{ $currentDate }}</p>
                </div>

                @if($missingReadings->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($missingReadings as $tank)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 bg-red-100 text-red-800 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium">{{ $tank->tank_number }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $tank->station_name }} - Tank {{ $tank->tank_number }}
                                                </p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $tank->product_name }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                Capacity: {{ number_format($tank->capacity_liters, 0) }}L
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Missing
                                        </span>
                                        @if($timeValidation['valid'] || $isAutoApproved)
                                            <a href="{{ route('morning.readings.create') }}?tank_id={{ $tank->id }}"
                                               class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors duration-200">
                                                Add Reading
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">All readings complete</h3>
                        <p class="mt-1 text-sm text-gray-500">All tanks have morning readings for today.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Variances Tab -->
        <div id="variances-content" class="tab-content hidden">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg border border-gray-200">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Overnight Variances</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Active variances requiring attention</p>
                </div>

                @if($overnightVariances->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($overnightVariances as $variance)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 bg-yellow-100 text-yellow-800 rounded-full flex items-center justify-center">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $variance->station_name }} - Tank {{ $variance->tank_number }}
                                                </p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $variance->product_name }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-4 mt-1">
                                                <p class="text-sm text-gray-600">
                                                    Variance: <strong>{{ number_format($variance->calculated_variance_percentage, 2) }}%</strong>
                                                    ({{ number_format($variance->calculated_variance_liters, 1) }}L)
                                                </p>
                                               @if($variance->created_at)
    <p class="text-sm text-gray-600">
        {{ \Carbon\Carbon::parse($variance->created_at)->format('H:i') }}
    </p>
@endif

                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $escalationLevel = $variance->escalation_level;
                                            $riskLevel = $variance->risk_level;
                                        @endphp

                                        @switch($escalationLevel)
                                            @case('STATION')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Station Level
                                                </span>
                                                @break
                                            @case('REGIONAL')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Regional
                                                </span>
                                                @break
                                            @case('CEO')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    CEO Level
                                                </span>
                                                @break
                                            @case('AUDIT')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Audit Required
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $escalationLevel }}
                                                </span>
                                        @endswitch

                                        @switch($riskLevel)
                                            @case('CRITICAL')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Critical
                                                </span>
                                                @break
                                            @case('HIGH')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    High Risk
                                                </span>
                                                @break
                                            @case('MEDIUM')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Medium
                                                </span>
                                                @break
                                            @case('LOW')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Low
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $riskLevel }}
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No active variances</h3>
                        <p class="mt-1 text-sm text-gray-500">All overnight readings are within acceptable ranges.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- System Health Tab -->
        <div id="health-content" class="tab-content hidden">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg border border-gray-200">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">System Health Status</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">FIFO automation and system monitoring status</p>
                </div>

                <div class="px-4 py-5 sm:px-6">
                    <!-- FIFO Health Status -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">FIFO System Health</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @php
                                $healthScore = $fifoHealthStatus['overall_score'];
                                $scoreColor = $healthScore >= 90 ? 'text-green-600' : ($healthScore >= 70 ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-medium text-gray-900">Overall Health Score</span>
                                <span class="text-2xl font-bold {{ $scoreColor }}">
                                    {{ $healthScore }}%
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Processing Stats (24h)</p>
                                    <p class="text-sm font-medium">
                                        {{ number_format($fifoHealthStatus['processing_stats_24h']['total_transactions']) }} transactions
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Active Alerts</p>
                                    <p class="text-sm font-medium">{{ $fifoHealthStatus['active_alerts']['total_alerts'] }} alerts</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Layers Consumed</p>
                                    <p class="text-sm font-medium">
                                        {{ number_format($fifoHealthStatus['processing_stats_24h']['layers_consumed']) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Total Volume (24h)</p>
                                    <p class="text-sm font-medium">
                                        {{ number_format($fifoHealthStatus['processing_stats_24h']['total_quantity'], 1) }}L
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Configuration Status -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Automation Configuration</h4>
                        <div class="space-y-3">
                            @if(isset($fifoHealthStatus['configuration_status']))
                                @foreach($fifoHealthStatus['configuration_status'] as $config => $enabled)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <span class="text-sm text-gray-900">
                                            {{ ucwords(str_replace(['_', 'enabled'], [' ', ''], $config)) }}
                                        </span>
                                        @if($enabled)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                                Enabled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1"></div>
                                                Disabled
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500">Configuration status unavailable</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent System Checks -->
                    @if(isset($systemHealth['monitoring_results']) && count($systemHealth['monitoring_results']) > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Recent System Checks</h4>
                            <div class="space-y-2">
                                @foreach(array_slice($systemHealth['monitoring_results'], 0, 5) as $check)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $check->check_type }}
                                            </p>
                                            @if($check->check_details)
                                                <p class="text-xs text-gray-600 truncate">
                                                    {{ Str::limit($check->check_details, 60) }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            @php
                                                $status = $check->check_status;
                                            @endphp
                                            @switch($status)
                                                @case('PASSED')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Passed
                                                    </span>
                                                    @break
                                                @case('FAILED')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Failed
                                                    </span>
                                                    @break
                                                @case('WARNING')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Warning
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $status }}
                                                    </span>
                                            @endswitch
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No recent system checks</h3>
                            <p class="mt-1 text-sm text-gray-500">System monitoring data will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function switchTab(tabName) {
    try {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'border-black', 'text-gray-900');
            button.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        const contentElement = document.getElementById(tabName + '-content');
        if (contentElement) {
            contentElement.classList.remove('hidden');
        }

        // Add active class to selected tab
        const activeTab = document.getElementById(tabName + '-tab');
        if (activeTab) {
            activeTab.classList.add('active', 'border-black', 'text-gray-900');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
        }

        // Update URL hash without page reload
        history.replaceState(null, null, '#' + tabName);
    } catch (error) {
        console.error('Tab switching error:', error);
    }
}

function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        const originalText = refreshBtn.innerHTML;

        // Show loading state
        refreshBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Refreshing...
        `;
        refreshBtn.disabled = true;

        // Small delay to show loading state, then reload
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
}

// Auto-refresh every 5 minutes for data freshness
let autoRefreshInterval = setInterval(() => {
    console.log('Auto-refreshing dashboard for data freshness...');
    window.location.reload();
}, 300000); // 5 minutes

// Check for new notifications/variances every 30 seconds
let notificationCheckInterval = setInterval(() => {
    if (!csrfToken) return;

    fetch('/api/notifications/check', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.new_variances > 0) {
            // Show notification for new variances using native browser notification or custom alert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'New Variance Detected',
                    text: `${data.new_variances} new variance(s) require attention.`,
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    timer: 5000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            } else {
                // Fallback to console log if SweetAlert not available
                console.log(`New variance detected: ${data.new_variances} variance(s) require attention.`);
            }
        }
    })
    .catch(error => {
        // Silent failure for notification checks to avoid console spam
        console.debug('Notification check failed:', error);
    });
}, 30000); // 30 seconds

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Load tab from URL hash if present
        const hash = window.location.hash.substring(1);
        const validTabs = ['overview', 'readings', 'missing', 'variances', 'health'];

        if (hash && validTabs.includes(hash)) {
            switchTab(hash);
        }

        // Add keyboard navigation for tabs (Arrow keys)
        document.addEventListener('keydown', function(e) {
            // Only handle arrow keys when no input is focused
            if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
                return;
            }

            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();

                const tabs = ['overview', 'readings', 'missing', 'variances', 'health'];
                const activeTab = document.querySelector('.tab-button.active');

                if (activeTab) {
                    const currentIndex = tabs.findIndex(tab => activeTab.id === tab + '-tab');

                    let nextIndex;
                    if (e.key === 'ArrowRight') {
                        nextIndex = (currentIndex + 1) % tabs.length;
                    } else {
                        nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                    }

                    switchTab(tabs[nextIndex]);
                }
            }
        });

        // Add click handlers for tab buttons to ensure proper focus management
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                this.focus();
            });
        });

        // Handle page visibility change to manage auto-refresh
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, clear intervals to save resources
                clearInterval(autoRefreshInterval);
                clearInterval(notificationCheckInterval);
            } else {
                // Page is visible again, restart intervals
                autoRefreshInterval = setInterval(() => {
                    window.location.reload();
                }, 300000);

                notificationCheckInterval = setInterval(() => {
                    // Notification check code here (same as above)
                }, 30000);
            }
        });

    } catch (error) {
        console.error('Initialization error:', error);
    }
});

// Clean up intervals when page is unloaded
window.addEventListener('beforeunload', function() {
    clearInterval(autoRefreshInterval);
    clearInterval(notificationCheckInterval);
});
</script>

<style>
/* Tab styling with smooth transitions */
.tab-button {
    transition: all 0.2s ease-in-out;
}

.tab-button:hover {
    color: #374151;
    border-color: #d1d5db;
}

.tab-button.active {
    color: #111827 !important;
    border-color: #000000 !important;
}

.tab-button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

.tab-content {
    min-height: 400px;
    opacity: 1;
    transition: opacity 0.15s ease-in-out;
}

.tab-content.hidden {
    opacity: 0;
}

/* Card hover effects */
.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-1 {
        gap: 1rem;
    }

    .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .tab-button {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }

    .space-x-8 > * + * {
        margin-left: 1rem;
    }
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .transition-colors,
    .tab-button,
    .tab-content {
        transition: none;
    }

    .animate-spin {
        animation: none;
    }
}

/* Print styles */
@media print {
    .tab-button,
    button,
    .bg-yellow-50,
    .bg-red-50 {
        display: none;
    }

    .tab-content {
        display: block !important;
    }
}
</style>
@endsection
