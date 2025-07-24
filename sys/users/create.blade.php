@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Header Section -->
    <div class="bg-card border-b border-border">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('users.index') }}"
                    class="btn btn-ghost btn-circle hover:bg-accent/50 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Create New User</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Add a new user account with appropriate roles and permissions
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('users.store') }}" id="createUserForm" novalidate>
            @csrf

            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-primary font-medium">Basic Information</span>
                    <span class="text-muted-foreground">→</span>
                    <span class="text-muted-foreground">Role & Permissions</span>
                    <span class="text-muted-foreground">→</span>
                    <span class="text-muted-foreground">Station Assignment</span>
                </div>
                <div class="mt-2 h-1 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 33.33%"
                        id="progressBar"></div>
                </div>
            </div>

            <!-- Step 1: Basic Information -->
            <div class="space-y-8" id="step1">
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-primary-foreground text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Personal Information</h2>
                            <p class="text-sm text-muted-foreground">Enter the user's basic details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div class="space-y-2">
                            <label for="first_name" class="text-sm font-medium text-foreground flex items-center gap-2">
                                First Name
                                <span class="text-destructive">*</span>
                            </label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                                class="input input-bordered w-full @error('first_name') border-destructive focus:ring-destructive @enderror"
                                placeholder="Enter first name" required autocomplete="given-name">
                            @error('first_name')
                            <p class="text-sm text-destructive flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label for="last_name" class="text-sm font-medium text-foreground flex items-center gap-2">
                                Last Name
                                <span class="text-destructive">*</span>
                            </label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                class="input input-bordered w-full @error('last_name') border-destructive focus:ring-destructive @enderror"
                                placeholder="Enter last name" required autocomplete="family-name">
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
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="input input-bordered w-full @error('email') border-destructive focus:ring-destructive @enderror"
                                placeholder="user@company.com" required autocomplete="email">
                            <div id="emailValidation"
                                class="text-xs text-muted-foreground opacity-0 transition-opacity">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                Checking availability...
                            </div>
                            @error('email')
                            <p class="text-sm text-destructive flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Hire Date -->
                        <div class="space-y-2">
                            <label for="hire_date" class="text-sm font-medium text-foreground flex items-center gap-2">
                                Hire Date
                                <span class="text-destructive">*</span>
                            </label>
                            <input type="date" name="hire_date" id="hire_date"
                                value="{{ old('hire_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                                class="input input-bordered w-full @error('hire_date') border-destructive focus:ring-destructive @enderror"
                                required>
                            @error('hire_date')
                            <p class="text-sm text-destructive flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Auto-Generated Fields Display -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Employee Number</label>
                            <div class="input input-bordered bg-muted/50 text-muted-foreground flex items-center gap-2">
                                <i class="fas fa-magic text-xs"></i>
                                Auto-generated on save
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-muted-foreground">Username</label>
                            <div class="input input-bordered bg-muted/50 text-muted-foreground flex items-center gap-2"
                                id="usernamePreview">
                                <i class="fas fa-magic text-xs"></i>
                                Will be generated from name
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="button" class="btn btn-primary flex items-center gap-2" onclick="nextStep(2)">
                            Next: Role & Permissions
                            <i class="fas fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Role & Permissions -->
            <div class="space-y-8 hidden" id="step2">
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-primary-foreground text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Role & Security</h2>
                            <p class="text-sm text-muted-foreground">Configure user access and permissions</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Role Selection -->
                        <div class="space-y-4">
                            <label for="role" class="text-sm font-medium text-foreground flex items-center gap-2">
                                User Role
                                <span class="text-destructive">*</span>
                            </label>

                            <div class="space-y-3">
                                @foreach(['CEO' => 'Chief Executive Officer', 'SYSTEM_ADMIN' => 'System Administrator',
                                'STATION_MANAGER' => 'Station Manager', 'DELIVERY_SUPERVISOR' => 'Delivery Supervisor',
                                'AUDITOR' => 'Auditor'] as $roleValue => $roleLabel)
                                <label
                                    class="flex items-start gap-3 p-4 border border-border rounded-lg hover:bg-accent/30 transition-colors cursor-pointer role-option @if(old('role') === $roleValue) border-primary bg-primary/5 @endif">
                                    <input type="radio" name="role" value="{{ $roleValue }}"
                                        class="radio radio-primary mt-1" @if(old('role')===$roleValue) checked @endif
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
                                    <option value="BASIC" @if(old('security_clearance_level')==='BASIC' ) selected
                                        @endif>Basic - Standard Access</option>
                                    <option value="ELEVATED" @if(old('security_clearance_level')==='ELEVATED' ) selected
                                        @endif>Elevated - Enhanced Access</option>
                                    <option value="CRITICAL" @if(old('security_clearance_level')==='CRITICAL' ) selected
                                        @endif>Critical - Maximum Access</option>
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
                                    <p class="text-sm text-muted-foreground">Select a role to see available permissions
                                    </p>
                                </div>
                            </div>

                            <!-- Department (Auto-filled) -->
                            <div class="space-y-2">
                                <label for="department" class="text-sm font-medium text-foreground">Department</label>
                                <input type="text" name="department" id="department" value="{{ old('department') }}"
                                    class="input input-bordered w-full bg-muted/50"
                                    placeholder="Auto-assigned based on role" readonly>
                            </div>

                            <!-- Max Approval Amount -->
                            <div class="space-y-2" id="approvalAmountSection" style="display: none;">
                                <label for="max_approval_amount" class="text-sm font-medium text-foreground">Maximum
                                    Approval Amount (UGX)</label>
                                <input type="number" name="max_approval_amount" id="max_approval_amount"
                                    value="{{ old('max_approval_amount') }}" min="0" step="1000"
                                    class="input input-bordered w-full" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn btn-ghost flex items-center gap-2" onclick="previousStep(1)">
                            <i class="fas fa-arrow-left text-sm"></i>
                            Previous
                        </button>
                        <button type="button" class="btn btn-primary flex items-center gap-2" onclick="nextStep(3)">
                            Next: Station Assignment
                            <i class="fas fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Station Assignment -->
            <div class="space-y-8 hidden" id="step3">
                <div class="bg-card rounded-lg border border-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fas fa-gas-pump text-primary-foreground text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Station Assignment</h2>
                            <p class="text-sm text-muted-foreground">Assign user to fuel stations (optional)</p>
                        </div>
                    </div>

                    @if($stations->count() > 0)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-foreground">Available Stations</label>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-ghost btn-sm" onclick="selectAllStations()">
                                    Select All
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="clearAllStations()">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($stations as $station)
                            <label
                                class="flex items-start gap-3 p-4 border border-border rounded-lg hover:bg-accent/30 transition-colors cursor-pointer station-option">
                                <input type="checkbox" name="station_ids[]" value="{{ $station->id }}"
                                    class="checkbox checkbox-primary mt-1" @if(is_array(old('station_ids')) &&
                                    in_array($station->id, old('station_ids'))) checked @endif>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-foreground">{{ $station->station_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $station->station_code }}</div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ $station->region }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('station_ids')
                        <p class="text-sm text-destructive flex items-center gap-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            {{ $message }}
                        </p>
                        @enderror

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                                <div class="text-sm text-blue-800">
                                    <strong>Station Assignment Notes:</strong>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        <li>The first selected station will be the primary assignment</li>
                                        <li>Additional stations will be secondary assignments</li>
                                        <li>You can modify assignments later from the user management page</li>
                                        <li>Some roles (CEO, SYSTEM_ADMIN) have access to all stations by default</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-gas-pump text-4xl text-muted-foreground mb-4"></i>
                        <h3 class="text-sm font-medium text-foreground mb-2">No stations available</h3>
                        <p class="text-sm text-muted-foreground">
                            You need to create stations before assigning users to them.
                        </p>
                    </div>
                    @endif

                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn btn-ghost flex items-center gap-2" onclick="previousStep(2)">
                            <i class="fas fa-arrow-left text-sm"></i>
                            Previous
                        </button>
                        <button type="submit" class="btn btn-primary flex items-center gap-2" id="submitButton">
                            <i class="fas fa-user-plus text-sm"></i>
                            Create User
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentStep = 1;
const totalSteps = 3;

