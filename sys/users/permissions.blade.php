@extends('layouts.app')

@section('title', 'Permissions - ' . $user->first_name . ' ' . $user->last_name)

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
                            <h1 class="text-2xl font-bold text-foreground">Permission Management</h1>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-sm text-muted-foreground">{{ $user->first_name }} {{ $user->last_name
                                    }}</span>
                                <span class="text-sm text-muted-foreground">•</span>
                                <span class="text-sm text-muted-foreground">{{ $user->employee_number }}</span>
                                <span
                                    class="badge {{ $user->role === 'CEO' ? 'badge-primary' : ($user->role === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }}">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                                <span class="badge badge-outline text-xs">
                                    {{ $user->security_clearance_level }} Clearance
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <button type="button" class="btn btn-ghost flex items-center gap-2" onclick="resetToDefaults()">
                        <i class="fas fa-undo text-sm"></i>
                        Reset to Role Defaults
                    </button>
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <i class="fas fa-shield-alt text-xs"></i>
                        <span>Security Management</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Management Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Permission Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Permission Matrix -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Core System Permissions -->
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-key text-primary-foreground text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Core System Permissions</h2>
                            <p class="text-sm text-muted-foreground">Fundamental access controls for system operations
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @php
                        $corePermissions = [
                        'can_approve_variances' => [
                        'title' => 'Approve Variances',
                        'description' => 'Can approve fuel inventory variances and discrepancies',
                        'risk' => 'HIGH',
                        'dependencies' => []
                        ],
                        'can_approve_purchases' => [
                        'title' => 'Approve Purchases',
                        'description' => 'Can approve purchase orders and supplier transactions',
                        'risk' => 'HIGH',
                        'dependencies' => []
                        ],
                        'can_modify_prices' => [
                        'title' => 'Modify Prices',
                        'description' => 'Can change fuel selling prices and pricing strategies',
                        'risk' => 'CRITICAL',
                        'dependencies' => []
                        ],
                        'can_access_financial_data' => [
                        'title' => 'Access Financial Data',
                        'description' => 'Can view financial reports, costs, and revenue data',
                        'risk' => 'MEDIUM',
                        'dependencies' => []
                        ],
                        'can_export_data' => [
                        'title' => 'Export Data',
                        'description' => 'Can export system data and generate reports',
                        'risk' => 'MEDIUM',
                        'dependencies' => ['can_access_financial_data']
                        ]
                        ];
                        @endphp

                        @foreach($corePermissions as $permission => $details)
                        <div class="permission-item border border-border rounded-lg p-4 hover:bg-accent/20 transition-colors"
                            data-permission="{{ $permission }}" data-risk="{{ $details['risk'] }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-sm font-semibold text-foreground">{{ $details['title'] }}</h3>
                                        <span class="badge {{
                                            $details['risk'] === 'CRITICAL' ? 'badge-error' : (
                                            $details['risk'] === 'HIGH' ? 'badge-warning' : 'badge-secondary'
                                        ) }} text-xs">
                                            {{ $details['risk'] }} RISK
                                        </span>
                                        @if(in_array($user->role, ['CEO', 'SYSTEM_ADMIN']) && $user->$permission)
                                        <span class="badge badge-primary text-xs">Required by Role</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-2">{{ $details['description'] }}</p>

                                    @if(!empty($details['dependencies']))
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <i class="fas fa-link text-xs"></i>
                                        <span>Requires:
                                            @foreach($details['dependencies'] as $dep)
                                            {{ $corePermissions[$dep]['title'] ?? $dep }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Permission Toggle -->
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" class="toggle toggle-primary permission-toggle"
                                            data-permission="{{ $permission }}" @if($user->$permission) checked @endif
                                        @if(in_array($user->role, ['CEO', 'SYSTEM_ADMIN'])) disabled @endif
                                        onchange="updatePermission('{{ $permission }}', this.checked)">
                                    </label>
                                    @if($user->$permission)
                                    <div class="w-2 h-2 bg-green-500 rounded-full" title="Permission Granted"></div>
                                    @else
                                    <div class="w-2 h-2 bg-gray-300 rounded-full" title="Permission Denied"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Advanced Permissions -->
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cogs text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Advanced System Access</h2>
                            <p class="text-sm text-muted-foreground">Specialized permissions for advanced operations</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @php
                        $advancedPermissions = [
                        'max_approval_amount' => [
                        'title' => 'Maximum Approval Amount',
                        'description' => 'Maximum value this user can approve in UGX',
                        'type' => 'amount',
                        'risk' => 'CRITICAL'
                        ],
                        'security_clearance_level' => [
                        'title' => 'Security Clearance Level',
                        'description' => 'User\'s security clearance level for sensitive operations',
                        'type' => 'select',
                        'risk' => 'HIGH',
                        'options' => ['BASIC', 'ELEVATED', 'CRITICAL']
                        ]
                        ];
                        @endphp

                        @foreach($advancedPermissions as $permission => $details)
                        <div
                            class="permission-item border border-border rounded-lg p-4 hover:bg-accent/20 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-sm font-semibold text-foreground">{{ $details['title'] }}</h3>
                                        <span class="badge {{
                                            $details['risk'] === 'CRITICAL' ? 'badge-error' : 'badge-warning'
                                        }} text-xs">
                                            {{ $details['risk'] }} RISK
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-3">{{ $details['description'] }}</p>
                                </div>

                                <!-- Permission Control -->
                                <div class="w-48">
                                    @if($details['type'] === 'amount')
                                    <div class="space-y-2">
                                        <input type="number" class="input input-bordered w-full text-sm"
                                            value="{{ number_format($user->$permission, 2) }}" min="0" step="1000"
                                            onchange="updateApprovalAmount(this.value)" placeholder="0.00">
                                        <div class="text-xs text-muted-foreground">
                                            Current: UGX {{ number_format($user->$permission, 0) }}
                                        </div>
                                    </div>
                                    @elseif($details['type'] === 'select')
                                    <select class="select select-bordered w-full text-sm"
                                        onchange="updateSecurityClearance(this.value)">
                                        @foreach($details['options'] as $option)
                                        <option value="{{ $option }}" @if($user->$permission === $option) selected
                                            @endif>
                                            {{ $option }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Permission Summary -->
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-pie text-blue-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Permission Summary</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Active Permissions</span>
                            <span class="text-sm font-semibold text-foreground" id="activeCount">
                                {{ collect(['can_approve_variances', 'can_approve_purchases', 'can_modify_prices',
                                'can_access_financial_data', 'can_export_data'])->filter(fn($p) => $user->$p)->count()
                                }}/5
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Security Level</span>
                            <span class="badge {{
                                $user->security_clearance_level === 'CRITICAL' ? 'badge-error' : (
                                $user->security_clearance_level === 'ELEVATED' ? 'badge-warning' : 'badge-default'
                            ) }} text-xs">
                                {{ $user->security_clearance_level }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Max Approval</span>
                            <span class="text-sm font-semibold text-foreground">
                                UGX {{ number_format($user->max_approval_amount, 0) }}
                            </span>
                        </div>

                        <!-- Permission Progress -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">Permission Coverage</span>
                                <span class="text-foreground" id="permissionPercentage">
                                    {{ round((collect(['can_approve_variances', 'can_approve_purchases',
                                    'can_modify_prices', 'can_access_financial_data', 'can_export_data'])->filter(fn($p)
                                    => $user->$p)->count() / 5) * 100) }}%
                                </span>
                            </div>
                            <div class="progress progress-primary h-2" id="permissionProgress">
                                <div class="progress-bar"
                                    style="width: {{ round((collect(['can_approve_variances', 'can_approve_purchases', 'can_modify_prices', 'can_access_financial_data', 'can_export_data'])->filter(fn($p) => $user->$p)->count() / 5) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Information -->
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-shield text-green-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Role Information</h3>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="text-sm font-medium text-foreground mb-1">Current Role</div>
                            <div
                                class="badge {{ $user->role === 'CEO' ? 'badge-primary' : ($user->role === 'SYSTEM_ADMIN' ? 'badge-secondary' : 'badge-outline') }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-foreground mb-1">Department</div>
                            <div class="text-sm text-muted-foreground">{{ $user->department }}</div>
                        </div>

                        @if(in_array($user->role, ['CEO', 'SYSTEM_ADMIN']))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center gap-2 text-blue-800 mb-1">
                                <i class="fas fa-crown text-sm"></i>
                                <span class="text-sm font-medium">Administrative Role</span>
                            </div>
                            <p class="text-xs text-blue-700">
                                This user has administrative privileges with automatic approval for all permissions.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Permission Changes -->
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-6 h-6 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-history text-purple-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Recent Changes</h3>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <i class="fas fa-clock text-xs"></i>
                            <span>No recent permission changes</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button type="button" class="btn btn-primary w-full flex items-center justify-center gap-2"
                        onclick="savePermissions()">
                        <i class="fas fa-save text-sm"></i>
                        Save Permission Changes
                    </button>

                    <a href="{{ route('users.edit', $user->id) }}"
                        class="btn btn-ghost w-full flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left text-sm"></i>
                        Back to User Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let permissionChanges = {};
let hasUnsavedChanges = false;

function updatePermission(permission, checked) {
    if (checked && permission === 'can_export_data' && !document.querySelector('[data-permission="can_access_financial_data"] input').checked) {
        Swal.fire({
            title: 'Permission Dependency',
            text: 'Export Data permission requires Access Financial Data permission. Would you like to enable both?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'hsl(var(--primary))',
            cancelButtonColor: 'hsl(var(--muted))',
            confirmButtonText: 'Enable Both',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('[data-permission="can_access_financial_data"] input').checked = true;
                updatePermission('can_access_financial_data', true);
                recordPermissionChange(permission, checked);
            } else {
                document.querySelector(`[data-permission="${permission}"] input`).checked = false;
            }
        });
        return;
    }

    recordPermissionChange(permission, checked);
    updatePermissionIndicator(permission, checked);
    updatePermissionSummary();
}

