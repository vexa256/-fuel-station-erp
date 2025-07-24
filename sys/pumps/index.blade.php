@extends('layouts.app')

@section('title', $station->station_name . ' - Pump Management')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --background: 0 0% 100%;
        --foreground: 222.2 84% 4.9%;
        --card: 0 0% 100%;
        --card-foreground: 222.2 84% 4.9%;
        --primary: 222.2 47.4% 11.2%;
        --primary-foreground: 210 40% 98%;
        --secondary: 210 40% 96%;
        --secondary-foreground: 222.2 84% 4.9%;
        --muted: 210 40% 96%;
        --muted-foreground: 215.4 16.3% 46.9%;
        --accent: 210 40% 96%;
        --accent-foreground: 222.2 84% 4.9%;
        --destructive: 0 84.2% 60.2%;
        --destructive-foreground: 210 40% 98%;
        --border: 214.3 31.8% 91.4%;
        --ring: 222.2 84% 4.9%;
        --radius: 0.5rem;
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-color: hsl(var(--border));
    }

    body {
        background-color: hsl(var(--background));
        color: hsl(var(--foreground));
    }

    .status-operational {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .status-maintenance-due {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .status-calibration-overdue {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .status-out-of-order {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    }

    .status-inactive {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .table-row-hover:hover {
        background-color: hsl(var(--accent) / 0.5);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .metric-card {
        background: linear-gradient(135deg, hsl(var(--card)) 0%, hsl(var(--accent) / 0.3) 100%);
        border: 1px solid hsl(var(--border));
        border-radius: calc(var(--radius));
    }

    .progress-ring {
        transition: stroke-dashoffset 0.3s ease;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <!-- Navigation Breadcrumb -->
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('pumps.select') }}"
                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                    </path>
                                </svg>
                                Pump Management
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $station->station_name
                                    }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Station Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $station->station_name }}</h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>{{ $station->station_code }}</span>
                                <span>•</span>
                                <span>{{ $station->district }}, {{ $station->region }}</span>
                            </div>
                        </div>
                    </div>

                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('pumps.create', $station->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Pump
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @php
            $totalPumps = $pumps->count();
            $operationalPumps = $pumps->where('pump_status', 'OPERATIONAL')->count();
            $maintenanceDue = $pumps->where('pump_status', 'MAINTENANCE_DUE')->count();
            $calibrationOverdue = $pumps->where('pump_status', 'CALIBRATION_OVERDUE')->count();
            $outOfOrder = $pumps->where('pump_status', 'OUT_OF_ORDER')->count();
            $operationalPercentage = $totalPumps > 0 ? round(($operationalPumps / $totalPumps) * 100) : 0;
            @endphp

            <!-- Total Pumps -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Pumps</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalPumps }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Operational -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Operational</p>
                        <p class="text-3xl font-bold text-green-600">{{ $operationalPumps }}</p>
                        <p class="text-xs text-gray-500">{{ $operationalPercentage }}% of total</p>
                    </div>
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-green-600 progress-ring" stroke="currentColor" stroke-width="3"
                                fill="none" stroke-dasharray="{{ $operationalPercentage }}, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-semibold text-green-600">{{ $operationalPercentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Due -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Maintenance Due</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $maintenanceDue }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.664 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Issues -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Critical Issues</p>
                        <p class="text-3xl font-bold text-red-600">{{ $calibrationOverdue + $outOfOrder }}</p>
                        <p class="text-xs text-gray-500">{{ $calibrationOverdue }} overdue, {{ $outOfOrder }} down</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg border border-gray-200 mb-6">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" id="pump-search"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Search pumps by number, serial, or manufacturer...">
                    </div>
                    <div class="flex gap-2">
                        <select id="status-filter"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="OPERATIONAL">Operational</option>
                            <option value="MAINTENANCE_DUE">Maintenance Due</option>
                            <option value="CALIBRATION_OVERDUE">Calibration Overdue</option>
                            <option value="OUT_OF_ORDER">Out of Order</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                        <select id="product-filter"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Products</option>
                            @foreach($pumps->pluck('product_type')->unique()->filter() as $productType)
                            <option value="{{ $productType }}">{{ $productType }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pumps Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="pumps-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pump Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tank & Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Maintenance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pumps as $pump)
                        @php
                        $statusConfig = [
                        'OPERATIONAL' => ['class' => 'status-operational', 'text' => 'Operational', 'icon' =>
                        'check-circle'],
                        'MAINTENANCE_DUE' => ['class' => 'status-maintenance-due', 'text' => 'Maintenance Due', 'icon'
                        => 'exclamation-triangle'],
                        'CALIBRATION_OVERDUE' => ['class' => 'status-calibration-overdue', 'text' => 'Calibration
                        Overdue', 'icon' => 'times-circle'],
                        'OUT_OF_ORDER' => ['class' => 'status-out-of-order', 'text' => 'Out of Order', 'icon' => 'ban'],
                        'INACTIVE' => ['class' => 'status-inactive', 'text' => 'Inactive', 'icon' => 'pause-circle']
                        ];
                        $status = $statusConfig[$pump->pump_status] ?? $statusConfig['INACTIVE'];
                        @endphp
                        <tr class="table-row-hover pump-row" data-pump-number="{{ $pump->pump_number }}"
                            data-serial="{{ strtolower($pump->pump_serial_number) }}"
                            data-manufacturer="{{ strtolower($pump->pump_manufacturer ?? '') }}"
                            data-status="{{ $pump->pump_status }}" data-product="{{ $pump->product_type }}">

                            <!-- Pump Details -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-600">P{{ $pump->pump_number
                                                }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Pump {{ $pump->pump_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $pump->pump_serial_number }}</div>
                                        @if($pump->pump_manufacturer)
                                        <div class="text-xs text-gray-400">{{ $pump->pump_manufacturer }} {{
                                            $pump->pump_model }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Tank & Product -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">Tank {{ $pump->tank_number }}</div>
                                <div class="text-sm text-gray-500">{{ $pump->product_name }}</div>
                                <div class="text-xs text-gray-400">{{ number_format($pump->tank_capacity) }}L capacity
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white {{ $status['class'] }}">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if($status['icon'] === 'check-circle')
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                        @elseif($status['icon'] === 'exclamation-triangle')
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                        @elseif($status['icon'] === 'times-circle')
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"></path>
                                        @elseif($status['icon'] === 'ban')
                                        <path fill-rule="evenodd"
                                            d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"
                                            clip-rule="evenodd"></path>
                                        @else
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                        @endif
                                    </svg>
                                    {{ $status['text'] }}
                                </span>
                                @if(!$pump->is_operational && $pump->out_of_order_reason)
                                <div class="text-xs text-red-600 mt-1">{{ $pump->out_of_order_reason }}</div>
                                @endif
                            </td>

                            <!-- Performance -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($pump->last_meter_reading)
                                    {{ number_format($pump->last_meter_reading) }}L
                                    @else
                                    No readings
                                    @endif
                                </div>
                                @if($pump->last_reading_date)
                                <div class="text-sm text-gray-500">{{
                                    \Carbon\Carbon::parse($pump->last_reading_date)->format('M j, Y') }}</div>
                                @endif
                                <div class="flex items-center mt-1">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ min(100, $pump->utilization_score ?? 0) }}%"></div>
                                    </div>
                                    <span class="ml-2 text-xs text-gray-600">{{ round($pump->utilization_score ?? 0)
                                        }}%</span>
                                </div>
                            </td>

                            <!-- Maintenance -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="space-y-1">
                                    <div class="flex items-center">
                                        <span class="text-xs">Cal:</span>
                                        <span
                                            class="ml-1 {{ $pump->days_until_calibration < 0 ? 'text-red-600 font-medium' : ($pump->days_until_calibration <= 30 ? 'text-yellow-600' : 'text-gray-600') }}">
                                            @if($pump->days_until_calibration < 0) {{ abs($pump->days_until_calibration)
                                                }}d overdue
                                                @else
                                                {{ $pump->days_until_calibration }}d left
                                                @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="text-xs">Mnt:</span>
                                        <span
                                            class="ml-1 {{ $pump->days_until_maintenance <= 7 ? 'text-yellow-600' : 'text-gray-600' }}">
                                            {{ $pump->days_until_maintenance }}d left
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('pumps.maintenance', $pump->id) }}"
                                        class="text-blue-600 hover:text-blue-900" title="View Maintenance">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
                                    <a href="{{ route('pumps.edit', $pump->id) }}"
                                        class="text-indigo-600 hover:text-indigo-900" title="Edit Pump">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No pumps configured</h3>
                                    <p class="text-gray-500 mb-4">This station doesn't have any pumps set up yet.</p>
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
                                    <a href="{{ route('pumps.create', $station->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Add First Pump
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pump-search');
    const statusFilter = document.getElementById('status-filter');
    const productFilter = document.getElementById('product-filter');
    const pumpRows = document.querySelectorAll('.pump-row');

    function filterPumps() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedProduct = productFilter.value;
        let visibleCount = 0;

        pumpRows.forEach(row => {
            const pumpNumber = row.dataset.pumpNumber;
            const serial = row.dataset.serial;
            const manufacturer = row.dataset.manufacturer;
            const status = row.dataset.status;
            const product = row.dataset.product;

            // Search filter
            const matchesSearch = !searchTerm ||
                pumpNumber.includes(searchTerm) ||
                serial.includes(searchTerm) ||
                manufacturer.includes(searchTerm);

            // Status filter
            const matchesStatus = !selectedStatus || status === selectedStatus;

            // Product filter
            const matchesProduct = !selectedProduct || product === selectedProduct;

            const shouldShow = matchesSearch && matchesStatus && matchesProduct;

            if (shouldShow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add event listeners
    searchInput.addEventListener('input', filterPumps);
    statusFilter.addEventListener('change', filterPumps);
    productFilter.addEventListener('change', filterPumps);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }

        // N for new pump (if authorized)
        if (e.key === 'n' || e.key === 'N') {
            const createButton = document.querySelector('a[href*="create"]');
            if (createButton && !searchInput.matches(':focus')) {
                e.preventDefault();
                createButton.click();
            }
        }
    });

    // Focus search on page load
    searchInput.focus();
});
</script>
@endpush
@endsection
