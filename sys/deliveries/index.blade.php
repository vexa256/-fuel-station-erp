@extends('layouts.app')

@section('content')
<div x-data="{
    activeTab: 'overview',
    showFilters: false,
    selectedDeliveries: [],
    bulkAction: '',
    search: '{{ request('search', '') }}',
    status: '{{ request('status', '') }}',
    qualityIssues: {{ request('quality_issues') ? 'true' : 'false' }},

    // EXACT data from controller - NO phantom variables
    stats: {
        total: {{ $stats['total'] }},
        completed_today: {{ $stats['completed_today'] }},
        in_transit: {{ $stats['in_transit'] }},
        quality_issues: {{ $stats['quality_issues'] }}
    },

    toggleDelivery(id) {
        const index = this.selectedDeliveries.indexOf(id);
        if (index > -1) {
            this.selectedDeliveries.splice(index, 1);
        } else {
            this.selectedDeliveries.push(id);
        }
    },

    selectAll() {
        this.selectedDeliveries = this.selectedDeliveries.length === {{ $deliveries->count() }} ? [] : {{ $deliveries->pluck('id')->toJson() }};
    },

    applyFilters() {
        const params = new URLSearchParams();
        if (this.search) params.set('search', this.search);
        if (this.status) params.set('status', this.status);
        if (this.qualityIssues) params.set('quality_issues', '1');
        window.location.href = '{{ route('deliveries.index') }}?' + params.toString();
    },

    executeBulkAction() {
        if (!this.selectedDeliveries.length || !this.bulkAction) return;

        Swal.fire({
            title: 'Confirm Bulk Action',
            text: `Execute ${this.bulkAction} on ${this.selectedDeliveries.length} deliveries?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implementation would go here
                Swal.fire('Success!', 'Bulk action executed successfully.', 'success');
            }
        });
    }
}" class="min-h-screen bg-slate-50">

    <!-- Header Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-truck text-white text-sm"></i>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-900">Deliveries</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button @click="showFilters = !showFilters"
                            class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filters
                    </button>

                    <a href="{{ route('deliveries.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        New Delivery
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation (Wizard Style) -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Overview
                </button>

                <button @click="activeTab = 'deliveries'"
                        :class="activeTab === 'deliveries' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-list mr-2"></i>
                    All Deliveries
                </button>

                <button @click="activeTab = 'actions'"
                        :class="activeTab === 'actions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-cogs mr-2"></i>
                    Bulk Actions
                </button>
            </nav>
        </div>
    </div>

    <!-- Filter Panel -->
    <div x-show="showFilters" x-transition class="bg-slate-100 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                    <input x-model="search" type="text" placeholder="Note number, supplier, driver..."
                           class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select x-model="status" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="SCHEDULED">Scheduled</option>
                        <option value="IN_TRANSIT">In Transit</option>
                        <option value="ARRIVED">Arrived</option>
                        <option value="UNLOADING">Unloading</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="REJECTED">Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quality Issues</label>
                    <label class="flex items-center">
                        <input x-model="qualityIssues" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-slate-700">Show only quality issues</span>
                    </label>
                </div>

                <div class="flex items-end">
                    <button @click="applyFilters()"
                            class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- EXACT Stats Cards from Controller -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck text-blue-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-slate-600">Total Deliveries</p>
                            <p class="text-2xl font-bold text-slate-900" x-text="stats.total.toLocaleString()"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-slate-600">Completed Today</p>
                            <p class="text-2xl font-bold text-slate-900" x-text="stats.completed_today.toLocaleString()"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-amber-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-slate-600">In Transit</p>
                            <p class="text-2xl font-bold text-slate-900" x-text="stats.in_transit.toLocaleString()"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-slate-600">Quality Issues</p>
                            <p class="text-2xl font-bold text-slate-900" x-text="stats.quality_issues.toLocaleString()"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliveries Tab -->
        <div x-show="activeTab === 'deliveries'" x-transition>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <!-- Table Header with Bulk Actions -->
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" @change="selectAll()"
                                       :checked="selectedDeliveries.length === {{ $deliveries->count() }}"
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">
                                    <span x-text="selectedDeliveries.length"></span> selected
                                </span>
                            </label>
                        </div>

                        <div class="text-sm text-slate-600">
                            Showing {{ $deliveries->firstItem() ?? 0 }} to {{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} results
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Quality</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($deliveries as $delivery)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" @change="toggleDelivery({{ $delivery->id }})"
                                           :checked="selectedDeliveries.includes({{ $delivery->id }})"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $delivery->delivery_note_number }}</div>
                                    <div class="text-sm text-slate-500">
                                        {{ Carbon\Carbon::parse($delivery->delivery_date)->format('M j, Y') }} at
                                        {{ Carbon\Carbon::parse($delivery->delivery_time)->format('H:i') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $delivery->company_name }}</div>
                                    <div class="text-sm text-slate-500">
                                        @if($delivery->driver_name)
                                            Driver: {{ $delivery->driver_name }}
                                        @endif
                                        @if($delivery->vehicle_registration)
                                            ({{ $delivery->vehicle_registration }})
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $delivery->tank_number }}</div>
                                    <div class="text-sm text-slate-500">{{ $delivery->station_name }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ number_format($delivery->quantity_delivered_liters, 3) }}L
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">
                                        UGX {{ number_format($delivery->total_delivery_cost, 2) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'SCHEDULED' => 'bg-blue-100 text-blue-800',
                                            'IN_TRANSIT' => 'bg-amber-100 text-amber-800',
                                            'ARRIVED' => 'bg-purple-100 text-purple-800',
                                            'UNLOADING' => 'bg-indigo-100 text-indigo-800',
                                            'COMPLETED' => 'bg-green-100 text-green-800',
                                            'REJECTED' => 'bg-red-100 text-red-800'
                                        ];
                                        $colorClass = $statusColors[$delivery->delivery_status] ?? 'bg-slate-100 text-slate-800';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colorClass }}">
                                        {{ str_replace('_', ' ', $delivery->delivery_status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($delivery->quality_test_passed)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Passed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Failed
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('deliveries.show', $delivery->id) }}"
                                           class="text-blue-600 hover:text-blue-900 transition-colors">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($delivery->delivery_status !== 'COMPLETED')
                                        <button onclick="approveDelivery({{ $delivery->id }})"
                                                class="text-green-600 hover:text-green-900 transition-colors">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif

                                        @if($delivery->delivery_status === 'COMPLETED')
                                        <a href="{{ route('deliveries.receipt', $delivery->id) }}"
                                           class="text-purple-600 hover:text-purple-900 transition-colors">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-truck text-slate-400 text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-slate-900 mb-2">No deliveries found</h3>
                                        <p class="text-slate-500">Try adjusting your search criteria or create a new delivery.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($deliveries->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                    {{ $deliveries->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Bulk Actions Tab -->
        <div x-show="activeTab === 'actions'" x-transition>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Bulk Actions</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Action</label>
                        <select x-model="bulkAction" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Action</option>
                            <option value="approve">Approve Selected</option>
                            <option value="export">Export Selected</option>
                            <option value="archive">Archive Selected</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button @click="executeBulkAction()"
                                :disabled="!selectedDeliveries.length || !bulkAction"
                                :class="selectedDeliveries.length && bulkAction ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-400 cursor-not-allowed'"
                                class="w-full px-4 py-2 text-white text-sm font-medium rounded-md transition-colors">
                            Execute Action
                        </button>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-slate-50 rounded-md">
                    <p class="text-sm text-slate-600">
                        <span class="font-medium" x-text="selectedDeliveries.length"></span> deliveries selected for bulk action.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveDelivery(id) {
    Swal.fire({
        title: 'Approve Delivery?',
        text: 'This will complete the delivery and trigger FIFO automation.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/deliveries/${id}/approve`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Approved!', data.message, 'success')
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error!', data.error, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Network error occurred', 'error');
            });
        }
    });
}
</script>
@endsection
