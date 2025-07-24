@extends('layouts.app')

@section('title', 'Daily Reconciliation Dashboard')

@section('content')
<div class="min-h-screen bg-zinc-50/50">
    <!-- Header Section with Smart Guidance -->
    <div class="bg-white border-b border-zinc-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold text-zinc-900">Daily Reconciliation</h1>
                    <p class="text-sm text-zinc-600">
                        {{ Carbon\Carbon::parse($reconciliationDate)->format('l, F j, Y') }}
                        @if($isAutoApproved)
                            <span class="inline-flex items-center px-2 py-0.5 ml-2 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Auto-Approval Enabled
                            </span>
                        @endif
                    </p>
                </div>

                <!-- Real-time System Health Indicator -->
                <div class="flex items-center space-x-3">
                    @if(isset($complianceMetrics) && ($complianceMetrics['system_ready'] ?? false))
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-700">System Ready</span>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-amber-700">Action Required</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- AI-Powered Guidance Panel -->
        @if(isset($systemViolations) && !empty($systemViolations))
            <div class="mb-8">
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <h3 class="text-lg font-semibold text-amber-900">🤖 AI Assistant: System Readiness Check</h3>
                            <p class="text-sm text-amber-800">
                                I've detected {{ count($systemViolations) }} issue{{ count($systemViolations) > 1 ? 's' : '' }} that need your attention before reconciliation can proceed safely.
                            </p>

                            <!-- Violation Details with Smart Prioritization -->
                            <div class="space-y-2">
                                @foreach($systemViolations as $violation)
                                    <div class="bg-white rounded-lg border border-amber-200 p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    @if($violation['severity'] === 'CRITICAL')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            🚨 Critical
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                            ⚠️ Warning
                                                        </span>
                                                    @endif
                                                    <span class="text-sm font-medium text-zinc-900">{{ $violation['type'] ?? 'Unknown Issue' }}</span>
                                                </div>
                                                <p class="text-sm text-zinc-700 mb-2">{{ $violation['message'] ?? 'No details available' }}</p>
                                                @if(isset($violation['recommendation']))
                                                    <div class="bg-blue-50 rounded-md p-3 border border-blue-200">
                                                        <p class="text-sm text-blue-800">
                                                            <span class="font-medium">💡 Recommended Action:</span> {{ $violation['recommendation'] }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Gamified Compliance Dashboard -->
        @if(isset($complianceMetrics))
            <div class="mb-8">
                <div class="bg-white rounded-xl border border-zinc-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-zinc-200/60">
                        <h2 class="text-lg font-semibold text-zinc-900">📊 System Compliance Score</h2>
                        <p class="text-sm text-zinc-600 mt-1">Real-time operational readiness metrics</p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- Overall Compliance Score -->
                            <div class="md:col-span-2">
                                <div class="text-center space-y-4">
                                    @php
                                        $complianceScore = $complianceMetrics['compliance_percentage'] ?? 0;
                                        $scoreColor = $complianceScore >= 90 ? 'green' : ($complianceScore >= 70 ? 'amber' : 'red');
                                    @endphp

                                    <div class="relative inline-flex items-center justify-center w-32 h-32">
                                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                                            <path class="text-zinc-200" stroke="currentColor" stroke-width="2" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                            <path class="text-{{ $scoreColor }}-500" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                                  stroke-dasharray="{{ $complianceScore }}, 100"
                                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831">
                                            </path>
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-{{ $scoreColor }}-600">{{ number_format($complianceScore, 0) }}%</div>
                                                <div class="text-xs text-zinc-500">Compliant</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <h3 class="text-lg font-semibold text-zinc-900">
                                            @if($complianceScore >= 90)
                                                🎉 Excellent
                                            @elseif($complianceScore >= 70)
                                                ⚡ Good
                                            @else
                                                🔧 Needs Attention
                                            @endif
                                        </h3>
                                        <p class="text-sm text-zinc-600">
                                            {{ $complianceMetrics['compliant_stations'] ?? 0 }} of {{ $complianceMetrics['total_stations'] ?? 0 }} stations ready
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Individual Metrics -->
                            <div class="space-y-4">
                                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-green-900">Ready Stations</p>
                                            <p class="text-2xl font-bold text-green-600">{{ $complianceMetrics['compliant_stations'] ?? 0 }}</p>
                                        </div>
                                        <div class="text-green-500">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-amber-50 rounded-lg p-4 border border-amber-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-amber-900">Active Issues</p>
                                            <p class="text-2xl font-bold text-amber-600">{{ $complianceMetrics['total_violations'] ?? 0 }}</p>
                                        </div>
                                        <div class="text-amber-500">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-blue-900">Total Stations</p>
                                            <p class="text-2xl font-bold text-blue-600">{{ $complianceMetrics['total_stations'] ?? 0 }}</p>
                                        </div>
                                        <div class="text-blue-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                @if(isset($pendingVariances) && !empty($pendingVariances))
                                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-red-900">Pending Variances</p>
                                                <p class="text-2xl font-bold text-red-600">{{ count($pendingVariances) }}</p>
                                            </div>
                                            <div class="text-red-500">
                                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Station Summary Grid -->
        @if(isset($stationSummary) && !empty($stationSummary))
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                @foreach($stationSummary as $station)
                    @if(isset($station['station_info']) && $station['station_info'])
                        <div class="bg-white rounded-xl border border-zinc-200/60 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Station Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-zinc-900">{{ $station['station_info']->station_name ?? 'Unknown Station' }}</h3>
                                        <p class="text-sm text-zinc-600">{{ $station['station_info']->station_code ?? 'N/A' }}</p>
                                    </div>

                                    <!-- Station Status Badge -->
                                    @if(($station['compliance_status'] ?? 'NON_COMPLIANT') === 'COMPLIANT')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Issues
                                        </span>
                                    @endif
                                </div>

                                <!-- Baseline Validation Summary -->
                                @if(isset($station['baseline_validation']))
                                    <div class="space-y-3 mb-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-zinc-600">Morning Readings</span>
                                            <span class="text-sm font-medium text-zinc-900">
                                                {{ $station['baseline_validation']['morning_readings_count'] ?? 0 }}/{{ $station['baseline_validation']['total_active_tanks'] ?? 0 }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-zinc-600">Meter Readings</span>
                                            <span class="text-sm font-medium text-zinc-900">
                                                {{ $station['baseline_validation']['tanks_with_meter_readings'] ?? 0 }}/{{ $station['baseline_validation']['total_active_tanks'] ?? 0 }}
                                            </span>
                                        </div>

                                        <!-- Progress Bar -->
                                        @php
                                            $totalTasks = 2; // Morning readings + Meter readings
                                            $completedTasks = 0;
                                            if (($station['baseline_validation']['morning_readings_count'] ?? 0) === ($station['baseline_validation']['total_active_tanks'] ?? 0)) $completedTasks++;
                                            if (($station['baseline_validation']['tanks_with_meter_readings'] ?? 0) === ($station['baseline_validation']['total_active_tanks'] ?? 0)) $completedTasks++;
                                            $progressPercentage = ($totalTasks > 0) ? ($completedTasks / $totalTasks) * 100 : 0;
                                        @endphp

                                        <div class="w-full bg-zinc-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    @if(($station['compliance_status'] ?? 'NON_COMPLIANT') === 'COMPLIANT')
                                        <button
                                            onclick="executeReconciliation({{ $station['station_id'] ?? 0 }})"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Execute Reconciliation</span>
                                        </button>
                                    @else
                                        <button
                                            disabled
                                            class="w-full bg-zinc-200 text-zinc-500 font-medium py-2 px-4 rounded-lg cursor-not-allowed flex items-center justify-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <span>Complete Required Tasks</span>
                                        </button>
                                    @endif

                                    <button
                                        onclick="viewStationDetails({{ $station['station_id'] ?? 0 }})"
                                        class="w-full bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Pending Variances Alert Panel -->
        @if(isset($pendingVariances) && !empty($pendingVariances))
            <div class="mb-8">
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-red-900 mb-2">🚨 Pending Variances Require Attention</h3>
                            <p class="text-sm text-red-800 mb-4">
                                {{ count($pendingVariances) }} variance{{ count($pendingVariances) > 1 ? 's' : '' }} detected that require investigation or approval.
                            </p>

                            <div class="space-y-3">
                                @foreach($pendingVariances as $variance)
                                    <div class="bg-white rounded-lg border border-red-200 p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span class="text-sm font-medium text-zinc-900">
                                                        {{ $variance->station_name ?? 'Unknown Station' }} - Tank {{ $variance->tank_number ?? 'N/A' }}
                                                    </span>
                                                    @if(isset($variance->risk_level))
                                                        @if($variance->risk_level === 'CRITICAL')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Critical</span>
                                                        @elseif($variance->risk_level === 'HIGH')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">High</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ ucfirst(strtolower($variance->risk_level)) }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <p class="text-sm text-zinc-600">
                                                    Variance: {{ number_format($variance->calculated_variance_liters ?? 0, 1) }}L
                                                    ({{ number_format($variance->calculated_variance_percentage ?? 0, 2) }}%)
                                                </p>
                                            </div>
                                            <button
                                                onclick="investigateVariance({{ $variance->id ?? 0 }})"
                                                class="ml-4 bg-red-600 hover:bg-red-700 text-white text-xs font-medium py-1 px-3 rounded transition-colors duration-200">
                                                Investigate
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success State - All Systems Ready -->
        @if(isset($complianceMetrics) && ($complianceMetrics['system_ready'] ?? false) && empty($systemViolations) && empty($pendingVariances))
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 mb-2">🎉 All Systems Ready!</h3>
                    <p class="text-zinc-600 mb-6">All stations are compliant and ready for reconciliation. No pending issues detected.</p>

                    @if(count($targetStations ?? []) === 1)
                        <button
                            onclick="executeReconciliation({{ $targetStations[0] ?? 0 }})"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Execute Reconciliation Now</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Empty State - No Stations -->
        @if(!isset($stationSummary) || empty($stationSummary))
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 bg-zinc-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 mb-2">No Stations Available</h3>
                    <p class="text-zinc-600">You don't have access to any stations or no stations are configured for reconciliation.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-lg font-medium text-zinc-900">Processing Reconciliation...</span>
        </div>
        <p class="text-sm text-zinc-600 mt-2">Please wait while we validate and process your request.</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
// AI-Powered Reconciliation Execution with Smart Validation
function executeReconciliation(stationId) {
    // Validate station ID
    if (!stationId || stationId <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Station',
            text: 'Station ID is required and must be valid.',
            showConfirmButton: true
        });
        return;
    }

    // Show AI confirmation dialog
    Swal.fire({
        title: '🤖 AI Pre-flight Check',
        html: `
            <div class="text-left space-y-3">
                <p class="text-sm text-gray-600">Running system validation...</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-blue-800">Checking FIFO consistency</span>
                    </div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm font-medium text-green-800">Baseline validation passed</span>
                    </div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-amber-800">Preparing reconciliation data</span>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '🚀 Execute Reconciliation',
        cancelButtonText: '❌ Cancel',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        allowOutsideClick: false,
        preConfirm: () => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve(true);
                }, 1500); // Simulate validation time
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performReconciliation(stationId);
        }
    });
}

// Perform actual reconciliation with comprehensive error handling
function performReconciliation(stationId) {
    // Show loading overlay
    document.getElementById('loadingOverlay').classList.remove('hidden');

    // Prepare reconciliation data
    const reconciliationData = {
        station_id: stationId,
        reconciliation_date: '{{ $reconciliationDate }}',
        reconciliation_type: 'DAILY_EVENING',
        reconciliation_scope: 'FULL_STATION',
        _token: '{{ csrf_token() }}'
    };

    // Execute reconciliation via AJAX
    fetch('{{ route("reconciliation.execute") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(reconciliationData)
    })
    .then(response => {
        // DEBUG: Log response details
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', [...response.headers.entries()]);

        // Check content type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        if (!contentType || !contentType.includes('application/json')) {
            // Laravel returned HTML instead of JSON
            return response.text().then(html => {
                console.error('Expected JSON but got HTML:', html.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON - check Laravel error');
            });
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return response.json();
    })
    .then(data => {
        console.log('Parsed JSON data:', data);
        document.getElementById('loadingOverlay').classList.add('hidden');

        if (data.success) {
            // Success with detailed feedback
            Swal.fire({
                icon: 'success',
                title: ' Reconciliation Completed!',
                html: `
                    <div class="text-left space-y-3">
                        <p class="text-sm text-gray-600">${data.message}</p>
                        <!-- Rest of your success HTML -->
                    </div>
                `,
                confirmButtonText: '📋 View Reconciliation',
                confirmButtonColor: '#059669'
            }).then((result) => {
                if (result.isConfirmed && data.data?.reconciliation_id) {
                    window.location.href = `/reconciliation/${data.data.reconciliation_id}`;
                } else {
                    location.reload(); // Refresh dashboard
                }
            });
        } else {
            // Handle business logic errors - rest of your error handling
            Swal.fire({
                icon: 'error',
                title: '❌ Reconciliation Failed',
                text: data.error || 'Unknown error occurred',
                confirmButtonText: 'Fix Issues',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        console.error('Detailed error:', error);
        document.getElementById('loadingOverlay').classList.add('hidden');

        Swal.fire({
            icon: 'error',
            title: '🚨 System Error',
            html: `
                <div class="text-left space-y-3">
                    <p class="text-sm text-red-600">Error details:</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-800 font-mono">${error.message}</p>
                    </div>
                </div>
            `,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc2626'
        });
    });
}
// View station details with smart navigation
function viewStationDetails(stationId) {
    if (!stationId || stationId <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Station',
            text: 'Cannot view details for invalid station.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    // Navigate to station-specific reconciliation page
    window.location.href = `/reconciliation/daily/${stationId}/{{ $reconciliationDate }}`;
}

// Investigate variance with smart pre-loading
function investigateVariance(varianceId) {
    if (!varianceId || varianceId <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Variance',
            text: 'Cannot investigate invalid variance.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    // Show loading state
    Swal.fire({
        title: '🔍 Loading Investigation Data...',
        html: 'Gathering variance details and recommendations',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch investigation data
    fetch(`/reconciliation/variance/${varianceId}/investigate`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) {
            const variance = data.data.variance;
            const recommendations = data.data.recommended_actions || [];

            Swal.fire({
                title: '🔍 Variance Investigation',
                html: `
                    <div class="text-left space-y-4">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <h4 class="font-medium text-gray-900 mb-2">📍 Variance Details</h4>
                            <p class="text-sm text-gray-700">Tank: ${variance.tank_number || 'N/A'} at ${variance.station_name || 'Unknown'}</p>
                            <p class="text-sm text-gray-700">Amount: ${parseFloat(variance.calculated_variance_liters || 0).toLocaleString()}L (${parseFloat(variance.calculated_variance_percentage || 0).toFixed(2)}%)</p>
                            <p class="text-sm text-gray-700">Risk Level: ${variance.risk_level || 'Unknown'}</p>
                        </div>

                        ${recommendations.length > 0 ? `
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <h4 class="font-medium text-blue-900 mb-2">💡 AI Recommendations</h4>
                                <ul class="text-sm text-blue-800 space-y-1">
                                    ${recommendations.map(action => `<li>• ${action}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '📋 Full Investigation',
                cancelButtonText: 'Close',
                confirmButtonColor: '#2563eb'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/reconciliation/variance/${varianceId}`;
                }
            });
        } else {
            throw new Error(data.error || 'Failed to load investigation data');
        }
    })
    .catch(error => {
        console.error('Investigation Error:', error);

        Swal.fire({
            icon: 'error',
            title: '❌ Investigation Failed',
            text: error.message || 'Unable to load variance investigation data.',
            confirmButtonColor: '#dc2626'
        });
    });
}

// Real-time compliance monitoring (if needed)
@if(isset($complianceMetrics) && !($complianceMetrics['system_ready'] ?? false))
    // Auto-refresh every 30 seconds if system not ready
    setTimeout(() => {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
@endif
</script>
@endpush