function updateApprovalAmount(amount) {
    const numAmount = parseFloat(amount) || 0;

    if (numAmount > 1000000000) {
        Swal.fire({
            title: 'High Approval Amount',
            text: 'You are setting a very high approval amount. Are you sure this is correct?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'hsl(var(--primary))',
            cancelButtonColor: 'hsl(var(--muted))',
            confirmButtonText: 'Confirm Amount',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                recordPermissionChange('max_approval_amount', numAmount);
            }
        });
    } else {
        recordPermissionChange('max_approval_amount', numAmount);
    }
}

function updateSecurityClearance(level) {
    if (level === 'CRITICAL') {
        Swal.fire({
            title: 'Critical Security Clearance',
            text: 'You are granting CRITICAL security clearance. This provides maximum system access.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'hsl(var(--destructive))',
            cancelButtonColor: 'hsl(var(--muted))',
            confirmButtonText: 'Grant Critical Access',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                recordPermissionChange('security_clearance_level', level);
            } else {
                event.target.value = '{{ $user->security_clearance_level }}';
            }
        });
    } else {
        recordPermissionChange('security_clearance_level', level);
    }
}

function recordPermissionChange(permission, value) {
    permissionChanges[permission] = value;
    hasUnsavedChanges = true;

    // Visual feedback
    const button = document.querySelector('.btn-primary');
    if (Object.keys(permissionChanges).length > 0) {
        button.classList.add('animate-pulse');
        button.innerHTML = '<i class="fas fa-save text-sm"></i> Save Changes (' + Object.keys(permissionChanges).length + ')';
    }
}

