@extends('layouts.app')

@section('title', 'Edit User - ' . $user->first_name . ' ' . $user->last_name)

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="bg-card border-b border-border">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('users.index') }}"
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
                            <h1 class="text-2xl font-bold text-foreground">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </h1>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-sm text-muted-foreground">{{ $user->employee_number }}</span>
                                <span
                                    class="badge {{ $user->role === 'CEO' ? 'badge-primary' : ($user->role === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }}">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                    <a href="{{ route('users.stations', $user->id) }}" class="btn btn-ghost flex items-center gap-2">
                        <i class="fas fa-gas-pump text-sm"></i>
                        Manage Stations
                    </a>
                    <a href="{{ route('users.permissions', $user->id) }}" class="btn btn-ghost flex items-center gap-2">
                        <i class="fas fa-key text-sm"></i>
                        Permissions
                    </a>
                    @endif
                    <a href="{{ route('users.activity', $user->id) }}" class="btn btn-ghost flex items-center gap-2">
                        <i class="fas fa-history text-sm"></i>
                        Activity Log
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('users.update', $user->id) }}" id="editUserForm" novalidate>
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Personal Information -->
                    <div class="bg-card rounded-lg border border-border p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-primary-foreground text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-foreground">Personal Information</h2>
                                <p class="text-sm text-muted-foreground">Update user's basic details</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div class="space-y-2">
                                <label for="first_name"
                                    class="text-sm font-medium text-foreground flex items-center gap-2">
                                    First Name
                                    <span class="text-destructive">*</span>
                                </label>
                                <input type="text" name="first_name" id="first_name"
                                    value="{{ old('first_name', $user->first_name) }}"
                                    class="input input-bordered w-full @error('first_name') border-destructive focus:ring-destructive @enderror"
                                    placeholder="Enter first name" required>
                                @error('first_name')
                                <p class="text-sm text-destructive flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="space-y-2">
                                <label for="last_name"
                                    class="text-sm font-medium text-foreground flex items-center gap-2">
                                    Last Name
                                    <span class="text-destructive">*</span>
                                </label>
                                <input type="text" name="last_name" id="last_name"
                                    value="{{ old('last_name', $user->last_name) }}"
                                    class="input input-bordered w-full @error('last_name') border-destructive focus:ring-destructive @enderror"
                                    placeholder="Enter last name" required>
                                @error('last_name')
                                <p class="text-sm text-destructive flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-medium text-foreground flex items-center gap-2">
                                    Email Address
                                    <span class="text-destructive">*</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="input input-bordered w-full @error('email') border-destructive focus:ring-destructive @enderror"
                                    placeholder="user@company.com" required>
                                @error('email')
                                <p class="text-sm text-destructive flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div class="space-y-2">
                                <label for="department" class="text-sm font-medium text-foreground">Department</label>
                                <input type="text" name="department" id="department"
                                    value="{{ old('department', $user->department) }}"
                                    class="input input-bordered w-full" placeholder="Auto-assigned based on role">
                            </div>

                            <!-- Read-only Fields -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Employee Number</label>
                                <div class="input input-bordered bg-muted/50 text-muted-foreground">
                                    {{ $user->employee_number }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Username</label>
                                <div class="input input-bordered bg-muted/50 text-muted-foreground">
                                    {{ $user->username }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Security -->
                    <div class="bg-card rounded-lg border border-border p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-primary-foreground text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-foreground">Role & Security</h2>
                                <p class="text-sm text-muted-foreground">Configure user access and permissions</p>
                            </div>
                            @if(!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                            <div class="ml-auto">
                                <span class="badge badge-outline text-xs">Limited Access</span>
                            </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Role Selection -->
                            <div class="space-y-4">
                                <label for="role" class="text-sm font-medium text-foreground flex items-center gap-2">
                                    User Role
                                    <span class="text-destructive">*</span>
                                    @if(!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                                    <i class="fas fa-lock text-xs text-muted-foreground ml-1"
                                        title="Role changes require admin privileges"></i>
                                    @endif
                                </label>

                                <div class="space-y-3">
                                    @foreach(['CEO' => 'Chief Executive Officer', 'SYSTEM_ADMIN' => 'System
                                    Administrator', 'STATION_MANAGER' => 'Station Manager', 'DELIVERY_SUPERVISOR' =>
                                    'Delivery Supervisor', 'AUDITOR' => 'Auditor'] as $roleValue => $roleLabel)
                                    <label
                                        class="flex items-start gap-3 p-4 border border-border rounded-lg hover:bg-accent/30 transition-colors cursor-pointer role-option @if(old('role', $user->role) === $roleValue) border-primary bg-primary/5 @endif">
                                        <input type="radio" name="role" value="{{ $roleValue }}"
                                            class="radio radio-primary mt-1" @if(old('role', $user->role) ===
                                        $roleValue) checked @endif
                                        @if(!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) disabled @endif
                                        onchange="updateRolePermissions(this.value)">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-foreground">{{ $roleLabel }}</div>
                                            <div class="text-xs text-muted-foreground mt-1">
                                                @switch($roleValue)
                                                @case('CEO')
                                                Full system access with absolute permissions across all operations
                                                @break
                                                @case('SYSTEM_ADMIN')
                                                Technical administration with complete system control
                                                @break
                                                @case('STATION_MANAGER')
                                                Manage assigned station operations and daily activities
                                                @break
                                                @case('DELIVERY_SUPERVISOR')
                                                Oversee fuel deliveries and logistics coordination
                                                @break
                                                @case('AUDITOR')
                                                Read-only access for financial and operational auditing
                                                @break
                                                @endswitch
                                            </div>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span
                                                    class="badge {{ $roleValue === 'CEO' ? 'badge-primary' : ($roleValue === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }} text-xs">
                                                    {{ str_replace('_', ' ', $roleValue) }}
                                                </span>
                                                @if($roleValue === $user->role)
                                                <span class="badge badge-default text-xs">Current</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('role')
                                <p class="text-sm text-destructive flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Security & Permissions -->
                            <div class="space-y-6">
                                <!-- Security Clearance -->
                                <div class="space-y-3">
                                    <label for="security_clearance_level"
                                        class="text-sm font-medium text-foreground flex items-center gap-2">
                                        Security Clearance
                                        <span class="text-destructive">*</span>
                                    </label>
                                    <select name="security_clearance_level" id="security_clearance_level"
                                        class="select select-bordered w-full @error('security_clearance_level') border-destructive @enderror">
                                        <option value="">Select clearance level</option>
                                        <option value="BASIC" @if(old('security_clearance_level', $user->
                                            security_clearance_level) === 'BASIC') selected @endif>Basic - Standard
                                            Access</option>
                                        <option value="ELEVATED" @if(old('security_clearance_level', $user->
                                            security_clearance_level) === 'ELEVATED') selected @endif>Elevated -
                                            Enhanced Access</option>
                                        <option value="CRITICAL" @if(old('security_clearance_level', $user->
                                            security_clearance_level) === 'CRITICAL') selected @endif>Critical - Maximum
                                            Access</option>
                                    </select>
                                    @error('security_clearance_level')
                                    <p class="text-sm text-destructive flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>

                                <!-- Dynamic Permissions -->
                                <div class="space-y-3" id="permissionsSection">
                                    <label class="text-sm font-medium text-foreground">System Permissions</label>
                                    <div class="bg-muted/30 rounded-lg p-4 space-y-3" id="permissionsList">
                                        @foreach(['can_approve_variances' => 'Approve Variances',
                                        'can_approve_purchases' => 'Approve Purchases', 'can_modify_prices' => 'Modify
                                        Prices', 'can_access_financial_data' => 'Access Financial Data',
                                        'can_export_data' => 'Export Data'] as $permission => $label)
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="{{ $permission }}" value="1"
                                                class="checkbox checkbox-primary" @if(old($permission,
                                                $user->$permission)) checked @endif
                                            @if(in_array($user->role, ['CEO', 'SYSTEM_ADMIN'])) disabled @endif>
                                            <span
                                                class="text-sm {{ old($permission, $user->$permission) ? 'text-foreground' : 'text-muted-foreground' }}">
                                                {{ $label }}
                                            </span>
                                            @if(old($permission, $user->$permission) && in_array($user->role, ['CEO',
                                            'SYSTEM_ADMIN']))
                                            <span class="badge badge-primary text-xs ml-auto">Required</span>
                                            @endif
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Max Approval Amount -->
                                <div class="space-y-2">
                                    <label for="max_approval_amount" class="text-sm font-medium text-foreground">Maximum
                                        Approval Amount (UGX)</label>
                                    <input type="number" name="max_approval_amount" id="max_approval_amount"
                                        value="{{ old('max_approval_amount', $user->max_approval_amount) }}" min="0"
                                        step="1000" class="input input-bordered w-full" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Account Status -->
                    <div class="bg-card rounded-lg border border-border p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-toggle-on text-blue-600 text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-foreground">Account Status</h3>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary"
                                    @if(old('is_active', $user->is_active)) checked @endif
                                @if(!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) && auth()->id() ==
                                $user->id) disabled @endif>
                                <div>
                                    <div class="text-sm font-medium text-foreground">Active Account</div>
                                    <div class="text-xs text-muted-foreground">User can log in and access the system
                                    </div>
                                </div>
                            </label>

                            @if($user->account_locked_until && $user->account_locked_until > now())
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 text-orange-800">
                                    <i class="fas fa-lock text-sm"></i>
                                    <span class="text-sm font-medium">Account Locked</span>
                                </div>
                                <p class="text-xs text-orange-700 mt-1">
                                    Until: {{ \Carbon\Carbon::parse($user->account_locked_until)->format('M j, Y g:i A')
                                    }}
                                </p>
                            </div>
                            @endif

                            @if($user->failed_login_attempts > 0)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle text-sm"></i>
                                    <span class="text-sm font-medium">Failed Login Attempts</span>
                                </div>
                                <p class="text-xs text-yellow-700 mt-1">
                                    {{ $user->failed_login_attempts }} failed attempts
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Station Assignments -->
                    @if($assignedStations && count($assignedStations) > 0)
                    <div class="bg-card rounded-lg border border-border p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-gas-pump text-green-600 text-sm"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-foreground">Station Assignments</h3>
                            </div>
                            @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']))
                            <a href="{{ route('users.stations', $user->id) }}" class="btn btn-ghost btn-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($stations->whereIn('id', $assignedStations) as $station)
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-foreground">{{ $station->station_name }}</span>
                                <span class="text-xs text-muted-foreground ml-auto">{{ $station->station_code }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2"
                            id="saveButton">
                            <i class="fas fa-save text-sm"></i>
                            Save Changes
                        </button>

                        <a href="{{ route('users.index') }}"
                            class="btn btn-ghost w-full flex items-center justify-center gap-2">
                            <i class="fas fa-times text-sm"></i>
                            Cancel
                        </a>
                    </div>

                    <!-- Danger Zone -->
                    @if(in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) && auth()->id() != $user->id)
                    <div class="bg-card rounded-lg border border-destructive/20 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-6 h-6 bg-destructive/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-destructive text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-destructive">Danger Zone</h3>
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm text-muted-foreground">
                                These actions cannot be undone. Please be certain before proceeding.
                            </p>

                            <button type="button"
                                class="btn btn-outline btn-sm w-full text-destructive border-destructive hover:bg-destructive hover:text-destructive-foreground"
                                onclick="resetPassword()">
                                <i class="fas fa-key text-xs mr-2"></i>
                                Reset Password
                            </button>

                            @if($user->failed_login_attempts > 0 || ($user->account_locked_until &&
                            $user->account_locked_until > now()))
                            <button type="button"
                                class="btn btn-outline btn-sm w-full text-orange-600 border-orange-600 hover:bg-orange-600 hover:text-white"
                                onclick="unlockAccount()">
                                <i class="fas fa-unlock text-xs mr-2"></i>
                                Unlock Account
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Role-based defaults (same as create form)
const roleDefaults = {
    'CEO': {
        department: 'EXECUTIVE',
        security_clearance: 'CRITICAL',
        permissions: {
            'can_approve_variances': true,
            'can_approve_purchases': true,
            'can_modify_prices': true,
            'can_access_financial_data': true,
            'can_export_data': true
        },
        max_approval_amount: 999999999.99
    },
    'SYSTEM_ADMIN': {
        department: 'IT',
        security_clearance: 'CRITICAL',
        permissions: {
            'can_approve_variances': true,
            'can_approve_purchases': true,
            'can_modify_prices': true,
            'can_access_financial_data': true,
            'can_export_data': true
        },
        max_approval_amount: 999999999.99
    },
    'STATION_MANAGER': {
        department: 'OPERATIONS',
        security_clearance: 'ELEVATED',
        permissions: {
            'can_approve_variances': false,
            'can_approve_purchases': false,
            'can_modify_prices': false,
            'can_access_financial_data': false,
            'can_export_data': false
        },
        max_approval_amount: 50000.00
    },
    'DELIVERY_SUPERVISOR': {
        department: 'LOGISTICS',
        security_clearance: 'BASIC',
        permissions: {
            'can_approve_variances': false,
            'can_approve_purchases': false,
            'can_modify_prices': false,
            'can_access_financial_data': false,
            'can_export_data': false
        },
        max_approval_amount: 10000.00
    },
    'AUDITOR': {
        department: 'FINANCE',
        security_clearance: 'ELEVATED',
        permissions: {
            'can_approve_variances': false,
            'can_approve_purchases': false,
            'can_modify_prices': false,
            'can_access_financial_data': true,
            'can_export_data': true
        },
        max_approval_amount: 0.00
    }
};

function updateRolePermissions(role) {
    const defaults = roleDefaults[role];
    if (!defaults) return;

    // Show confirmation for role changes
    const currentRole = '{{ $user->role }}';
    if (role !== currentRole) {
        Swal.fire({
            title: 'Role Change Confirmation',
            text: `Are you sure you want to change the role from ${currentRole.replace('_', ' ')} to ${role.replace('_', ' ')}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'hsl(var(--primary))',
            cancelButtonColor: 'hsl(var(--muted))',
            confirmButtonText: 'Yes, change role',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) {
                // Revert to current role
                document.querySelector(`input[name="role"][value="${currentRole}"]`).checked = true;
                return;
            }
        });
    }

    // Update department
    document.getElementById('department').value = defaults.department;

    // Update security clearance
    document.getElementById('security_clearance_level').value = defaults.security_clearance;

    // Update max approval amount
    document.getElementById('max_approval_amount').value = defaults.max_approval_amount;

    // Update permissions checkboxes
    for (const [permission, enabled] of Object.entries(defaults.permissions)) {
        const checkbox = document.querySelector(`input[name="${permission}"]`);
        if (checkbox && !checkbox.disabled) {
            checkbox.checked = enabled;
        }
    }
}

function resetPassword() {
    Swal.fire({
        title: 'Reset Password',
        text: 'This will reset the user\'s password to "password123" and require them to change it on next login.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--destructive))',
        cancelButtonColor: 'hsl(var(--muted))',
        confirmButtonText: 'Reset Password',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would make an AJAX request to reset password
            // For now, just show success message
            Swal.fire({
                title: 'Password Reset',
                text: 'Password has been reset successfully. Temporary password: password123',
                icon: 'success',
                confirmButtonColor: 'hsl(var(--primary))'
            });
        }
    });
}

function unlockAccount() {
    Swal.fire({
        title: 'Unlock Account',
        text: 'This will unlock the user account and reset failed login attempts.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--primary))',
        cancelButtonColor: 'hsl(var(--muted))',
        confirmButtonText: 'Unlock Account',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would make an AJAX request to unlock account
            Swal.fire({
                title: 'Account Unlocked',
                text: 'The user account has been unlocked successfully.',
                icon: 'success',
                confirmButtonColor: 'hsl(var(--primary))'
            });
        }
    });
}

// Form submission
document.getElementById('editUserForm').addEventListener('submit', function() {
    const saveButton = document.getElementById('saveButton');
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Saving Changes...';
    saveButton.disabled = true;
});

// Unsaved changes detection
let formChanged = false;
document.getElementById('editUserForm').addEventListener('change', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endpush
@endsection
