@extends('layouts.app')

@section('title', 'Maintenance - Pump ' . $pump->pump_number . ' - ' . $pump->station_name)

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Navigation Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('pumps.select') }}"
                        class="inline-flex items-center text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
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
                        <svg class="w-6 h-6 text-muted-foreground" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('pumps.index', $pump->station_id) }}"
                            class="ml-1 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors md:ml-2">{{
                            $pump->station_name }}</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-muted-foreground" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-muted-foreground md:ml-2">Maintenance
                            Dashboard</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Pump {{ $pump->pump_number }} Maintenance</h1>
                        <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                            <span>{{ $pump->pump_serial_number }}</span>
                            <span>•</span>
                            <span>{{ $pump->station_name }}</span>
                            <span>•</span>
                            <span>Tank {{ $pump->tank_number }} ({{ $pump->product_name }})</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Auto-Approved Access
                    </div>
                    @endif

                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']))
                    <a href="{{ route('pumps.edit', $pump->id) }}" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit Pump
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        @if(count($alerts) > 0)
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($alerts as $alert)
                @php
                $alertConfig = [
                'ERROR' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'icon' =>
                'text-red-600'],
                'WARNING' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800',
                'icon' => 'text-yellow-600'],
                'INFO' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' =>
                'text-blue-600']
                ];
                $config = $alertConfig[$alert['type']] ?? $alertConfig['INFO'];
                @endphp
                <div class="card {{ $config['bg'] }} {{ $config['border'] }}">
                    <div class="p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 {{ $config['icon'] }} mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                @if($alert['type'] === 'ERROR')
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                                @elseif($alert['type'] === 'WARNING')
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                                @else
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                                @endif
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium {{ $config['text'] }}">{{
                                    ucfirst(strtolower($alert['type'])) }}</h4>
                                <p class="text-sm {{ $config['text'] }} mt-1">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Operational Status -->
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Operational Status</p>
                            <p
                                class="text-2xl font-bold {{ $pump->is_operational ? 'text-green-600' : 'text-red-600' }}">
                                {{ $pump->is_operational ? 'Operational' : 'Out of Order' }}
                            </p>
                            @if(!$pump->is_operational && $pump->out_of_order_reason)
                            <p class="text-xs text-muted-foreground mt-1">{{ $pump->out_of_order_reason }}</p>
                            @endif
                        </div>
                        <div class="p-3 {{ $pump->is_operational ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                            <svg class="w-6 h-6 {{ $pump->is_operational ? 'text-green-600' : 'text-red-600' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                @if($pump->is_operational)
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                                @else
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Status -->
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Next Maintenance</p>
                            @if($maintenanceStats['maintenance_overdue'])
                            <p class="text-2xl font-bold text-red-600">Overdue</p>
                            <p class="text-xs text-red-600">{{ abs($maintenanceStats['days_until_next_maintenance']) }}
                                days overdue</p>
                            @elseif($maintenanceStats['days_until_next_maintenance'] <= 7) <p
                                class="text-2xl font-bold text-yellow-600">{{
                                $maintenanceStats['days_until_next_maintenance'] }} days</p>
                                <p class="text-xs text-yellow-600">Due soon</p>
                                @else
                                <p class="text-2xl font-bold text-green-600">{{
                                    $maintenanceStats['days_until_next_maintenance'] }} days</p>
                                <p class="text-xs text-green-600">On schedule</p>
                                @endif
                        </div>
                        <div
                            class="p-3 {{ $maintenanceStats['maintenance_overdue'] ? 'bg-red-100' : ($maintenanceStats['days_until_next_maintenance'] <= 7 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                            <svg class="w-6 h-6 {{ $maintenanceStats['maintenance_overdue'] ? 'text-red-600' : ($maintenanceStats['days_until_next_maintenance'] <= 7 ? 'text-yellow-600' : 'text-green-600') }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calibration Status -->
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Next Calibration</p>
                            @if($maintenanceStats['calibration_overdue'])
                            <p class="text-2xl font-bold text-red-600">Overdue</p>
                            <p class="text-xs text-red-600">{{ abs($maintenanceStats['days_until_calibration']) }} days
                                overdue</p>
                            @elseif($maintenanceStats['days_until_calibration'] <= 30) <p
                                class="text-2xl font-bold text-yellow-600">{{
                                $maintenanceStats['days_until_calibration'] }} days</p>
                                <p class="text-xs text-yellow-600">Due soon</p>
                                @else
                                <p class="text-2xl font-bold text-green-600">{{
                                    $maintenanceStats['days_until_calibration'] }} days</p>
                                <p class="text-xs text-green-600">Compliant</p>
                                @endif
                        </div>
                        <div
                            class="p-3 {{ $maintenanceStats['calibration_overdue'] ? 'bg-red-100' : ($maintenanceStats['days_until_calibration'] <= 30 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                            <svg class="w-6 h-6 {{ $maintenanceStats['calibration_overdue'] ? 'text-red-600' : ($maintenanceStats['days_until_calibration'] <= 30 ? 'text-yellow-600' : 'text-green-600') }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilization Score -->
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Utilization Score</p>
                            <p class="text-2xl font-bold text-foreground">{{
                                round($utilizationMetrics['daily_average_volume'] ?? 0) }}%</p>
                            <p class="text-xs text-muted-foreground">30-day average</p>
                        </div>
                        <div class="relative w-16 h-16">
                            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-muted" stroke="currentColor" stroke-width="3" fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-primary" stroke="currentColor" stroke-width="3" fill="none"
                                    stroke-dasharray="{{ min(100, round($utilizationMetrics['daily_average_volume'] ?? 0)) }}, 100"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-semibold text-foreground">{{
                                    round($utilizationMetrics['daily_average_volume'] ?? 0) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Volume Trend Chart -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Volume Trend (30 Days)</h3>
                    <p class="text-sm text-muted-foreground">Daily meter reading progression and volume dispensed</p>
                </div>
                <div class="p-6">
                    <div id="volume-chart" style="width: 100%; height: 300px;"></div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Performance Metrics</h3>
                    <p class="text-sm text-muted-foreground">Key operational indicators and efficiency scores</p>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Daily Average Volume -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium text-foreground">Daily Average Volume</span>
                                <span class="text-foreground">{{
                                    number_format($utilizationMetrics['daily_average_volume'] ?? 0, 1) }}L</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($utilizationMetrics['daily_average_volume'] ?? 0) / 1000 * 100) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Peak Day Volume -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium text-foreground">Peak Day Volume</span>
                                <span class="text-foreground">{{ number_format($utilizationMetrics['peak_day_volume'] ??
                                    0, 1) }}L</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($utilizationMetrics['peak_day_volume'] ?? 0) / 2000 * 100) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Flow Rate Efficiency -->
                        @php
                        $flowEfficiency = $pump->flow_rate_max_lpm > 0 ? (($utilizationMetrics['daily_average_volume']
                        ?? 0) / ($pump->flow_rate_max_lpm * 60 * 24)) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium text-foreground">Flow Rate Efficiency</span>
                                <span class="text-foreground">{{ number_format($flowEfficiency, 1) }}%</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full"
                                    style="width: {{ min(100, $flowEfficiency) }}%"></div>
                            </div>
                        </div>

                        <!-- Meter Resets -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium text-foreground">Meter Resets (30 days)</span>
                                <span class="text-foreground">{{ $utilizationMetrics['meter_resets'] ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-orange-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($utilizationMetrics['meter_resets'] ?? 0) * 20) }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Total Readings -->
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium text-foreground">Reading Frequency</span>
                                <span class="text-foreground">{{ $utilizationMetrics['total_readings'] ?? 0 }}
                                    readings</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-teal-600 h-2 rounded-full"
                                    style="width: {{ min(100, ($utilizationMetrics['total_readings'] ?? 0) / 60 * 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Schedule & History -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Maintenance Schedule -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Maintenance Schedule</h3>
                    <p class="text-sm text-muted-foreground">Upcoming and overdue maintenance activities</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Calibration Schedule -->
                        <div class="flex items-center justify-between p-4 border border-border rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-2 {{ $maintenanceStats['calibration_overdue'] ? 'bg-red-100 text-red-600' : ($maintenanceStats['days_until_calibration'] <= 30 ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-foreground">Calibration</h4>
                                    <p class="text-sm text-muted-foreground">{{
                                        \Carbon\Carbon::parse($pump->next_calibration_date)->format('M j, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($maintenanceStats['calibration_overdue'])
                                <span class="text-sm font-medium text-red-600">{{
                                    abs($maintenanceStats['days_until_calibration']) }} days overdue</span>
                                @else
                                <span class="text-sm font-medium text-foreground">{{
                                    $maintenanceStats['days_until_calibration'] }} days</span>
                                @endif
                                <p class="text-xs text-muted-foreground">{{ $pump->calibration_certificate ?: 'No
                                    certificate' }}</p>
                            </div>
                        </div>

                        <!-- Maintenance Schedule -->
                        <div class="flex items-center justify-between p-4 border border-border rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="p-2 {{ $maintenanceStats['maintenance_overdue'] ? 'bg-red-100 text-red-600' : ($maintenanceStats['days_until_next_maintenance'] <= 7 ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }} rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-foreground">Routine Maintenance</h4>
                                    <p class="text-sm text-muted-foreground">{{
                                        \Carbon\Carbon::parse($pump->next_maintenance_date)->format('M j, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($maintenanceStats['maintenance_overdue'])
                                <span class="text-sm font-medium text-red-600">{{
                                    abs($maintenanceStats['days_until_next_maintenance']) }} days overdue</span>
                                @else
                                <span class="text-sm font-medium text-foreground">{{
                                    $maintenanceStats['days_until_next_maintenance'] }} days</span>
                                @endif
                                <p class="text-xs text-muted-foreground">6-month interval</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Recent Activity</h3>
                    <p class="text-sm text-muted-foreground">Latest meter readings and operational events</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @forelse($recentReadings->take(10) as $reading)
                        <div class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="p-1.5 bg-primary/10 text-primary rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-foreground">{{
                                        ucfirst(strtolower($reading->reading_shift)) }} Reading</p>
                                    <p class="text-xs text-muted-foreground">{{
                                        \Carbon\Carbon::parse($reading->reading_date)->format('M j, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-foreground">{{
                                    number_format($reading->meter_reading_liters, 3) }}L</p>
                                @if($reading->meter_reset_occurred)
                                <p class="text-xs text-orange-600">Reset occurred</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-foreground mb-2">No Recent Activity</h3>
                            <p class="text-muted-foreground">No meter readings recorded in the last 30 days</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Specifications -->
        <div class="card">
            <div class="p-6 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Technical Specifications</h3>
                <p class="text-sm text-muted-foreground">Pump configuration and operational parameters</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Manufacturer</p>
                        <p class="text-base text-foreground">{{ $pump->pump_manufacturer ?: 'Not specified' }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ $pump->pump_model ?: 'Model not specified' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Flow Rate Range</p>
                        <p class="text-base text-foreground">{{ $pump->flow_rate_min_lpm }} - {{
                            $pump->flow_rate_max_lpm }} L/min</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ $pump->meter_type }} meter</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Configuration</p>
                        <p class="text-base text-foreground">{{ $pump->nozzle_count }} nozzle{{ $pump->nozzle_count > 1
                            ? 's' : '' }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            @if($pump->has_preset_capability)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Preset</span>
                            @endif
                            @if($pump->has_card_reader)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Card
                                Reader</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Installation</p>
                        <p class="text-base text-foreground">{{
                            \Carbon\Carbon::parse($pump->installation_date)->format('M j, Y') }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{
                            \Carbon\Carbon::parse($pump->installation_date)->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Volume Trend Chart
    const volumeChart = echarts.init(document.getElementById('volume-chart'));

    const recentReadings = @json($recentReadings);

    // Process data for chart
    const dates = [];
    const volumes = [];
    const dailyVolumes = [];

    let previousReading = null;

    recentReadings.reverse().forEach((reading, index) => {
        const date = new Date(reading.reading_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        dates.push(date);
        volumes.push(parseFloat(reading.meter_reading_liters));

        if (previousReading && !reading.meter_reset_occurred) {
            const dailyVolume = parseFloat(reading.meter_reading_liters) - parseFloat(previousReading.meter_reading_liters);
            dailyVolumes.push(Math.max(0, dailyVolume));
        } else {
            dailyVolumes.push(0);
        }

        previousReading = reading;
    });

    const volumeOption = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross'
            },
            formatter: function(params) {
                let result = params[0].name + '<br/>';
                params.forEach(function(param) {
                    if (param.seriesName === 'Meter Reading') {
                        result += param.marker + param.seriesName + ': ' + Number(param.value).toLocaleString() + 'L<br/>';
                    } else {
                        result += param.marker + param.seriesName + ': ' + Number(param.value).toLocaleString() + 'L<br/>';
                    }
                });
                return result;
            }
        },
        legend: {
            data: ['Meter Reading', 'Daily Volume'],
            top: 10
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                boundaryGap: false,
                data: dates
            }
        ],
        yAxis: [
            {
                type: 'value',
                name: 'Meter Reading (L)',
                position: 'left',
                axisLabel: {
                    formatter: function(value) {
                        return (value / 1000).toFixed(0) + 'K';
                    }
                }
            },
            {
                type: 'value',
                name: 'Daily Volume (L)',
                position: 'right',
                axisLabel: {
                    formatter: function(value) {
                        return value.toFixed(0);
                    }
                }
            }
        ],
        series: [
            {
                name: 'Meter Reading',
                type: 'line',
                yAxisIndex: 0,
                data: volumes,
                smooth: true,
                lineStyle: {
                    color: '#3b82f6',
                    width: 3
                },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [
                            { offset: 0, color: 'rgba(59, 130, 246, 0.3)' },
                            { offset: 1, color: 'rgba(59, 130, 246, 0.1)' }
                        ]
                    }
                }
            },
            {
                name: 'Daily Volume',
                type: 'bar',
                yAxisIndex: 1,
                data: dailyVolumes,
                itemStyle: {
                    color: '#10b981'
                },
                barWidth: '60%'
            }
        ],
        color: ['#3b82f6', '#10b981']
    };

    volumeChart.setOption(volumeOption);

    // Make chart responsive
    window.addEventListener('resize', function() {
        volumeChart.resize();
    });
});
</script>
@endpush
@endsection
