@extends('layouts.app')
@section('title', 'Audit Trail Management')
@section('breadcrumb')
    <span class="text-slate-500 dark:text-slate-400">System</span>
    <i class="fas fa-chevron-right h-3 w-3 text-slate-400 mx-2"></i>
    <span class="font-medium text-slate-900 dark:text-slate-100">Audit Trail</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Audit Trail Management</h1>
            <p class="text-slate-600 dark:text-slate-400">Monitor system activity and maintain forensic audit trail</p>
        </div>
        <div class="flex items-center gap-3">
            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <i class="fas fa-shield-alt mr-1.5"></i>
                    Full Access Active
                </span>
            @endif
            <button type="button" onclick="verifyIntegrity()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-shield-check mr-2"></i>
                Verify Integrity
            </button>
            <button type="button" onclick="openExportModal()" class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-download mr-2"></i>
                Export
            </button>
        </div>
    </div>

    <!-- Integrity Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Records</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($integrityStatus['total_audit_records']) }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <i class="fas fa-database text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Integrity Status</p>
                    <p class="text-2xl font-bold {{ $integrityStatus['status'] === 'VERIFIED' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $integrityStatus['integrity_percentage'] }}%
                    </p>
                </div>
                <div class="p-3 {{ $integrityStatus['status'] === 'VERIFIED' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }} rounded-lg">
                    <i class="fas {{ $integrityStatus['status'] === 'VERIFIED' ? 'fa-shield-check text-emerald-600 dark:text-emerald-400' : 'fa-shield-alt text-amber-600 dark:text-amber-400' }}"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Verified Records</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($integrityStatus['hash_chain_verified_count']) }}</p>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Last Check</p>
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                        {{ $integrityStatus['last_integrity_check'] ? \Carbon\Carbon::parse($integrityStatus['last_integrity_check'])->diffForHumans() : 'Never' }}
                    </p>
                </div>
                <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
                    <i class="fas fa-clock text-slate-600 dark:text-slate-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Advanced Filters</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('audit.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ $currentFilters['start_date'] ?? '' }}"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ $currentFilters['end_date'] ?? '' }}"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">User</label>
                        <select name="user_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Users</option>
                            @foreach($filterOptions['users'] as $user)
                                <option value="{{ $user->id }}" {{ ($currentFilters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Action Type</label>
                        <select name="action_type" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Actions</option>
                            @foreach($filterOptions['action_types'] as $actionType)
                                <option value="{{ $actionType }}" {{ ($currentFilters['action_type'] ?? '') == $actionType ? 'selected' : '' }}>
                                    {{ $actionType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Table Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Table</label>
                        <select name="table_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Tables</option>
                            @foreach($filterOptions['table_names'] as $tableName)
                                <option value="{{ $tableName }}" {{ ($currentFilters['table_name'] ?? '') == $tableName ? 'selected' : '' }}>
                                    {{ $tableName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Station Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Station</label>
                        <select name="station_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Stations</option>
                            @foreach($filterOptions['stations'] as $station)
                                <option value="{{ $station->id }}" {{ ($currentFilters['station_id'] ?? '') == $station->id ? 'selected' : '' }}>
                                    {{ $station->station_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- IP Address Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">IP Address</label>
                        <input type="text" name="ip_address" value="{{ $currentFilters['ip_address'] ?? '' }}" placeholder="e.g., 192.168.1.1"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('audit.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                Audit Trail Records
                <span class="text-sm font-normal text-slate-600 dark:text-slate-400">({{ $auditLogs->total() }} total)</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Table</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Station</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                        @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Hash</th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900 dark:text-slate-100">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y') }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('g:i:s A') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center mr-3">
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                            {{ substr($log->first_name, 0, 1) }}{{ substr($log->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ $log->first_name }} {{ $log->last_name }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $log->role }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($log->action_type)
                                        @case('CREATE')
                                            bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300
                                            @break
                                        @case('UPDATE')
                                            bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                            @break
                                        @case('DELETE')
                                            bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                            @break
                                        @case('LOGIN')
                                            bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300
                                            @break
                                        @case('APPROVE')
                                            bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                            @break
                                        @default
                                            bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300
                                    @endswitch
                                ">
                                    {{ $log->action_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-mono">
                                {{ $log->table_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100">
                                {{ $log->station_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-mono">
                                {{ $log->ip_address }}
                            </td>
                            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="showHashDetails('{{ $log->id }}')" class="text-xs font-mono text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ substr($log->current_hash, 0, 8) }}...
                                </button>
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="showAuditDetails('{{ $log->id }}')" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                <button onclick="verifyRecord('{{ $log->id }}')" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                    <i class="fas fa-shield-check"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-slate-400 dark:text-slate-500">
                                    <i class="fas fa-search text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No audit records found</p>
                                    <p class="text-sm">Try adjusting your filters or check back later</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($auditLogs->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-slate-800 rounded-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Export Audit Trail</h3>
            <button onclick="closeExportModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="exportForm" method="POST" action="{{ route('audit.export') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Export Format</label>
                    <select name="export_format" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                        <option value="CSV">CSV</option>
                        <option value="EXCEL">Excel</option>
                        <option value="PDF">PDF</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Start Date</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">End Date</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <div class="flex items-center">
                    <input type="checkbox" name="include_hash_verification" id="includeHash" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="includeHash" class="ml-2 text-sm text-slate-700 dark:text-slate-300">Include hash verification data</label>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" onclick="closeExportModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Export
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
    document.getElementById('exportModal').classList.add('flex');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
    document.getElementById('exportModal').classList.remove('flex');
}

function verifyIntegrity() {
    Swal.fire({
        title: 'Verify Audit Trail Integrity',
        text: 'This will verify the complete hash chain integrity. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, verify now'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("audit.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    verification_type: 'FULL'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Verification Complete',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Verification Failed',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to verify integrity. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    });
}

function showAuditDetails(logId) {
    // Fetch and display audit log details in modal
    // Implementation would fetch detailed audit log data
    Swal.fire({
        title: 'Audit Log Details',
        text: 'Detailed view for audit log ID: ' + logId,
        icon: 'info',
        confirmButtonColor: '#3b82f6'
    });
}

function showHashDetails(logId) {
    // Show hash chain details for CEO/SYSTEM_ADMIN
    Swal.fire({
        title: 'Hash Chain Details',
        text: 'Hash chain verification for audit log ID: ' + logId,
        icon: 'info',
        confirmButtonColor: '#3b82f6'
    });
}

function verifyRecord(logId) {
    // Verify individual record hash
    fetch('{{ route("audit.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            verification_type: 'SINGLE',
            audit_log_id: logId
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            title: data.success ? 'Record Verified' : 'Verification Failed',
            text: data.message,
            icon: data.success ? 'success' : 'error',
            confirmButtonColor: data.success ? '#10b981' : '#ef4444'
        });
    });
}

// Auto-refresh for CEO/SYSTEM_ADMIN every 30 seconds
@if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
setInterval(() => {
    // Refresh page silently to show latest audit entries
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 30000);
@endif
</script>
@endsection
