@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100/50" x-data="{ tab: '{{ $tanksNeedingReadings->count() > 0 ? 'pending' : 'overview' }}' }">

    <!-- Premium Header with Glassmorphism -->
    <div class="sticky top-0 z-10 backdrop-blur-xl bg-white/80 border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center py-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                        Evening Readings
                    </h1>
                    <div class="flex items-center space-x-3 text-sm">
                        <span class="text-slate-600">{{ $currentDate }}</span>
                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $timeValidation['valid'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            <i class="lucide-clock w-3 h-3 mr-1"></i>
                            {{ $timeValidation['message'] }}
                        </span>
                    </div>
                </div>
                @if($tanksNeedingReadings->count() > 0)
                <a href="{{ route('evening.readings.create') }}"
                   class="group relative overflow-hidden bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-medium shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 hover:-translate-y-0.5">
                    <div class="flex items-center">
                        <i class="lucide-plus w-4 h-4 mr-2"></i>
                        Add Reading
                    </div>
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Premium Tab Navigation -->
    <div class="max-w-7xl mx-auto px-6 pt-6">
        <nav class="flex space-x-1 bg-slate-100/50 rounded-xl p-1">
            <button @click="tab = 'overview'"
                    :class="tab === 'overview' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium text-sm transition-all duration-200">
                <i class="lucide-layout-dashboard w-4 h-4 mr-2"></i>
                Overview
            </button>
            <button @click="tab = 'pending'"
                    :class="tab === 'pending' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium text-sm transition-all duration-200 relative">
                <i class="lucide-clock w-4 h-4 mr-2"></i>
                Pending
                @if($tanksNeedingReadings->count() > 0)
                <span class="ml-2 bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $tanksNeedingReadings->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'completed'"
                    :class="tab === 'completed' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium text-sm transition-all duration-200">
                <i class="lucide-check-circle w-4 h-4 mr-2"></i>
                Completed
                @if($completedReadings->count() > 0)
                <span class="ml-2 bg-emerald-100 text-emerald-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $completedReadings->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'stations'"
                    :class="tab === 'stations' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium text-sm transition-all duration-200">
                <i class="lucide-building w-4 h-4 mr-2"></i>
                Stations
            </button>
        </nav>
    </div>

    <!-- Premium Content Area -->
    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- Overview Tab -->
        <div x-show="tab === 'overview'" x-transition class="space-y-8">
            <!-- Premium Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                $stats = [
                    ['icon' => 'droplet', 'label' => 'Total Tanks', 'value' => $stationSummaries->sum('total_tanks'), 'color' => 'blue'],
                    ['icon' => 'check-circle', 'label' => 'Completed', 'value' => $completedReadings->count(), 'color' => 'emerald'],
                    ['icon' => 'clock', 'label' => 'Pending', 'value' => $tanksNeedingReadings->count(), 'color' => 'amber'],
                    ['icon' => 'alert-triangle', 'label' => 'Variances', 'value' => $stationSummaries->sum('active_variances'), 'color' => 'red']
                ];
                @endphp

                @foreach($stats as $stat)
                <div class="group relative bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:bg-white/90 transition-all duration-300 hover:shadow-xl hover:shadow-{{ $stat['color'] }}-500/10 hover:-translate-y-1">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-{{ $stat['color'] }}-100 rounded-xl group-hover:bg-{{ $stat['color'] }}-200 transition-colors">
                            <i class="lucide-{{ $stat['icon'] }} w-6 h-6 text-{{ $stat['color'] }}-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Premium Station Progress -->
            @if($stationSummaries->count() > 0)
            <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900 mb-6">Station Progress</h3>
                <div class="space-y-6">
                    @foreach($stationSummaries as $summary)
                    <div class="group flex items-center justify-between p-4 rounded-xl hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="w-3 h-3 rounded-full {{
                                $summary['status'] === 'COMPLETED' ? 'bg-emerald-500 shadow-lg shadow-emerald-500/30' :
                                ($summary['status'] === 'IN_PROGRESS' ? 'bg-blue-500 shadow-lg shadow-blue-500/30' :
                                ($summary['status'] === 'ATTENTION_REQUIRED' ? 'bg-red-500 shadow-lg shadow-red-500/30' : 'bg-slate-300'))
                            }}"></div>
                            <div>
                                <h4 class="font-semibold text-slate-900">{{ $summary['station']->station_name }}</h4>
                                <p class="text-sm text-slate-500">{{ $summary['evening_readings'] }}/{{ $summary['total_tanks'] }} completed</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-6">
                            <div class="w-32 bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500"
                                     style="width: {{ $summary['completion_rate'] }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 w-12">{{ $summary['completion_rate'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Pending Tab -->
        <div x-show="tab === 'pending'" x-transition>
            @if($tanksNeedingReadings->count() > 0)
            <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-200/60">
                    <h3 class="text-xl font-semibold text-slate-900">Tanks Requiring Readings</h3>
                    <p class="text-slate-600 mt-1">{{ $tanksNeedingReadings->count() }} tanks need evening readings</p>
                </div>
                <div class="divide-y divide-slate-200/60">
                    @foreach($tanksNeedingReadings as $tank)
                    <div class="group p-6 hover:bg-white/50 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-xl flex items-center justify-center group-hover:from-blue-100 group-hover:to-blue-200 transition-colors">
                                    <i class="lucide-droplet w-6 h-6 text-slate-600 group-hover:text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900">Tank {{ $tank->tank_number }}</h4>
                                    <p class="text-slate-600">{{ $tank->station_name }} • {{ $tank->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format($tank->capacity_liters, 0) }}L capacity</p>
                                </div>
                            </div>
                            <a href="{{ route('evening.readings.create', ['tank_id' => $tank->id]) }}"
                               class="group relative overflow-hidden bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 hover:-translate-y-0.5">
                                <i class="lucide-plus w-4 h-4 mr-2 inline"></i>
                                Add Reading
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-20">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mb-6">
                    <i class="lucide-check-circle w-10 h-10 text-emerald-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">All Complete</h3>
                <p class="text-slate-600">All evening readings have been completed for today.</p>
            </div>
            @endif
        </div>

        <!-- Completed Tab -->
        <div x-show="tab === 'completed'" x-transition>
            @if($completedReadings->count() > 0)
            <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-200/60">
                    <h3 class="text-xl font-semibold text-slate-900">Completed Readings</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr class="text-left">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Tank</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Reading</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Volume</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Variance</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60">
                            @foreach($completedReadings as $reading)
                            <tr class="hover:bg-white/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-semibold text-slate-900">Tank {{ $reading->tank_number }}</div>
                                        <div class="text-sm text-slate-600">{{ $reading->station_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-900">{{ number_format($reading->dip_reading_mm, 2) }}mm</td>
                                <td class="px-6 py-4 font-mono text-slate-900">{{ number_format($reading->dip_reading_liters, 3) }}L</td>
                                <td class="px-6 py-4">
                                    @if($reading->variance_from_expected_percentage)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{
                                        abs($reading->variance_from_expected_percentage) <= 0.5 ? 'bg-emerald-100 text-emerald-700' :
                                        (abs($reading->variance_from_expected_percentage) <= 2.0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')
                                    }}">
                                        {{ $reading->variance_from_expected_percentage > 0 ? '+' : '' }}{{ number_format($reading->variance_from_expected_percentage, 2) }}%
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{
                                        $reading->reading_status === 'VALIDATED' ? 'bg-emerald-100 text-emerald-700' :
                                        ($reading->reading_status === 'PENDING' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')
                                    }}">
                                        {{ $reading->reading_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-mono">{{ \Carbon\Carbon::parse($reading->reading_time)->format('H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="text-center py-20">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mb-6">
                    <i class="lucide-clock w-10 h-10 text-slate-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">No Readings Yet</h3>
                <p class="text-slate-600">No evening readings completed today.</p>
            </div>
            @endif
        </div>

        <!-- Stations Tab -->
        <div x-show="tab === 'stations'" x-transition>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($stationSummaries as $summary)
                <div class="group bg-white/70 backdrop-blur-sm border border-white/20 rounded-2xl p-8 hover:bg-white/90 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">{{ $summary['station']->station_name }}</h3>
                            <p class="text-slate-600">{{ $summary['station']->station_code }}</p>
                        </div>
                        <div class="w-4 h-4 rounded-full {{
                            $summary['status'] === 'COMPLETED' ? 'bg-emerald-500 shadow-lg shadow-emerald-500/40' :
                            ($summary['status'] === 'IN_PROGRESS' ? 'bg-blue-500 shadow-lg shadow-blue-500/40' :
                            ($summary['status'] === 'ATTENTION_REQUIRED' ? 'bg-red-500 shadow-lg shadow-red-500/40' : 'bg-slate-300'))
                        }}"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        @php
                        $metrics = [
                            ['label' => 'Total Tanks', 'value' => $summary['total_tanks']],
                            ['label' => 'Morning', 'value' => $summary['morning_readings']],
                            ['label' => 'Evening', 'value' => $summary['evening_readings']],
                            ['label' => 'Pending', 'value' => $summary['pending_readings'], 'highlight' => $summary['pending_readings'] > 0]
                        ];
                        @endphp

                        @foreach($metrics as $metric)
                        <div class="text-center">
                            <p class="text-2xl font-bold {{ isset($metric['highlight']) && $metric['highlight'] ? 'text-amber-600' : 'text-slate-900' }}">{{ $metric['value'] }}</p>
                            <p class="text-xs text-slate-600 font-medium">{{ $metric['label'] }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-slate-600">Completion</span>
                            <span class="text-lg font-bold text-slate-900">{{ $summary['completion_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-700"
                                 style="width: {{ $summary['completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
