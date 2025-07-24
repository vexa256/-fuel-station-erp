@extends('layouts.app')

@section('title', 'Readings Dashboard - Fuel Operations Center')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 transition-colors duration-200">
                <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                Dashboard
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                </svg>
                <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2 dark:text-slate-400">Readings</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Readings Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Monitor fuel readings, detect variances, and ensure operational compliance across all stations
        </p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center space-x-3">
        <button type="button" onclick="refreshDashboard()" title="Refresh Dashboard (F5)"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-10 px-4 py-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
        <a href="{{ route('readings.morning') }}" title="Morning Entry (Alt+M)"
           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 bg-slate-900 text-slate-50 hover:bg-slate-900/90 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 h-10 px-4 py-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Morning Entry
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Real-time Status Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Complete Readings Card -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Complete</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-50" id="complete-count">{{ $readingStats['complete'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/20">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-emerald-600 dark:text-emerald-400">
                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    All readings within tolerance
                </div>
            </div>
        </div>
    </div>

    <!-- Partial Readings Card -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Partial</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-50" id="partial-count">{{ $readingStats['partial'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/20">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-amber-600 dark:text-amber-400">
                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    Missing some readings
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Readings Card -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">Overdue</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-50" id="overdue-count">{{ $readingStats['overdue'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-red-600 dark:text-red-400">
                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    Past deadline - CEO notified
                </div>
            </div>
        </div>
    </div>

    <!-- Variance Alerts Card -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-violet-600 dark:text-violet-400">Variances</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-50" id="variance-count">{{ $readingStats['variance'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/20">
                    <svg class="h-6 w-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-violet-600 dark:text-violet-400">
                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Investigation required
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Navigation -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('readings.morning') }}" class="group rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 transition-colors group-hover:bg-blue-200 dark:bg-blue-900/20 dark:group-hover:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-2">Morning Readings</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">Enter tank dip readings for morning shift operations</p>
            <div class="mt-4 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                Alt+M
            </div>
        </div>
    </a>

    <a href="{{ route('readings.evening') }}" class="group rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 transition-colors group-hover:bg-orange-200 dark:bg-orange-900/20 dark:group-hover:bg-orange-900/30">
                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-2">Evening Reconciliation</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">Complete daily reconciliation with variance analysis</p>
            <div class="mt-4 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                Alt+E
            </div>
        </div>
    </a>

    <a href="{{ route('readings.meters') }}" class="group rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 transition-colors group-hover:bg-emerald-200 dark:bg-emerald-900/20 dark:group-hover:bg-emerald-900/30">
                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-2">Meter Readings</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">Record pump meter progressions with tamper detection</p>
            <div class="mt-4 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                Alt+R
            </div>
        </div>
    </a>
</div>

<!-- Recent Activity & Variance Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Readings Table -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="flex flex-col space-y-1.5 p-6 pb-4">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Recent Readings</h3>
            <div class="flex space-x-2">
                <button type="button" onclick="filterReadings('today')"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 px-3 py-1">
                    Today
                </button>
                <button type="button" onclick="filterReadings('week')"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 px-3 py-1">
                    Week
                </button>
            </div>
        </div>
        <div class="p-6 pt-0">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b border-slate-200 transition-colors hover:bg-slate-100/50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Tank</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Shift</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Reading</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Status</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Time</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0" id="recent-readings">
                        @forelse($recentReadings ?? [] as $reading)
                        <tr class="border-b border-slate-200 transition-colors hover:bg-slate-100/50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                            <td class="p-4 align-middle font-medium">{{ $reading->tank->name ?? 'N/A' }}</td>
                            <td class="p-4 align-middle">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $reading->reading_shift === 'MORNING' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' }}">
                                    {{ $reading->reading_shift }}
                                </span>
                            </td>
                            <td class="p-4 align-middle">{{ number_format($reading->volume_liters ?? 0) }}L</td>
                            <td class="p-4 align-middle">
                                @if($reading->variance_percentage && abs($reading->variance_percentage) > 1.0)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400">Variance</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">Normal</span>
                                @endif
                            </td>
                            <td class="p-4 align-middle text-sm text-slate-500 dark:text-slate-400">{{ $reading->created_at?->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">No recent readings found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Variance Alerts -->
    <div class="rounded-lg border border-slate-200 bg-white text-slate-950 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50">
        <div class="flex flex-col space-y-1.5 p-6 pb-4">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Variance Alerts</h3>
            <a href="{{ route('readings.variance') }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 bg-slate-900 text-slate-50 hover:bg-slate-900/90 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90 h-8 px-3 py-1 w-fit">
                View All
            </a>
        </div>
        <div class="p-6 pt-0">
            <div class="space-y-4" id="variance-alerts">
                @forelse($varianceAlerts ?? [] as $alert)
                <div class="flex items-start space-x-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ $alert->tank->name ?? 'Unknown Tank' }} - {{ number_format(abs($alert->variance_percentage), 2) }}% variance
                        </p>
                        <p class="mt-1 text-xs text-red-600 dark:text-red-300">
                            {{ $alert->created_at?->diffForHumans() }} • Investigation required
                        </p>
                    </div>
                    <button type="button" onclick="investigateVariance({{ $alert->id }})"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border border-red-200 bg-red-50 hover:bg-red-100 hover:text-red-900 dark:border-red-800 dark:bg-red-900/20 dark:hover:bg-red-900/30 dark:hover:text-red-50 h-7 px-2 py-1 text-xs">
                        Investigate
                    </button>
                </div>
                @empty
                <div class="py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No variance alerts</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Real-time Updates and Keyboard Shortcuts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.altKey) {
            switch(e.key.toLowerCase()) {
                case 'm':
                    e.preventDefault();
                    window.location.href = '{{ route("readings.morning") }}';
                    break;
                case 'e':
                    e.preventDefault();
                    window.location.href = '{{ route("readings.evening") }}';
                    break;
                case 'r':
                    e.preventDefault();
                    window.location.href = '{{ route("readings.meters") }}';
                    break;
                case 'v':
                    e.preventDefault();
                    window.location.href = '{{ route("readings.variance") }}';
                    break;
            }
        }
        if (e.key === 'F5') {
            e.preventDefault();
            refreshDashboard();
        }
    });

    // Auto-refresh every 30 seconds
    setInterval(refreshDashboard, 30000);
});

function refreshDashboard() {
    // Show loading state
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Refreshing...';

    // Fetch updated data
    fetch('{{ route("readings.index") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update counters
        document.getElementById('complete-count').textContent = data.readingStats.complete || 0;
        document.getElementById('partial-count').textContent = data.readingStats.partial || 0;
        document.getElementById('overdue-count').textContent = data.readingStats.overdue || 0;
        document.getElementById('variance-count').textContent = data.readingStats.variance || 0;

        // Update recent readings table
        updateRecentReadings(data.recentReadings);

        // Update variance alerts
        updateVarianceAlerts(data.varianceAlerts);

        // Restore button
        refreshBtn.innerHTML = originalContent;

        // Show success notification
        showNotification('Dashboard refreshed successfully', 'success');
    })
    .catch(error => {
        console.error('Refresh failed:', error);
        refreshBtn.innerHTML = originalContent;
        showNotification('Failed to refresh dashboard', 'error');
    });
}

function filterReadings(period) {
    // Implementation for filtering readings by period
    console.log('Filtering readings for:', period);
}

function investigateVariance(alertId) {
    // Navigate to variance investigation
    window.location.href = `{{ route("readings.variance") }}?alert=${alertId}`;
}

function showNotification(message, type) {
    // Simple notification system using shadcn/ui styling
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 rounded-lg border p-4 shadow-lg transition-all duration-300 ${
        type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400'
            : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