function updatePermissionIndicator(permission, granted) {
    const indicator = document.querySelector(`[data-permission="${permission}"] .w-2`);
    if (indicator) {
        indicator.className = granted ? 'w-2 h-2 bg-green-500 rounded-full' : 'w-2 h-2 bg-gray-300 rounded-full';
        indicator.title = granted ? 'Permission Granted' : 'Permission Denied';
    }
}

function updatePermissionSummary() {
    const checkboxes = document.querySelectorAll('.permission-toggle');
    const activeCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const percentage = Math.round((activeCount / checkboxes.length) * 100);

    document.getElementById('activeCount').textContent = `${activeCount}/${checkboxes.length}`;
    document.getElementById('permissionPercentage').textContent = `${percentage}%`;

    const progressBar = document.querySelector('#permissionProgress .progress-bar');
    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
    }
}

function resetToDefaults() {
    Swal.fire({
        title: 'Reset to Role Defaults',
        text: 'This will reset all permissions to the default values for this user\'s role. All custom permission changes will be lost.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--destructive))',
        cancelButtonColor: 'hsl(var(--muted))',
        confirmButtonText: 'Reset Permissions',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would make an AJAX request to reset permissions
            Swal.fire({
                title: 'Permissions Reset',
                text: 'User permissions have been reset to role defaults.',
                icon: 'success',
                confirmButtonColor: 'hsl(var(--primary))'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

function savePermissions() {
    if (Object.keys(permissionChanges).length === 0) {
        Swal.fire({
            title: 'No Changes',
            text: 'No permission changes have been made.',
            icon: 'info',
            confirmButtonColor: 'hsl(var(--primary))'
        });
        return;
    }

    Swal.fire({
        title: 'Save Permission Changes',
        text: `You are about to save ${Object.keys(permissionChanges).length} permission changes. This action will be logged for security audit.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'hsl(var(--primary))',
        cancelButtonColor: 'hsl(var(--muted))',
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would make an AJAX request to save permissions
            const button = document.querySelector('.btn-primary');
            button.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Saving...';
            button.disabled = true;

            // Simulate API call
            setTimeout(() => {
                Swal.fire({
                    title: 'Permissions Saved',
                    text: 'User permissions have been updated successfully.',
                    icon: 'success',
                    confirmButtonColor: 'hsl(var(--primary))'
                }).then(() => {
                    permissionChanges = {};
                    hasUnsavedChanges = false;
                    button.innerHTML = '<i class="fas fa-save text-sm"></i> Save Permission Changes';
                    button.disabled = false;
                    button.classList.remove('animate-pulse');
                });
            }, 1500);
        }
    });
}

// Unsaved changes warning
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved permission changes. Are you sure you want to leave?';
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updatePermissionSummary();
});
</script>
@endpush
@endsection