// Role-based defaults
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

function nextStep(step) {
    if (validateCurrentStep()) {
        document.getElementById(`step${currentStep}`).classList.add('hidden');
        document.getElementById(`step${step}`).classList.remove('hidden');
        currentStep = step;
        updateProgress();
    }
}

function previousStep(step) {
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${step}`).classList.remove('hidden');
    currentStep = step;
    updateProgress();
}

function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
}

function validateCurrentStep() {
    if (currentStep === 1) {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const hireDate = document.getElementById('hire_date').value;

        if (!firstName || !lastName || !email || !hireDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                text: 'Please fill in all required fields before proceeding.',
                confirmButtonColor: 'hsl(var(--primary))'
            });
            return false;
        }

        // Update username preview
        updateUsernamePreview(firstName, lastName);
    }

    if (currentStep === 2) {
        const role = document.querySelector('input[name="role"]:checked');
        const securityClearance = document.getElementById('security_clearance_level').value;

        if (!role || !securityClearance) {
            Swal.fire({
                icon: 'warning',
                title: 'Role Selection Required',
                text: 'Please select a role and security clearance level before proceeding.',
                confirmButtonColor: 'hsl(var(--primary))'
            });
            return false;
        }
    }

    return true;
}

function updateUsernamePreview(firstName, lastName) {
    const username = (firstName + lastName.charAt(0)).toLowerCase();
    document.getElementById('usernamePreview').innerHTML = `
        <i class="fas fa-user text-xs"></i>
        ${username} (will be made unique)
    `;
}

function updateRolePermissions(role) {
    const defaults = roleDefaults[role];
    if (!defaults) return;

    // Update department
    document.getElementById('department').value = defaults.department;

    // Update security clearance
    document.getElementById('security_clearance_level').value = defaults.security_clearance;

    // Update permissions display
    const permissionsList = document.getElementById('permissionsList');
    const approvalSection = document.getElementById('approvalAmountSection');

    let permissionsHtml = '';
    const permissionLabels = {
        'can_approve_variances': 'Approve Variances',
        'can_approve_purchases': 'Approve Purchases',
        'can_modify_prices': 'Modify Prices',
        'can_access_financial_data': 'Access Financial Data',
        'can_export_data': 'Export Data'
    };

    for (const [permission, enabled] of Object.entries(defaults.permissions)) {
        permissionsHtml += `
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox"
                       name="${permission}"
                       value="1"
                       class="checkbox checkbox-primary"
                       ${enabled ? 'checked' : ''}
                       ${['CEO', 'SYSTEM_ADMIN'].includes(role) ? 'disabled' : ''}>
                <span class="text-sm ${enabled ? 'text-foreground' : 'text-muted-foreground'}">
                    ${permissionLabels[permission]}
                </span>
                ${enabled && ['CEO', 'SYSTEM_ADMIN'].includes(role) ?
                    '<span class="badge badge-primary text-xs ml-auto">Required</span>' : ''}
            </label>
        `;
    }

    permissionsList.innerHTML = permissionsHtml;

    // Show/hide approval amount
    if (defaults.max_approval_amount > 0) {
        approvalSection.style.display = 'block';
        document.getElementById('max_approval_amount').value = defaults.max_approval_amount;
    } else {
        approvalSection.style.display = 'none';
    }
}

function selectAllStations() {
    document.querySelectorAll('input[name="station_ids[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function clearAllStations() {
    document.querySelectorAll('input[name="station_ids[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Email validation
let emailTimeout;
document.getElementById('email').addEventListener('input', function() {
    clearTimeout(emailTimeout);
    const email = this.value.trim();
    const validation = document.getElementById('emailValidation');

    if (email.length > 3 && email.includes('@')) {
        validation.style.opacity = '1';

        emailTimeout = setTimeout(() => {
            // Simulate email validation
            setTimeout(() => {
                validation.innerHTML = '<i class="fas fa-check text-green-600 mr-1"></i>Email available';
                validation.className = 'text-xs text-green-600 opacity-100 transition-opacity';
            }, 500);
        }, 500);
    } else {
        validation.style.opacity = '0';
    }
});

// Form submission
document.getElementById('createUserForm').addEventListener('submit', function() {
    const submitButton = document.getElementById('submitButton');
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Creating User...';
    submitButton.disabled = true;
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default hire date to today
    document.getElementById('hire_date').value = new Date().toISOString().split('T')[0];

    // Pre-select role if coming back from validation error
    const selectedRole = document.querySelector('input[name="role"]:checked');
    if (selectedRole) {
        updateRolePermissions(selectedRole.value);
    }
});
</script>
@endpush
@endsection
