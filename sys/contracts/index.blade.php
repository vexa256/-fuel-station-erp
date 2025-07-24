@extends('layouts.app')

@section('title', 'Active Contracts')

@section('content')
<div class="min-h-screen bg-slate-50 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Active Contracts</h1>
                    <p class="text-sm text-slate-600 mt-1">Manage supplier contracts for fuel procurement</p>
                </div>
                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || auth()->user()->can_approve_purchases)
                    <a href="{{ route('contracts.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Contract
                    </a>
                @endif
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Active Contracts</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $stats['active_contracts'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Expiring Soon</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $stats['expiring_soon'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Wizard -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200">
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6">
                    <button class="tab-btn active py-4 px-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600" data-tab="search">
                        Search & Filter
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <form method="GET" action="{{ route('contracts.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Search Contracts</label>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Contract number or supplier name..."
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product Type</label>
                            <select name="product_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Products</option>
                                <option value="PETROL_95" {{ request('product_type') == 'PETROL_95' ? 'selected' : '' }}>Petrol 95</option>
                                <option value="PETROL_98" {{ request('product_type') == 'PETROL_98' ? 'selected' : '' }}>Petrol 98</option>
                                <option value="DIESEL" {{ request('product_type') == 'DIESEL' ? 'selected' : '' }}>Diesel</option>
                                <option value="KEROSENE" {{ request('product_type') == 'KEROSENE' ? 'selected' : '' }}>Kerosene</option>
                                <option value="JET_A1" {{ request('product_type') == 'JET_A1' ? 'selected' : '' }}>Jet A1</option>
                                <option value="HEAVY_FUEL_OIL" {{ request('product_type') == 'HEAVY_FUEL_OIL' ? 'selected' : '' }}>Heavy Fuel Oil</option>
                                <option value="LIGHT_FUEL_OIL" {{ request('product_type') == 'LIGHT_FUEL_OIL' ? 'selected' : '' }}>Light Fuel Oil</option>
                                <option value="LPG_AUTOGAS" {{ request('product_type') == 'LPG_AUTOGAS' ? 'selected' : '' }}>LPG Autogas</option>
                                <option value="ETHANOL_E10" {{ request('product_type') == 'ETHANOL_E10' ? 'selected' : '' }}>Ethanol E10</option>
                                <option value="ETHANOL_E85" {{ request('product_type') == 'ETHANOL_E85' ? 'selected' : '' }}>Ethanol E85</option>
                                <option value="BIODIESEL_B7" {{ request('product_type') == 'BIODIESEL_B7' ? 'selected' : '' }}>Biodiesel B7</option>
                                <option value="BIODIESEL_B20" {{ request('product_type') == 'BIODIESEL_B20' ? 'selected' : '' }}>Biodiesel B20</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contract</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price/Liter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Validity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $contract->contract_number }}</div>
                                            <div class="text-sm text-slate-500">{{ $contract->supplier_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $contract->company_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', $contract->product_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">UGX {{ number_format($contract->base_price_per_liter, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900">{{ \Carbon\Carbon::parse($contract->effective_from)->format('M d, Y') }}</div>
                                    <div class="text-sm text-slate-500">to {{ \Carbon\Carbon::parse($contract->effective_until)->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('contracts.show', $contract->id) }}"
                                       class="text-blue-600 hover:text-blue-900 transition-colors">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm">No active contracts found</p>
                                        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                            <a href="{{ route('contracts.create') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">Create your first contract</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
                    {{ $contracts->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Minimal tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(t => t.classList.remove('active', 'border-blue-600', 'text-blue-600'));
            this.classList.add('active', 'border-blue-600', 'text-blue-600');
        });
    });
});
</script>
@endsection
