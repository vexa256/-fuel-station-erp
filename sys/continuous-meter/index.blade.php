@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100" x-data="continuousMeterDashboard()">
    <!-- Header Section -->
    <div class="border-b border-slate-200 bg-white/80 backdrop-blur-sm sticky top-0 z-10">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-900">Continuous Meter Dashboard</h1>
                        <p class="text-sm text-slate-600">{{ $currentDate }} - {{ $currentShift }} Shift</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($isAutoApproved)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-crown mr-1"></i>Auto-Approved
                        </span>
                    @endif
                    <div class="text-xs text-slate-500">
                        Load: {{ number_format($executionTime, 0) }}ms
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="px-6 pt-6">
        <nav class="flex space-x-1 mb-6" role="tablist">
            <template x-for="(tab, index) in tabs" :key="tab.id">
                <button
                    @click="activeTab = tab.id"
                    :class="{
                        'bg-white text-blue-600 shadow-sm border-blue-200': activeTab === tab.id,
                        'text-slate-600 hover:text-slate-900': activeTab !== tab.id
                    }"
                    class="relative px-4 py-2.5 text-sm font-medium rounded-lg border border-transparent transition-all duration-200 hover:bg-white hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    role="tab"
                    :aria-selected="activeTab === tab.id"
                >
                    <div class="flex items-center space-x-2">
                        <i :class="tab.icon" class="text-sm"></i>
                        <span x-text="tab.name"></span>
                        <span x-show="tab.badge > 0"
                              x-text="tab.badge"
                              class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                        </span>
                    </div>
                </button>
            </template>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="px-6 pb-6">
        <!-- Pump Status Tab -->
        <div x-show="activeTab === 'pumps'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($pumpStatusData as $pump)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-6">
                            <!-- Pump Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                                @if($pump->latest_reading_date === $currentDate) bg-emerald-100 @else bg-yellow-100 @endif">
                                        <i class="fas fa-gas-pump text-sm
                                           @if($pump->latest_reading_date === $currentDate) text-emerald-600 @else text-yellow-600 @endif"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $pump->station_name }}</div>
                                        <div class="text-sm text-slate-600">Pump {{ $pump->pump_number }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($pump->meter_reset_occurred)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full">
                                            <i class="fas fa-redo-alt mr-1"></i>Reset
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                                @if($pump->latest_reading_date === $currentDate) bg-emerald-100 text-emerald-800 @else bg-red-100 text-red-800 @endif">
                                        @if($pump->latest_reading_date === $currentDate) Current @else Outdated @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Pump Details -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Product</span>
                                    <span class="text-sm font-medium text-slate-900">{{ $pump->product_name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Tank</span>
                                    <span class="text-sm font-medium text-slate-900">{{ $pump->tank_number }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Latest Reading</span>
                                    <span class="text-sm font-medium text-slate-900">
                                        @if($pump->latest_meter_reading)
                                            {{ number_format($pump->latest_meter_reading, 3) }}L
                                        @else
                                            <span class="text-red-600">No Reading</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">Date/Shift</span>
                                    <span class="text-sm font-medium text-slate-900">
                                        @if($pump->latest_reading_date)
                                            {{ $pump->latest_reading_date }} / {{ $pump->latest_reading_shift }}
                                        @else
                                            No Data
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <div class="flex space-x-2">
                                    <button @click="createReading({{ $pump->pump_id }})"
                                            class="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <i class="fas fa-plus mr-1"></i>Add Reading
                                    </button>
                                    @if($pump->latest_reading_id)
                                        <button @click="viewReading({{ $pump->latest_reading_id }})"
                                                class="px-3 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-gas-pump text-slate-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-900 mb-2">No Pumps Available</h3>
                            <p class="text-slate-600">No pumps are assigned to your stations.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Morning Baselines Tab -->
        <div x-show="activeTab === 'baselines'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Morning Baseline Readings</h3>
                    <p class="text-sm text-slate-600 mt-1">Tank dip readings for {{ $currentDate }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Station</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reading (L)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reading (mm)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Temperature</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($morningAnchors as $anchor)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                        {{ $anchor->station_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        Tank {{ $anchor->tank_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ number_format($anchor->dip_reading_liters, 3) }}L
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ number_format($anchor->dip_reading_mm, 1) }}mm
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        @if($anchor->temperature_celsius)
                                            {{ number_format($anchor->temperature_celsius, 1) }}°C
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <i class="fas fa-check-circle mr-1"></i>Complete
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="w-12 h-12 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-ruler-vertical text-slate-400"></i>
                                        </div>
                                        <h3 class="text-sm font-medium text-slate-900 mb-2">No Morning Readings</h3>
                                        <p class="text-sm text-slate-600">No morning baseline readings found for today.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reconciliation Tab -->
        <div x-show="activeTab === 'reconciliation'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="space-y-6">
                @foreach($stationReconciliationData as $stationId => $reconciliation)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">Station {{ $stationId }} Reconciliation</h3>
                                <div class="flex items-center space-x-2">
                                    @if($reconciliation['baseline_complete'] ?? false)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <i class="fas fa-check-circle mr-1"></i>Complete
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Incomplete
                                        </span>
                                    @endif
                                    <button @click="runReconciliation({{ $stationId }})"
                                            class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <i class="fas fa-calculator mr-1"></i>Run
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if(isset($reconciliation['error']))
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex">
                                        <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>
                                        <div>
                                            <h4 class="text-sm font-medium text-red-800">Reconciliation Error</h4>
                                            <p class="text-sm text-red-700 mt-1">{{ $reconciliation['error'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-slate-900">
                                            {{ $reconciliation['tanks_count'] ?? 0 }}
                                        </div>
                                        <div class="text-sm text-slate-600">Tanks</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-slate-900">
                                            {{ $reconciliation['readings_count'] ?? 0 }}
                                        </div>
                                        <div class="text-sm text-slate-600">Readings</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold {{ ($reconciliation['baseline_complete'] ?? false) ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ ($reconciliation['baseline_complete'] ?? false) ? '100%' : '0%' }}
                                        </div>
                                        <div class="text-sm text-slate-600">Complete</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- System Health Tab -->
        <div x-show="activeTab === 'health'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">System Health Status</h3>
                    <p class="text-sm text-slate-600 mt-1">Real-time system monitoring and automation status</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center
                                        {{ ($systemHealth['overall_score'] ?? 0) >= 80 ? 'bg-emerald-100' : 'bg-red-100' }}">
                                <i class="fas fa-heartbeat text-xl {{ ($systemHealth['overall_score'] ?? 0) >= 80 ? 'text-emerald-600' : 'text-red-600' }}"></i>
                            </div>
                            <div class="text-2xl font-bold {{ ($systemHealth['overall_score'] ?? 0) >= 80 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $systemHealth['overall_score'] ?? 0 }}%
                            </div>
                            <div class="text-sm text-slate-600">Overall Health</div>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center
                                        {{ ($systemHealth['fifo_consistency'] ?? 0) >= 95 ? 'bg-emerald-100' : 'bg-yellow-100' }}">
                                <i class="fas fa-layer-group text-xl {{ ($systemHealth['fifo_consistency'] ?? 0) >= 95 ? 'text-emerald-600' : 'text-yellow-600' }}"></i>
                            </div>
                            <div class="text-2xl font-bold {{ ($systemHealth['fifo_consistency'] ?? 0) >= 95 ? 'text-emerald-600' : 'text-yellow-600' }}">
                                {{ $systemHealth['fifo_consistency'] ?? 0 }}%
                            </div>
                            <div class="text-sm text-slate-600">FIFO Consistency</div>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center
                                        {{ ($systemHealth['automation_status'] ?? false) ? 'bg-emerald-100' : 'bg-red-100' }}">
                                <i class="fas fa-cogs text-xl {{ ($systemHealth['automation_status'] ?? false) ? 'text-emerald-600' : 'text-red-600' }}"></i>
                            </div>
                            <div class="text-lg font-bold {{ ($systemHealth['automation_status'] ?? false) ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ($systemHealth['automation_status'] ?? false) ? 'ON' : 'OFF' }}
                            </div>
                            <div class="text-sm text-slate-600">Automation</div>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center
                                        {{ ($systemHealth['data_integrity'] ?? 0) >= 99 ? 'bg-emerald-100' : 'bg-red-100' }}">
                                <i class="fas fa-shield-alt text-xl {{ ($systemHealth['data_integrity'] ?? 0) >= 99 ? 'text-emerald-600' : 'text-red-600' }}"></i>
                            </div>
                            <div class="text-2xl font-bold {{ ($systemHealth['data_integrity'] ?? 0) >= 99 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $systemHealth['data_integrity'] ?? 0 }}%
                            </div>
                            <div class="text-sm text-slate-600">Data Integrity</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function continuousMeterDashboard() {
    return {
        activeTab: 'pumps',
        loading: false,
        tabs: [
            { id: 'pumps', name: 'Pump Status', icon: 'fas fa-gas-pump', badge: {{ count($pumpStatusData) }} },
            { id: 'baselines', name: 'Morning Baselines', icon: 'fas fa-ruler-vertical', badge: {{ count($morningAnchors) }} },
            { id: 'reconciliation', name: 'Reconciliation', icon: 'fas fa-calculator', badge: 0 },
            { id: 'health', name: 'System Health', icon: 'fas fa-heartbeat', badge: 0 }
        ],

        init() {
            // Auto-refresh every 5 minutes
            setInterval(() => {
                if (!this.loading) {
                    this.refreshData();
                }
            }, 300000);
        },

        createReading(pumpId) {
            if (!pumpId || pumpId <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Pump',
                    text: 'Please select a valid pump to create a reading.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            window.location.href = `/continuous-meter/create?pump_id=${pumpId}`;
        },

        viewReading(readingId) {
            if (!readingId || readingId <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Reading',
                    text: 'Reading ID is invalid.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            window.location.href = `/continuous-meter/${readingId}`;
        },

        runReconciliation(stationId) {
            if (!stationId || stationId <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Station',
                    text: 'Please select a valid station for reconciliation.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            Swal.fire({
                title: 'Run Reconciliation?',
                text: `This will process reconciliation for Station ${stationId} for {{ $currentDate }}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Run It!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.processReconciliation(stationId);
                }
            });
        },

        processReconciliation(stationId) {
            this.loading = true;

            fetch('/continuous-meter/reconciliation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    station_id: stationId,
                    reconciliation_date: '{{ $currentDate }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reconciliation Complete',
                        text: 'Station reconciliation processed successfully.',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Reconciliation failed');
                }
            })
            .catch(error => {
                this.loading = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Reconciliation Failed',
                    text: error.message || 'An error occurred during reconciliation.',
                    confirmButtonColor: '#3b82f6'
                });
            });
        },

        refreshData() {
            this.loading = true;
            window.location.reload();
        }
    };
}
</script>
@endsection
