<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN see all users immediately
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            $query = DB::table('users')
                ->leftJoin('user_stations', 'users.id', '=', 'user_stations.user_id')
                ->leftJoin('stations', 'user_stations.station_id', '=', 'stations.id')
                ->select([
                    'users.id',
                    'users.employee_number',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.role',
                    'users.department',
                    'users.is_active',
                    'users.last_login_at',
                    'users.failed_login_attempts',
                    'users.account_locked_until',
                    DB::raw('GROUP_CONCAT(DISTINCT stations.station_name) as assigned_stations'),
                    DB::raw('COUNT(DISTINCT user_stations.station_id) as station_count')
                ]);
        } else {
            // Station-scoped access for non-admin users
            $userStations = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('is_active', 1)
                ->pluck('station_id');

            $query = DB::table('users')
                ->leftJoin('user_stations', 'users.id', '=', 'user_stations.user_id')
                ->leftJoin('stations', 'user_stations.station_id', '=', 'stations.id')
                ->whereIn('user_stations.station_id', $userStations)
                ->select([
                    'users.id',
                    'users.employee_number',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.role',
                    'users.department',
                    'users.is_active',
                    'users.last_login_at',
                    'users.failed_login_attempts',
                    'users.account_locked_until',
                    DB::raw('GROUP_CONCAT(DISTINCT stations.station_name) as assigned_stations'),
                    DB::raw('COUNT(DISTINCT user_stations.station_id) as station_count')
                ]);
        }

        $users = $query
            ->when($request->role, fn($q) => $q->where('users.role', $request->role))
            ->when($request->status, fn($q) => $q->where('users.is_active', $request->status === 'active'))
            ->when($request->search, fn($q) => $q->where(function ($query) use ($request) {
                $query->where('users.first_name', 'like', "%{$request->search}%")
                    ->orWhere('users.last_name', 'like', "%{$request->search}%")
                    ->orWhere('users.email', 'like', "%{$request->search}%")
                    ->orWhere('users.employee_number', 'like', "%{$request->search}%");
            }))
            ->groupBy(['users.id', 'users.employee_number', 'users.first_name', 'users.last_name', 'users.email', 'users.role', 'users.department', 'users.is_active', 'users.last_login_at', 'users.failed_login_attempts', 'users.account_locked_until'])
            ->orderBy('users.created_at', 'desc')
            ->paginate(25);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all checks
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            // Auto-approved access - log for audit
            $this->logUserAction('CREATE', 'users', 0, 'User creation page accessed', 'AUTO_APPROVED');
        } else {
            // Standard permission check for other roles
            return redirect()->route('users.index')->with('error', 'Insufficient permissions');
        }

        $stations = DB::table('stations')
            ->where('is_active', 1)
            ->select('id', 'station_name', 'station_code', 'region')
            ->orderBy('station_name')
            ->get();

        return view('users.create', compact('stations'));
    }

    public function store(Request $request)
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all validation checks
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            // Auto-approve and log for audit
            $isAutoApproved = true;
        } else {
            // Standard validation for non-admin roles
            if ($request->role === 'SYSTEM_ADMIN') {
                return redirect()->back()->withErrors(['role' => 'Only CEO can create SYSTEM_ADMIN users']);
            }
            $isAutoApproved = false;
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['SYSTEM_ADMIN', 'CEO', 'STATION_MANAGER', 'DELIVERY_SUPERVISOR', 'AUDITOR'])],
            'department' => 'nullable|string|max:100',
            'hire_date' => 'required|date|before_or_equal:today',
            'station_ids' => 'nullable|array',
            'station_ids.*' => 'exists:stations,id',
            'security_clearance_level' => ['required', Rule::in(['BASIC', 'ELEVATED', 'CRITICAL'])],
            'can_approve_variances' => 'boolean',
            'can_approve_purchases' => 'boolean',
            'can_modify_prices' => 'boolean',
            'can_access_financial_data' => 'boolean',
            'can_export_data' => 'boolean',
            'max_approval_amount' => 'nullable|numeric|min:0'
        ]);

        $userId = DB::transaction(function () use ($request, $isAutoApproved) {
            // Thread-safe employee number generation
            $employeeNumber = DB::table('users')->lockForUpdate()->get()->count() + 1;
            do {
                $empNumber = 'EMP' . str_pad($employeeNumber, 3, '0', STR_PAD_LEFT);
                $exists = DB::table('users')->where('employee_number', $empNumber)->exists();
                if ($exists) $employeeNumber++;
            } while ($exists);

            // Generate unique username
            $baseUsername = strtolower($request->first_name . substr($request->last_name, 0, 1));
            $username = $baseUsername;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            // Set permissions based on role hierarchy
            $permissions = $this->getDefaultPermissions($request->role);

            // Create user
            $userId = DB::table('users')->insertGetId([
                'employee_number' => $empNumber,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Temporary password
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $request->role,
                'department' => $request->department ?? $this->getDepartmentByRole($request->role),
                'hire_date' => $request->hire_date,
                'security_clearance_level' => $request->security_clearance_level,
                'can_approve_variances' => $permissions['can_approve_variances'] || $request->boolean('can_approve_variances'),
                'can_approve_purchases' => $permissions['can_approve_purchases'] || $request->boolean('can_approve_purchases'),
                'can_modify_prices' => $permissions['can_modify_prices'] || $request->boolean('can_modify_prices'),
                'can_access_financial_data' => $permissions['can_access_financial_data'] || $request->boolean('can_access_financial_data'),
                'can_export_data' => $permissions['can_export_data'] || $request->boolean('can_export_data'),
                'max_approval_amount' => $permissions['max_approval_amount'] ?? ($request->max_approval_amount ?? 0.00),
                'is_active' => 1,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Assign stations if provided
            if ($request->station_ids) {
                $stationData = collect($request->station_ids)->map(function ($stationId, $index) use ($userId, $request) {
                    return [
                        'user_id' => $userId,
                        'station_id' => $stationId,
                        'assigned_role' => $this->getStationRole($request->role),
                        'assignment_type' => $index === 0 ? 'PRIMARY' : 'SECONDARY',
                        'can_enter_readings' => in_array($request->role, ['STATION_MANAGER', 'DELIVERY_SUPERVISOR']) ? 1 : 0,
                        'can_approve_deliveries' => $request->role === 'STATION_MANAGER' ? 1 : 0,
                        'can_modify_prices' => in_array($request->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']) ? 1 : 0,
                        'can_approve_variances' => in_array($request->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']) ? 1 : 0,
                        'can_handle_cash' => in_array($request->role, ['STATION_MANAGER']) ? 1 : 0,
                        'can_open_station' => in_array($request->role, ['STATION_MANAGER']) ? 1 : 0,
                        'can_close_station' => in_array($request->role, ['STATION_MANAGER']) ? 1 : 0,
                        'access_level' => $this->getAccessLevel($request->role),
                        'assignment_start_date' => $request->hire_date,
                        'assigned_by' => auth()->id(),
                        'is_active' => 1
                    ];
                });

                DB::table('user_stations')->insert($stationData->toArray());
            }

            $this->logUserAction('CREATE', 'users', $userId, "User created: {$empNumber}", $isAutoApproved ? 'AUTO_APPROVED' : null);
            return $userId;
        });

        return redirect()->route('users.index')
            ->with('success', 'User created successfully. Temporary password: password123');
    }

    public function edit($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all access checks
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            // Auto-approved access - log for audit
            $this->logUserAction('READ', 'users', $id, 'User edit page accessed', 'AUTO_APPROVED');
        } else {
            // Standard permission check - can only edit themselves
            if (auth()->id() != $id) {
                return redirect()->route('users.index')->with('error', 'Insufficient permissions');
            }
        }

        $stations = DB::table('stations')
            ->where('is_active', 1)
            ->select('id', 'station_name', 'station_code')
            ->orderBy('station_name')
            ->get();

        $assignedStations = DB::table('user_stations')
            ->where('user_id', $id)
            ->where('is_active', 1)
            ->pluck('station_id')
            ->toArray();

        return view('users.edit', compact('user', 'stations', 'assignedStations'));
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all validation
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            $isAutoApproved = true;
        } else {
            // Standard validation for non-admin roles
            if ($request->role !== $user->role) {
                return redirect()->back()->withErrors(['role' => 'Only CEO/SYSTEM_ADMIN can change user roles']);
            }
            if ($request->role === 'SYSTEM_ADMIN') {
                return redirect()->back()->withErrors(['role' => 'Only CEO can assign SYSTEM_ADMIN role']);
            }
            $isAutoApproved = false;
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'role' => ['required', Rule::in(['SYSTEM_ADMIN', 'CEO', 'STATION_MANAGER', 'DELIVERY_SUPERVISOR', 'AUDITOR'])],
            'department' => 'nullable|string|max:100',
            'security_clearance_level' => ['required', Rule::in(['BASIC', 'ELEVATED', 'CRITICAL'])],
            'is_active' => 'boolean',
            'can_approve_variances' => 'boolean',
            'can_approve_purchases' => 'boolean',
            'can_modify_prices' => 'boolean',
            'can_access_financial_data' => 'boolean',
            'can_export_data' => 'boolean',
            'max_approval_amount' => 'nullable|numeric|min:0'
        ]);

        DB::transaction(function () use ($request, $id, $user, $isAutoApproved) {
            $changes = [];
            $permissions = $this->getDefaultPermissions($request->role);

            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'department' => $request->department ?? $this->getDepartmentByRole($request->role),
                'security_clearance_level' => $request->security_clearance_level,
                'is_active' => $request->boolean('is_active', true),
                'can_approve_variances' => $permissions['can_approve_variances'] || $request->boolean('can_approve_variances'),
                'can_approve_purchases' => $permissions['can_approve_purchases'] || $request->boolean('can_approve_purchases'),
                'can_modify_prices' => $permissions['can_modify_prices'] || $request->boolean('can_modify_prices'),
                'can_access_financial_data' => $permissions['can_access_financial_data'] || $request->boolean('can_access_financial_data'),
                'can_export_data' => $permissions['can_export_data'] || $request->boolean('can_export_data'),
                'max_approval_amount' => $permissions['max_approval_amount'] ?? ($request->max_approval_amount ?? 0.00),
                'updated_at' => now()
            ];

            // Track changes for audit
            foreach ($updateData as $field => $newValue) {
                if ($user->$field != $newValue) {
                    $changes[] = "{$field}: {$user->$field} → {$newValue}";
                }
            }

            DB::table('users')->where('id', $id)->update($updateData);

            if (!empty($changes)) {
                // Auto-approval for CEO/SYSTEM_ADMIN actions
                $this->logUserAction('UPDATE', 'users', $id, 'User updated: ' . implode(', ', $changes), $isAutoApproved ? 'AUTO_APPROVED' : null);
            }
        });

        return redirect()->route('users.edit', $id)
            ->with('success', 'User updated successfully');
    }

    public function assignStations(Request $request, $id)
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all validation
        if (!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            return redirect()->route('users.index')->with('error', 'Insufficient permissions');
        }

        $request->validate([
            'station_ids' => 'required|array|min:1',
            'station_ids.*' => 'exists:stations,id'
        ]);

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        DB::transaction(function () use ($request, $id, $user) {
            // Deactivate existing assignments
            DB::table('user_stations')
                ->where('user_id', $id)
                ->update(['is_active' => 0, 'assignment_end_date' => now()]);

            // Create new assignments
            $stationData = collect($request->station_ids)->map(function ($stationId, $index) use ($id, $user) {
                return [
                    'user_id' => $id,
                    'station_id' => $stationId,
                    'assigned_role' => $this->getStationRole($user->role),
                    'assignment_type' => $index === 0 ? 'PRIMARY' : 'SECONDARY',
                    'can_enter_readings' => in_array($user->role, ['STATION_MANAGER', 'DELIVERY_SUPERVISOR']) ? 1 : 0,
                    'can_approve_deliveries' => $user->role === 'STATION_MANAGER' ? 1 : 0,
                    'can_modify_prices' => in_array($user->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']) ? 1 : 0,
                    'can_approve_variances' => in_array($user->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']) ? 1 : 0,
                    'can_handle_cash' => $user->role === 'STATION_MANAGER' ? 1 : 0,
                    'can_open_station' => $user->role === 'STATION_MANAGER' ? 1 : 0,
                    'can_close_station' => $user->role === 'STATION_MANAGER' ? 1 : 0,
                    'access_level' => $this->getAccessLevel($user->role),
                    'assignment_start_date' => now()->toDateString(),
                    'assigned_by' => auth()->id(),
                    'is_active' => 1
                ];
            });

            DB::table('user_stations')->insert($stationData->toArray());

            $stationNames = DB::table('stations')
                ->whereIn('id', $request->station_ids)
                ->pluck('station_name')
                ->implode(', ');

            // Auto-approval for CEO/SYSTEM_ADMIN actions
            $this->logUserAction('UPDATE', 'user_stations', $id, "Stations assigned: {$stationNames}", 'AUTO_APPROVED');
        });

        return redirect()->route('users.edit', $id)
            ->with('success', 'Station assignments updated successfully');
    }

    public function activity($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all access checks
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            // Auto-approved access - log for audit
            $this->logUserAction('READ', 'audit_logs', $id, 'User activity viewed', 'AUTO_APPROVED');
        } else {
            // Standard permission check - can only view their own activity
            if (auth()->id() != $id) {
                return redirect()->route('users.index')->with('error', 'Insufficient permissions');
            }
        }

        $activities = DB::table('audit_logs')
            ->where('user_id', $id)
            ->orderBy('timestamp', 'desc')
            ->paginate(50);

        return view('users.activity', compact('user', 'activities'));
    }

    public function permissions($id)
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all checks
        if (!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            return redirect()->route('users.index')->with('error', 'Insufficient permissions');
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // Auto-approved access - log for audit
        $this->logUserAction('READ', 'users', $id, 'User permissions viewed', 'AUTO_APPROVED');

        $permissions = [
            'can_approve_variances',
            'can_approve_purchases',
            'can_modify_prices',
            'can_access_financial_data',
            'can_export_data'
        ];

        return view('users.permissions', compact('user', 'permissions'));
    }

    private function getDefaultPermissions($role)
    {
        return match ($role) {
            'CEO', 'SYSTEM_ADMIN' => [
                'can_approve_variances' => true,
                'can_approve_purchases' => true,
                'can_modify_prices' => true,
                'can_access_financial_data' => true,
                'can_export_data' => true,
                'max_approval_amount' => 999999999.99
            ],
            'STATION_MANAGER' => [
                'can_approve_variances' => false, // Only minor variances
                'can_approve_purchases' => false,
                'can_modify_prices' => false,
                'can_access_financial_data' => false,
                'can_export_data' => false,
                'max_approval_amount' => 50000.00
            ],
            'DELIVERY_SUPERVISOR' => [
                'can_approve_variances' => false,
                'can_approve_purchases' => false,
                'can_modify_prices' => false,
                'can_access_financial_data' => false,
                'can_export_data' => false,
                'max_approval_amount' => 10000.00
            ],
            'AUDITOR' => [
                'can_approve_variances' => false,
                'can_approve_purchases' => false,
                'can_modify_prices' => false,
                'can_access_financial_data' => true,
                'can_export_data' => true,
                'max_approval_amount' => 0.00
            ],
            default => [
                'can_approve_variances' => false,
                'can_approve_purchases' => false,
                'can_modify_prices' => false,
                'can_access_financial_data' => false,
                'can_export_data' => false,
                'max_approval_amount' => 0.00
            ]
        };
    }

    private function getDepartmentByRole($role)
    {
        return match ($role) {
            'SYSTEM_ADMIN' => 'IT',
            'CEO' => 'EXECUTIVE',
            'STATION_MANAGER' => 'OPERATIONS',
            'DELIVERY_SUPERVISOR' => 'LOGISTICS',
            'AUDITOR' => 'FINANCE',
            default => 'GENERAL'
        };
    }

    private function getStationRole($userRole)
    {
        return match ($userRole) {
            'STATION_MANAGER' => 'MANAGER',
            'DELIVERY_SUPERVISOR' => 'SUPERVISOR',
            'AUDITOR' => 'AUDITOR',
            'CEO', 'SYSTEM_ADMIN' => 'MANAGER',
            default => 'OPERATOR'
        };
    }

    private function getAccessLevel($role)
    {
        return match ($role) {
            'SYSTEM_ADMIN', 'CEO' => 'FULL_ACCESS',
            'STATION_MANAGER' => 'MANAGER',
            'DELIVERY_SUPERVISOR' => 'SUPERVISOR',
            'AUDITOR' => 'READ_ONLY',
            default => 'DATA_ENTRY'
        };
    }

    private function logUserAction($action, $table, $recordId, $description, $approvalStatus = null)
    {
        $stationId = null;
        if ($table === 'user_stations') {
            $stationId = DB::table('user_stations')
                ->where('user_id', $recordId)
                ->where('is_active', 1)
                ->value('station_id');
        }

        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'station_id' => $stationId,
            'action_type' => $action,
            'action_category' => 'DATA_ENTRY',
            'table_name' => $table,
            'record_id' => $recordId,
            'change_reason' => $description,
            'new_value_text' => $approvalStatus ? "ACTION: {$approvalStatus} - {$description}" : $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'hash_data' => json_encode(['action' => $action, 'table' => $table, 'record' => $recordId, 'description' => $description]),
            'hash_current' => hash('sha256', $action . $table . $recordId . now()),
            'hash_algorithm' => 'SHA256',
            'risk_level' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']) ? 'LOW' : 'MEDIUM',
            'compliance_category' => 'OPERATIONAL'
        ]);
    }

    public function stations($id)
    {
        // ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all validation
        if (!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            return redirect()->route('users.index')->with('error', 'Insufficient permissions');
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        // Auto-approved access - log for audit
        $this->logUserAction('READ', 'user_stations', $id, 'Station assignment page accessed', 'AUTO_APPROVED');

        $stations = DB::table('stations')
            ->where('is_active', 1)
            ->select('id', 'station_name', 'station_code', 'region', 'district')
            ->orderBy('station_name')
            ->get();

        $assignedStations = DB::table('user_stations as us')
            ->join('stations as s', 'us.station_id', '=', 's.id')
            ->leftJoin('users as assigners', 'us.assigned_by', '=', 'assigners.id')
            ->where('us.user_id', $id)
            ->where('us.is_active', 1)
            ->select([
                'us.*',
                's.station_name',
                's.station_code',
                's.region',
                's.district',
                DB::raw('CONCAT(assigners.first_name, " ", assigners.last_name) as assigned_by_name')
            ])
            ->orderBy('us.assignment_type')
            ->orderBy('s.station_name')
            ->get();

        return view('users.stations', compact('user', 'stations', 'assignedStations'));
    }
}