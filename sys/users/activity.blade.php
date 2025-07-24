@extends('layouts.app')

@section('title', 'Activity Log - ' . $user->first_name . ' ' . $user->last_name)

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="bg-card border-b border-border">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('users.edit', $user->id) }}"
                        class="btn btn-ghost btn-circle hover:bg-accent/50 transition-colors">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <div class="flex items-center gap-4">
                        <!-- User Avatar -->
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                            <span class="text-lg font-bold text-primary">
                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-foreground">Activity Log</h1>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-sm text-muted-foreground">{{ $user->first_name }} {{ $user->last_name
                                    }}</span>
                                <span class="text-sm text-muted-foreground">•</span>
                                <span class="text-sm text-muted-foreground">{{ $user->employee_number }}</span>
                                <span
                                    class="badge {{ $user->role === 'CEO' ? 'badge-primary' : ($user->role === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }}">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                    <button type="button" class="btn btn-ghost flex items-center gap-2" onclick="exportActivityLog()">
                        <i class="fas fa-download text-sm"></i>
                        Export Log
                    </button>
                    @endif
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <i class="fas fa-shield-alt text-xs"></i>
                        <span>Forensic Audit Trail</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <form method="GET" action="{{ route('users.activity', $user->id) }}"
                class="space-y-4 lg:space-y-0 lg:flex lg:items-end lg:gap-4">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Action Type Filter -->
                    <div class="space-y-2">
                        <label for="action_type" class="text-sm font-medium text-foreground">Action Type</label>
                        <select name="action_type" id="action_type"
                            class="select select-bordered w-full focus:ring-2 focus:ring-primary/20">
                            <option value="">All Actions</option>
                            <option value="CREATE" {{ request('action_type')==='CREATE' ? 'selected' : '' }}>Create
                            </option>
                            <option value="READ" {{ request('action_type')==='READ' ? 'selected' : '' }}>Read</option>
                            <option value="UPDATE" {{ request('action_type')==='UPDATE' ? 'selected' : '' }}>Update
                            </option>
                            <option value="DELETE" {{ request('action_type')==='DELETE' ? 'selected' : '' }}>Delete
                            </option>
                            <option value="LOGIN" {{ request('action_type')==='LOGIN' ? 'selected' : '' }}>Login
                            </option>
                            <option value="LOGOUT" {{ request('action_type')==='LOGOUT' ? 'selected' : '' }}>Logout
                            </option>
                            <option value="APPROVE" {{ request('action_type')==='APPROVE' ? 'selected' : '' }}>Approve
                            </option>
                            <option value="REJECT" {{ request('action_type')==='REJECT' ? 'selected' : '' }}>Reject
                            </option>
                        </select>
                    </div>

                    <!-- Risk Level Filter -->
                    <div class="space-y-2">
                        <label for="risk_level" class="text-sm font-medium text-foreground">Risk Level</label>
                        <select name="risk_level" id="risk_level"
                            class="select select-bordered w-full focus:ring-2 focus:ring-primary/20">
                            <option value="">All Levels</option>
                            <option value="LOW" {{ request('risk_level')==='LOW' ? 'selected' : '' }}>Low</option>
                            <option value="MEDIUM" {{ request('risk_level')==='MEDIUM' ? 'selected' : '' }}>Medium
                            </option>
                            <option value="HIGH" {{ request('risk_level')==='HIGH' ? 'selected' : '' }}>High</option>
                            <option value="CRITICAL" {{ request('risk_level')==='CRITICAL' ? 'selected' : '' }}>Critical
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="space-y-2">
                        <label for="date_from" class="text-sm font-medium text-foreground">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="input input-bordered w-full focus:ring-2 focus:ring-primary/20">
                    </div>

                    <!-- Date To -->
                    <div class="space-y-2">
                        <label for="date_to" class="text-sm font-medium text-foreground">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="input input-bordered w-full focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <i class="fas fa-filter text-sm"></i>
                        Filter
                    </button>
                    @if(request()->anyFilled(['action_type', 'risk_level', 'date_from', 'date_to']))
                    <a href="{{ route('users.activity', $user->id) }}" class="btn btn-ghost flex items-center gap-2">
                        <i class="fas fa-times text-sm"></i>
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Activity Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-card rounded-lg border border-border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-list text-blue-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-foreground">{{ $activities->total() }}</div>
                        <div class="text-sm text-muted-foreground">Total Activities</div>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-lg border border-border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-foreground">{{ $activities->where('risk_level',
                            'LOW')->count() }}</div>
                        <div class="text-sm text-muted-foreground">Low Risk</div>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-lg border border-border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-foreground">{{ $activities->whereIn('risk_level', ['MEDIUM',
                            'HIGH'])->count() }}</div>
                        <div class="text-sm text-muted-foreground">Med/High Risk</div>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-lg border border-border p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-red-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-foreground">{{ $activities->where('risk_level',
                            'CRITICAL')->count() }}</div>
                        <div class="text-sm text-muted-foreground">Critical Risk</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-card rounded-lg border border-border overflow-hidden">
            @if($activities->count() > 0)
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground">Activity Timeline</h2>
                    <div class="text-sm text-muted-foreground">
                        Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{
                        $activities->total() }} activities
                    </div>
                </div>

                <!-- Timeline Container -->
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-border"></div>

                    <!-- Activity Items -->
                    <div class="space-y-6">
                        @foreach($activities as $activity)
                        <div class="relative flex items-start gap-4">
                            <!-- Timeline Dot -->
                            <div class="relative z-10 flex-shrink-0">
                                <div class="w-4 h-4 rounded-full border-2 border-background {{
                                    $activity->risk_level === 'CRITICAL' ? 'bg-red-500' : (
                                    $activity->risk_level === 'HIGH' ? 'bg-orange-500' : (
                                    $activity->risk_level === 'MEDIUM' ? 'bg-yellow-500' : 'bg-green-500'
                                )) }}"></div>
                            </div>

                            <!-- Activity Content -->
                            <div class="flex-1 min-w-0 bg-muted/30 rounded-lg p-4 hover:bg-muted/50 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <!-- Action Header -->
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="badge {{
                                                    $activity->action_type === 'CREATE' ? 'badge-default' : (
                                                    $activity->action_type === 'UPDATE' ? 'badge-secondary' : (
                                                    $activity->action_type === 'DELETE' ? 'badge-error' : (
                                                    in_array($activity->action_type, ['LOGIN', 'LOGOUT']) ? 'badge-primary' : 'badge-outline'
                                                ))) }} text-xs">
                                                    {{ $activity->action_type }}
                                                </span>
                                                <span class="text-sm font-medium text-foreground">{{
                                                    $activity->table_name }}</span>
                                                @if($activity->record_id)
                                                <span class="text-xs text-muted-foreground">#{{ $activity->record_id
                                                    }}</span>
                                                @endif
                                            </div>
                                            <span class="badge {{
                                                $activity->risk_level === 'CRITICAL' ? 'badge-error' : (
                                                $activity->risk_level === 'HIGH' ? 'badge-warning' : (
                                                $activity->risk_level === 'MEDIUM' ? 'badge-secondary' : 'badge-default'
                                            )) }} text-xs">
                                                {{ $activity->risk_level }}
                                            </span>
                                        </div>

                                        <!-- Action Description -->
                                        @if($activity->change_reason)
                                        <div class="text-sm text-foreground mb-2">
                                            {{ $activity->change_reason }}
                                        </div>
                                        @endif

                                        <!-- Technical Details -->
                                        <div
                                            class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs text-muted-foreground">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clock text-xs"></i>
                                                    <span>{{ \Carbon\Carbon::parse($activity->timestamp)->format('M j, Y
                                                        g:i:s A') }}</span>
                                                </div>
                                                @if($activity->ip_address)
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-globe text-xs"></i>
                                                    <span>{{ $activity->ip_address }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="space-y-1">
                                                @if($activity->session_id)
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-id-card text-xs"></i>
                                                    <span class="font-mono">{{ Str::limit($activity->session_id, 12)
                                                        }}</span>
                                                </div>
                                                @endif
                                                @if($activity->compliance_category)
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-balance-scale text-xs"></i>
                                                    <span>{{ $activity->compliance_category }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Expandable Details -->
                                        @if($activity->old_value_text || $activity->new_value_text ||
                                        $activity->user_agent)
                                        <div class="mt-3">
                                            <button type="button"
                                                class="text-xs text-primary hover:text-primary/80 transition-colors flex items-center gap-1"
                                                onclick="toggleDetails('activity-{{ $activity->id }}')">
                                                <i class="fas fa-chevron-right transition-transform"
                                                    id="chevron-{{ $activity->id }}"></i>
                                                Show Technical Details
                                            </button>

                                            <div class="hidden mt-3 p-3 bg-background rounded-lg border border-border"
                                                id="details-activity-{{ $activity->id }}">
                                                @if($activity->old_value_text && $activity->new_value_text)
                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-3">
                                                    <div>
                                                        <div class="text-xs font-medium text-muted-foreground mb-1">
                                                            Before:</div>
                                                        <div
                                                            class="text-xs text-foreground bg-red-50 border border-red-200 rounded p-2 font-mono">
                                                            {{ Str::limit($activity->old_value_text, 100) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-medium text-muted-foreground mb-1">
                                                            After:</div>
                                                        <div
                                                            class="text-xs text-foreground bg-green-50 border border-green-200 rounded p-2 font-mono">
                                                            {{ Str::limit($activity->new_value_text, 100) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @elseif($activity->new_value_text)
                                                <div class="mb-3">
                                                    <div class="text-xs font-medium text-muted-foreground mb-1">Data:
                                                    </div>
                                                    <div
                                                        class="text-xs text-foreground bg-muted/50 border border-border rounded p-2 font-mono">
                                                        {{ Str::limit($activity->new_value_text, 200) }}
                                                    </div>
                                                </div>
                                                @endif

                                                @if($activity->user_agent)
                                                <div>
                                                    <div class="text-xs font-medium text-muted-foreground mb-1">User
                                                        Agent:</div>
                                                    <div class="text-xs text-muted-foreground font-mono">
                                                        {{ $activity->user_agent }}
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Time Ago -->
                                    <div class="text-xs text-muted-foreground whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($activity->timestamp)->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($activities->hasPages())
            <div class="border-t border-border bg-muted/30 px-6 py-4">
                {{ $activities->appends(request()->query())->links() }}
            </div>
            @endif

            @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-muted-foreground mb-4">
                    <i class="fas fa-history text-4xl"></i>
                </div>
                <h3 class="text-sm font-medium text-foreground mb-2">No activity found</h3>
                <p class="text-sm text-muted-foreground mb-6">
                    @if(request()->anyFilled(['action_type', 'risk_level', 'date_from', 'date_to']))
                    Try adjusting your filter criteria to see more activities.
                    @else
                    This user hasn't performed any recorded activities yet.
                    @endif
                </p>
                @if(request()->anyFilled(['action_type', 'risk_level', 'date_from', 'date_to']))
                <a href="{{ route('users.activity', $user->id) }}" class="btn btn-outline">
                    Clear Filters
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleDetails(activityId) {
    const details = document.getElementById(`details-${activityId}`);
    const chevron = document.getElementById(`chevron-${activityId.replace('activity-', '')}`);

    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        chevron.style.transform = 'rotate(90deg)';
    } else {
        details.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

function exportActivityLog() {
    Swal.fire({
        title: 'Export Activity Log',
        text: 'This will generate a comprehensive audit report for this user.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--primary))',
        cancelButtonColor: 'hsl(var(--muted))',
        confirmButtonText: 'Export Report',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would make an AJAX request to generate export
            Swal.fire({
                title: 'Export Started',
                text: 'Your audit report is being generated and will be downloaded shortly.',
                icon: 'success',
                confirmButtonColor: 'hsl(var(--primary))'
            });
        }
    });
}

// Date range validation
document.getElementById('date_from').addEventListener('change', function() {
    const dateTo = document.getElementById('date_to');
    if (this.value && dateTo.value && this.value > dateTo.value) {
        dateTo.value = this.value;
    }
});

document.getElementById('date_to').addEventListener('change', function() {
    const dateFrom = document.getElementById('date_from');
    if (this.value && dateFrom.value && this.value < dateFrom.value) {
        dateFrom.value = this.value;
    }
});

// Auto-refresh for real-time updates (optional)
let autoRefresh = false;
function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        setInterval(() => {
            if (!document.hidden) {
                window.location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    }
}
</script>
@endpush
@endsection
