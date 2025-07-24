@extends('layouts.app')

@section('title', 'Station Assignments')

@section('breadcrumb')
<a href="{{ route('users.index') }}" class="text-muted-foreground hover:text-primary transition-colors">Users</a>
<i class="fas fa-chevron-right h-3 w-3 text-muted-foreground"></i>
<a href="{{ route('users.edit', $user->id) }}" class="text-muted-foreground hover:text-primary transition-colors">{{
    $user->first_name }} {{ $user->last_name }}</a>
<i class="fas fa-chevron-right h-3 w-3 text-muted-foreground"></i>
<span class="text-primary font-medium">Station Assignments</span>
@endsection

@php
$isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);
$currentAssignments = collect($assignedStations);
$availableStations = $stations->whereNotIn('id', $currentAssignments->pluck('station_id'));
@endphp

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                <i class="fas fa-map-marked-alt text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-foreground">Station Assignments</h1>
                <p class="text-sm text-muted-foreground">
                    Manage station access for {{ $user->first_name }} {{ $user->last_name }}
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground ml-2">
                        {{ $user->role }}
                    </span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline gap-2">
                <i class="fas fa-arrow-left h-4 w-4"></i>
                Back to Profile
            </a>
            @if($isAutoApproved)
            <span
                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                <i class="fas fa-check-circle h-3 w-3 mr-1.5"></i>
                Auto-Approved Access
            </span>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-card rounded-xl border border-border p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-gas-pump text-blue-600 h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Active Assignments</p>
                    <p class="text-2xl font-bold text-foreground">{{ $currentAssignments->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <i class="fas fa-crown text-purple-600 h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Primary Stations</p>
                    <p class="text-2xl font-bold text-foreground">{{ $currentAssignments->where('assignment_type',
                        'PRIMARY')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-shield-check text-green-600 h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Full Access</p>
                    <p class="text-2xl font-bold text-foreground">{{
                        $currentAssignments->where('can_approve_deliveries', 1)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-orange-50 flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Days Active</p>
                    @php
                    $oldestAssignment = $currentAssignments->min('assignment_start_date');
                    $daysActive = $oldestAssignment ? \Carbon\Carbon::parse($oldestAssignment)->diffInDays(now()) : 0;
                    @endphp
                    <p class="text-2xl font-bold text-foreground">{{ $daysActive }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Assignments -->
    @if($currentAssignments->count() > 0)
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="px-6 py-4 border-b border-border">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-foreground">Current Station Assignments</h2>
                <span class="text-sm text-muted-foreground">{{ $currentAssignments->count() }} active</span>
            </div>
        </div>

        <div class="divide-y divide-border">
            @foreach($currentAssignments as $assignment)
            @php
            $station = $stations->where('id', $assignment->station_id)->first();
            $isPrimary = $assignment->assignment_type === 'PRIMARY';
            @endphp
            <div class="p-6 hover:bg-accent/50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-lg {{ $isPrimary ? 'bg-gradient-to-br from-purple-500 to-purple-600' : 'bg-gradient-to-br from-blue-500 to-blue-600' }} flex items-center justify-center text-white shadow-lg">
                            <i class="fas {{ $isPrimary ? 'fa-crown' : 'fa-gas-pump' }} text-lg"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-foreground">{{ $station->station_name }}</h3>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $isPrimary ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $assignment->assignment_type }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground">{{ $station->station_code }} • {{ $station->region
                                }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                Assigned {{ \Carbon\Carbon::parse($assignment->assignment_start_date)->format('M j, Y')
                                }} by
                                <span class="font-medium">{{ $assignment->assigned_by_name ?? 'System' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Permission Indicators -->
                        <div class="grid grid-cols-3 gap-2">
                            @if($assignment->can_enter_readings)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 border border-green-200"
                                title="Can Enter Readings">
                                <i class="fas fa-keyboard text-green-600 text-sm"></i>
                            </div>
                            @endif
                            @if($assignment->can_approve_deliveries)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 border border-blue-200"
                                title="Can Approve Deliveries">
                                <i class="fas fa-truck text-blue-600 text-sm"></i>
                            </div>
                            @endif
                            @if($assignment->can_handle_cash)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-50 border border-yellow-200"
                                title="Can Handle Cash">
                                <i class="fas fa-coins text-yellow-600 text-sm"></i>
                            </div>
                            @endif
                            @if($assignment->can_modify_prices)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 border border-purple-200"
                                title="Can Modify Prices">
                                <i class="fas fa-tag text-purple-600 text-sm"></i>
                            </div>
                            @endif
                            @if($assignment->can_approve_variances)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 border border-red-200"
                                title="Can Approve Variances">
                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                            </div>
                            @endif
                            @if($assignment->can_open_station || $assignment->can_close_station)
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-200"
                                title="Can Open/Close Station">
                                <i class="fas fa-power-off text-indigo-600 text-sm"></i>
                            </div>
                            @endif
                        </div>

                        @if($isAutoApproved)
                        <button onclick="removeAssignment({{ $station->id }}, '{{ $station->station_name }}')"
                            class="btn btn-ghost btn-sm text-destructive hover:text-destructive hover:bg-destructive/10">
                            <i class="fas fa-times h-4 w-4"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Add New Assignment -->
    @if($isAutoApproved && $availableStations->count() > 0)
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="px-6 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Add Station Assignment</h2>
            <p class="text-sm text-muted-foreground mt-1">Select stations to assign to this user</p>
        </div>

        <form action="{{ route('users.assignStations', $user->id) }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availableStations as $station)
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="station_ids[]" value="{{ $station->id }}" class="peer sr-only" />
                        <div
                            class="border-2 border-border rounded-xl p-4 transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50 hover:bg-accent/50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-lg bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center text-white shadow-sm">
                                    <i class="fas fa-gas-pump text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-foreground truncate">{{ $station->station_name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ $station->station_code }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $station->region }} • {{
                                        $station->district }}</p>
                                </div>
                                <div class="peer-checked:block hidden">
                                    <i class="fas fa-check-circle text-primary h-5 w-5"></i>
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <p class="text-sm text-muted-foreground">
                        <i class="fas fa-info-circle h-4 w-4 mr-1"></i>
                        First selected station will be set as PRIMARY
                    </p>
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="fas fa-plus h-4 w-4"></i>
                        Assign Selected Stations
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif

    <!-- No Available Stations -->
    @if($availableStations->count() === 0 && $currentAssignments->count() === 0)
    <div class="bg-card rounded-xl border border-border shadow-sm p-8 text-center">
        <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-gas-pump text-2xl text-muted-foreground"></i>
        </div>
        <h3 class="text-lg font-semibold text-foreground mb-2">No Stations Available</h3>
        <p class="text-muted-foreground mb-4">There are currently no stations available for assignment.</p>
        <a href="{{ route('stations.create') }}" class="btn btn-primary gap-2">
            <i class="fas fa-plus h-4 w-4"></i>
            Create New Station
        </a>
    </div>
    @endif

    @if($availableStations->count() === 0 && $currentAssignments->count() > 0)
    <div class="bg-card rounded-xl border border-border shadow-sm p-6 text-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-foreground mb-2">All Stations Assigned</h3>
        <p class="text-muted-foreground">This user has been assigned to all available stations.</p>
    </div>
    @endif

    <!-- Permission Matrix Reference -->
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="px-6 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Permission Matrix Reference</h2>
            <p class="text-sm text-muted-foreground mt-1">Understanding role-based station permissions</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-green-50 border border-green-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-keyboard text-green-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Enter Readings</p>
                    <p class="text-xs text-muted-foreground">Manager, Supervisor</p>
                </div>

                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-truck text-blue-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Approve Deliveries</p>
                    <p class="text-xs text-muted-foreground">Manager Only</p>
                </div>

                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-yellow-50 border border-yellow-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-coins text-yellow-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Handle Cash</p>
                    <p class="text-xs text-muted-foreground">Manager Only</p>
                </div>

                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-tag text-purple-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Modify Prices</p>
                    <p class="text-xs text-muted-foreground">CEO, Admin, Manager</p>
                </div>

                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-red-50 border border-red-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Approve Variances</p>
                    <p class="text-xs text-muted-foreground">CEO, Admin, Manager</p>
                </div>

                <div class="text-center">
                    <div
                        class="h-10 w-10 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-power-off text-indigo-600"></i>
                    </div>
                    <p class="text-xs font-medium text-foreground">Open/Close</p>
                    <p class="text-xs text-muted-foreground">Manager Only</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function removeAssignment(stationId, stationName) {
    Swal.fire({
        title: 'Remove Station Assignment?',
        text: `Remove access to ${stationName} for this user?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Remove Access',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("users.assignStations", $user->id) }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Get currently assigned stations except the one being removed
            const currentStations = @json($currentAssignments->pluck('station_id')->toArray());
            const remainingStations = currentStations.filter(id => id != stationId);

            // Add remaining stations as hidden inputs
            remainingStations.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'station_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // If no stations remain, add a dummy input to prevent validation error
            if (remainingStations.length === 0) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_all';
                input.value = '1';
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="assignStations"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checkboxes = form.querySelectorAll('input[name="station_ids[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'No Stations Selected',
                    text: 'Please select at least one station to assign.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
});
</script>
@endsection
