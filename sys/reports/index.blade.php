@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="reportsHub()" x-init="init()">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Reports Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive reporting and analytics hub</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                            <span class="text-sm font-medium text-green-800">{{ $currentDate ?? date('Y-m-d') }}</span>
                        </div>
                    </div>
                    @if($isAutoApproved ?? false)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                        <span class="text-xs font-medium text-blue-800">{{ $currentUserRole ?? 'USER' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Metrics Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Today's Reconciliations</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $dashboardMetrics['today_reconciliations'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Variances</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $dashboardMetrics['pending_variances'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-50 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.618 6.618l1.414-1.414L9 8.172l3.968-3.968 1.414 1.414L11.414 8.6l2.968 2.968-1.414 1.414L10 9.414l-2.968 2.968-1.414-1.414L8.6 8.414 4.618 6.618z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Critical Alerts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $dashboardMetrics['critical_alerts'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Stations</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $dashboardMetrics['total_stations'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">System Accuracy</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($dashboardMetrics['system_accuracy'] ?? 99.5, 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Categories Navigation (ShadCN Wizard/Tab Style) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" role="tablist">
                    <button @click="setActiveTab('operational')"
                            :class="activeTab === 'operational' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Operational Reports
                    </button>
                    <button @click="setActiveTab('financial')"
                            :class="activeTab === 'financial' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Financial Reports
                    </button>
                    <button @click="setActiveTab('compliance')"
                            :class="activeTab === 'compliance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Compliance & Audit
                    </button>
                    <button @click="setActiveTab('executive')"
                            :class="activeTab === 'executive' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Executive Summary
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Operational Reports Tab -->
                <div x-show="activeTab === 'operational'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a href="{{ route('reports.daily-reconciliation') }}"
                           class="group block p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 group-hover:text-blue-700">Daily Reconciliation</h3>
                                    <p class="mt-1 text-sm text-blue-600">Tank-level daily reconciliation reports</p>
                                </div>
                                <svg class="w-8 h-8 text-blue-500 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.variance-analysis') }}"
                           class="group block p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border border-yellow-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-yellow-900 group-hover:text-yellow-700">Variance Analysis</h3>
                                    <p class="mt-1 text-sm text-yellow-600">Detailed variance investigation reports</p>
                                </div>
                                <svg class="w-8 h-8 text-yellow-500 group-hover:text-yellow-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.inventory-valuation') }}"
                           class="group block p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-green-900 group-hover:text-green-700">Inventory Valuation</h3>
                                    <p class="mt-1 text-sm text-green-600">FIFO-based inventory valuations</p>
                                </div>
                                <svg class="w-8 h-8 text-green-500 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.sales-meter-reconciliation') }}"
                           class="group block p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-purple-900 group-hover:text-purple-700">Sales Reconciliation</h3>
                                    <p class="mt-1 text-sm text-purple-600">Meter vs physical sales analysis</p>
                                </div>
                                <svg class="w-8 h-8 text-purple-500 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.delivery-tracking') }}"
                           class="group block p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-indigo-900 group-hover:text-indigo-700">Delivery Tracking</h3>
                                    <p class="mt-1 text-sm text-indigo-600">Delivery status and logistics reports</p>
                                </div>
                                <svg class="w-8 h-8 text-indigo-500 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.operational-efficiency') }}"
                           class="group block p-6 bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg border border-teal-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-teal-900 group-hover:text-teal-700">Operational Efficiency</h3>
                                    <p class="mt-1 text-sm text-teal-600">Performance metrics and KPIs</p>
                                </div>
                                <svg class="w-8 h-8 text-teal-500 group-hover:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Financial Reports Tab -->
                <div x-show="activeTab === 'financial'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('reports.financial-performance') }}"
                           class="group block p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-emerald-900 group-hover:text-emerald-700">Financial Performance</h3>
                                    <p class="mt-1 text-sm text-emerald-600">Revenue, profit margins, and cost analysis</p>
                                </div>
                                <svg class="w-8 h-8 text-emerald-500 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Compliance & Audit Tab -->
                <div x-show="activeTab === 'compliance'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('reports.compliance-audit') }}"
                           class="group block p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-red-900 group-hover:text-red-700">Compliance Audit</h3>
                                    <p class="mt-1 text-sm text-red-600">Regulatory compliance and audit trails</p>
                                </div>
                                <svg class="w-8 h-8 text-red-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('reports.exceptions-alerts') }}"
                           class="group block p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg border border-orange-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-orange-900 group-hover:text-orange-700">Exceptions & Alerts</h3>
                                    <p class="mt-1 text-sm text-orange-600">System alerts and exception reports</p>
                                </div>
                                <svg class="w-8 h-8 text-orange-500 group-hover:text-orange-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Executive Summary Tab -->
                <div x-show="activeTab === 'executive'" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <a href="{{ route('reports.executive-summary') }}"
                           class="group block p-8 bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg border border-slate-200 hover:shadow-lg transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-900 group-hover:text-slate-700">Executive Summary</h3>
                                    <p class="mt-2 text-slate-600">Comprehensive executive dashboard with key performance indicators</p>
                                    <div class="mt-4 flex items-center text-sm text-slate-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        High-level insights for strategic decision making
                                    </div>
                                </div>
                                <svg class="w-12 h-12 text-slate-400 group-hover:text-slate-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reportsHub() {
    return {
        activeTab: 'operational',

        init() {
            this.validateControllerData();
        },

        setActiveTab(tab) {
            this.activeTab = tab;
        },

        validateControllerData() {
            // Validate that all expected controller data exists
            const requiredMetrics = ['today_reconciliations', 'pending_variances', 'critical_alerts', 'total_stations', 'system_accuracy'];
            const dashboardMetrics = @json($dashboardMetrics ?? []);

            requiredMetrics.forEach(metric => {
                if (!(metric in dashboardMetrics)) {
                    console.warn(`Missing dashboard metric: ${metric}`);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Loading Issue',
                        text: `Some dashboard metrics may not be available.`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

            // Validate user permissions
            const userRole = @json($currentUserRole ?? '');
            const isAutoApproved = @json($isAutoApproved ?? false);

            if (!userRole) {
                Swal.fire({
                    icon: 'error',
                    title: 'Authentication Error',
                    text: 'User role could not be determined. Please refresh the page.',
                    confirmButtonColor: '#3b82f6'
                });
            }
        }
    }
}
</script>
@endsection
