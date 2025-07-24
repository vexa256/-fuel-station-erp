@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="bg-card border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">User Management</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage user accounts, roles, and permissions across the system
                    </p>
                </div>

                @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <div class="flex items-center gap-3">
                    <a href="{{ route('users.create') }}"
                        class="btn btn-primary flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                        <i class="fas fa-plus text-sm"></i>
                        Add New User
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters & Search Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <form method="GET" action="{{ route('users.index') }}"
                class="space-y-4 lg:space-y-0 lg:flex lg:items-end lg:gap-4">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="space-y-2">
                        <label for="search" class="text-sm font-medium text-foreground">Search Users</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Name, email, or employee number..."
                            class="input input-bordered w-full focus:ring-2 focus:ring-primary/20">
                    </div>

                    <!-- Role Filter -->
                    <div class="space-y-2">
                        <label for="role" class="text-sm font-medium text-foreground">Filter by Role</label>
                        <select name="role" id="role"
                            class="select select-bordered w-full focus:ring-2 focus:ring-primary/20">
                            <option value="">All Roles</option>
                            <option value="SYSTEM_ADMIN" {{ request('role')==='SYSTEM_ADMIN' ? 'selected' : '' }}>System
                                Admin</option>
                            <option value="CEO" {{ request('role')==='CEO' ? 'selected' : '' }}>CEO</option>
                            <option value="STATION_MANAGER" {{ request('role')==='STATION_MANAGER' ? 'selected' : '' }}>
                                Station Manager</option>
                            <option value="DELIVERY_SUPERVISOR" {{ request('role')==='DELIVERY_SUPERVISOR' ? 'selected'
                                : '' }}>Delivery Supervisor</option>
                            <option value="AUDITOR" {{ request('role')==='AUDITOR' ? 'selected' : '' }}>Auditor</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="space-y-2">
                        <label for="status" class="text-sm font-medium text-foreground">Filter by Status</label>
                        <select name="status" id="status"
                            class="select select-bordered w-full focus:ring-2 focus:ring-primary/20">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <i class="fas fa-search text-sm"></i>
                        Search
                    </button>
                    @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="btn btn-ghost flex items-center gap-2">
                        <i class="fas fa-times text-sm"></i>
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="flex items-center justify-between mb-6">
            <div class="text-sm text-muted-foreground">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
            </div>
            <div class="text-sm text-muted-foreground">
                {{ $users->links('pagination::simple-default') }}
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-card rounded-lg border border-border overflow-hidden">
            @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="text-left font-semibold text-foreground py-4 px-6">User Details</th>
                            <th class="text-left font-semibold text-foreground py-4 px-6">Role & Status</th>
                            <th class="text-left font-semibold text-foreground py-4 px-6">Station Assignments</th>
                            <th class="text-left font-semibold text-foreground py-4 px-6">Last Activity</th>
                            <th class="text-right font-semibold text-foreground py-4 px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($users as $user)
                        <tr class="hover:bg-muted/30 transition-colors">
                            <!-- User Details -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-primary">
                                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name,
                                                0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-foreground truncate">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="text-sm text-muted-foreground truncate">
                                            {{ $user->email }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            ID: {{ $user->employee_number }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role & Status -->
                            <td class="py-4 px-6">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="badge {{ $user->role === 'CEO' ? 'badge-primary' : ($user->role === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }}">
                                            {{ str_replace('_', ' ', $user->role) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($user->is_active)
                                        <span
                                            class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full">
                                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                            Active
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-100 px-2 py-1 rounded-full">
                                            <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                                            Inactive
                                        </span>
                                        @endif

                                        @if($user->account_locked_until && $user->account_locked_until > now())
                                        <span class="text-xs text-orange-700 bg-orange-100 px-2 py-1 rounded-full">
                                            <i class="fas fa-lock text-xs mr-1"></i>
                                            Locked
                                        </span>
                                        @endif

                                        @if($user->failed_login_attempts > 0)
                                        <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded-full">
                                            {{ $user->failed_login_attempts }} Failed
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Station Assignments -->
                            <td class="py-4 px-6">
                                @if($user->station_count > 0)
                                <div class="space-y-1">
                                    <div class="text-sm text-foreground">
                                        <span
                                            class="inline-flex items-center gap-1 text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                            <i class="fas fa-gas-pump text-xs"></i>
                                            {{ $user->station_count }} Station{{ $user->station_count > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    @if($user->assigned_stations)
                                    <div class="text-xs text-muted-foreground truncate max-w-48"
                                        title="{{ $user->assigned_stations }}">
                                        {{ Str::limit($user->assigned_stations, 40) }}
                                    </div>
                                    @endif
                                </div>
                                @else
                                <span class="text-sm text-muted-foreground">No assignments</span>
                                @endif
                            </td>

                            <!-- Last Activity -->
                            <td class="py-4 px-6">
                                @if($user->last_login_at)
                                <div class="text-sm text-foreground">
                                    {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ \Carbon\Carbon::parse($user->last_login_at)->format('M j, Y g:i A') }}
                                </div>
                                @else
                                <span class="text-sm text-muted-foreground">Never logged in</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View Activity (Self or Admin) -->
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || auth()->id() ==
                                    $user->id)
                                    <a href="{{ route('users.activity', $user->id) }}"
                                        class="btn btn-ghost btn-sm hover:bg-accent/50 transition-colors"
                                        title="View Activity">
                                        <i class="fas fa-history text-sm"></i>
                                    </a>
                                    @endif

                                    <!-- Edit User (Self or Admin) -->
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) || auth()->id() ==
                                    $user->id)
                                    <a href="{{ route('users.edit', $user->id) }}"
                                        class="btn btn-ghost btn-sm hover:bg-accent/50 transition-colors"
                                        title="Edit User">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    @endif

                                    <!-- Station Assignment (Admin Only) -->
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                    <a href="{{ route('users.stations', $user->id) }}"
                                        class="btn btn-ghost btn-sm hover:bg-accent/50 transition-colors"
                                        title="Manage Stations">
                                        <i class="fas fa-gas-pump text-sm"></i>
                                    </a>
                                    @endif

                                    <!-- Permissions (Admin Only) -->
                                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                    <a href="{{ route('users.permissions', $user->id) }}"
                                        class="btn btn-ghost btn-sm hover:bg-accent/50 transition-colors"
                                        title="Manage Permissions">
                                        <i class="fas fa-key text-sm"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="border-t border-border bg-muted/30 px-6 py-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
            @endif

            @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-muted-foreground mb-4">
                    <i class="fas fa-users text-4xl"></i>
                </div>
                <h3 class="text-sm font-medium text-foreground mb-2">No users found</h3>
                <p class="text-sm text-muted-foreground mb-6">
                    @if(request()->anyFilled(['search', 'role', 'status']))
                    Try adjusting your search criteria or filters.
                    @else
                    Get started by creating your first user account.
                    @endif
                </p>
                @if(request()->anyFilled(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="btn btn-outline">
                    Clear Filters
                </a>
                @elseif(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    Add First User
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="fixed top-4 right-4 z-50 max-w-sm w-full bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-lg animate-in"
    role="alert">
    <div class="flex items-center gap-2">
        <i class="fas fa-check-circle"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 z-50 max-w-sm w-full bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-lg animate-in"
    role="alert">
    <div class="flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages
    const alerts = document.querySelectorAll('[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Search debouncing
    let searchTimeout;
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    // Auto-submit could be implemented here
                }
            }, 500);
        });
    }
});
</script>
@endpush
@endsection
