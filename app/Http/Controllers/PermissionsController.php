<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionsController extends Controller
{
    /**
     * PERMISSIONS CONTROLLER - FORENSICALLY VERIFIED SCHEMA COMPLIANCE
     * Schema Lock: ENGAGED with exact FUEL_ERP.sql table/column names
     * CEO/SYSTEM_ADMIN Bypass: ACTIVE with auto-approval logic
     * Zero Phantom Logic: Only database-supported functionality
     */

    public function index(Request $request)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['users', 'user_stations', 'stations', 'audit_logs']);

        // STEP 2: MANDATORY PERMISSION CHECK WITH CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved) {
            return redirect()->back()->with('error', 'Insufficient permissions to manage user permissions');
        }

        // STEP 3: MANDATORY INPUT VALIDATION
        $validatedFilters = $request->validate([
            'search' => 'nullable|string|max:100',
            'role' => 'nullable|in:SYSTEM_ADMIN,CEO,STATION_MANAGER,DELIVERY_SUPERVISOR,AUDITOR',
            'station_id' => 'nullable|exists:stations,id',
            'permission_type' => 'nullable|in:financial,operational,administrative,station_specific'
        ]);

        // STEP 4: QUERY BUILDER ONLY - EXACT SCHEMA COLUMNS
        $usersQuery = DB::table('users')
            ->select([
                'users.id',
                'users.employee_number',
                'users.first_name',
                'users.last_name',
                'users.role',
                'users.department',
                'users.is_active',
                'users.can_approve_variances',
                'users.can_approve_purchases',
                'users.can_modify_prices',
                'users.can_access_financial_data',
                'users.can_export_data',
                'users.max_approval_amount',
                'users.security_clearance_level'
            ])
            ->where('users.is_active', 1);

        // Apply search filter
        if (!empty($validatedFilters['search'])) {
            $searchTerm = $validatedFilters['search'];
            $usersQuery->where(function ($query) use ($searchTerm) {
                $query->where('users.first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('users.last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('users.employee_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply role filter
        if (!empty($validatedFilters['role'])) {
            $usersQuery->where('users.role', $validatedFilters['role']);
        }

        // Apply station filter if provided
        if (!empty($validatedFilters['station_id'])) {
            $usersQuery->join('user_stations', 'users.id', '=', 'user_stations.user_id')
                       ->where('user_stations.station_id', $validatedFilters['station_id'])
                       ->where('user_stations.is_active', 1);
        }

        $users = $usersQuery->orderBy('users.first_name')
                           ->orderBy('users.last_name')
                           ->paginate(20);

        // Get station data for filters - CEO/SYSTEM_ADMIN see all stations
        $stations = DB::table('stations')
            ->select('id', 'station_name', 'station_code')
            ->where('is_active', 1)
            ->orderBy('station_name')
            ->get();

        // Get user-station permissions for context
        $userStationPermissions = [];
        foreach ($users as $user) {
            $userStationPermissions[$user->id] = DB::table('user_stations')
                ->select([
                    'station_id',
                    'assigned_role',
                    'can_enter_readings',
                    'can_approve_deliveries',
                    'can_modify_prices',
                    'can_approve_variances',
                    'can_handle_cash',
                    'can_open_station',
                    'can_close_station',
                    'access_level'
                ])
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->get();
        }

        // STEP 5: LOG ACCESS WITH AUTO-APPROVAL
        $this->logPermissionAction('READ', 'users', null, 'Permissions index accessed', 'AUTO_APPROVED');

        return view('permissions.index', compact('users', 'stations', 'userStationPermissions', 'validatedFilters'));
    }

    public function manage($userId)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['users', 'user_stations', 'stations', 'audit_logs']);

        // STEP 2: MANDATORY PERMISSION CHECK WITH CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved) {
            return redirect()->route('permissions.index')->with('error', 'Insufficient permissions to manage user permissions');
        }

        // STEP 3: VALIDATE USER EXISTS - EXACT SCHEMA COLUMNS
        $targetUser = DB::table('users')
            ->select([
                'id',
                'employee_number',
                'first_name',
                'last_name',
                'role',
                'department',
                'security_clearance_level',
                'is_active',
                'can_approve_variances',
                'can_approve_purchases',
                'can_modify_prices',
                'can_access_financial_data',
                'can_export_data',
                'max_approval_amount'
            ])
            ->where('id', $userId)
            ->first();

        if (!$targetUser) {
            return redirect()->route('permissions.index')->with('error', 'User not found');
        }

        // STEP 4: GET USER STATION ASSIGNMENTS - EXACT SCHEMA COLUMNS
        $userStations = DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->select([
                'user_stations.id as assignment_id',
                'user_stations.station_id',
                'user_stations.assigned_role',
                'user_stations.assignment_type',
                'user_stations.can_enter_readings',
                'user_stations.can_approve_deliveries',
                'user_stations.can_modify_prices',
                'user_stations.can_approve_variances',
                'user_stations.can_handle_cash',
                'user_stations.can_open_station',
                'user_stations.can_close_station',
                'user_stations.access_level',
                'stations.station_name',
                'stations.station_code'
            ])
            ->where('user_stations.user_id', $userId)
            ->where('user_stations.is_active', 1)
            ->orderBy('stations.station_name')
            ->get();

        // STEP 5: LOG ACCESS WITH AUTO-APPROVAL
        $this->logPermissionAction('read', 'users', $userId, 'Permission management accessed', 'AUTO_APPROVED');

        return view('permissions.manage', compact('targetUser', 'userStations'));
    }

    public function updateUserPermissions(Request $request, $userId)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['users', 'audit_logs']);

        // STEP 2: MANDATORY PERMISSION CHECK WITH CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // STEP 3: VALIDATE USER EXISTS
        $targetUser = DB::table('users')->where('id', $userId)->first();
        if (!$targetUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // STEP 4: MANDATORY INPUT VALIDATION WITH EXACT COLUMN NAMES
        $validatedData = $request->validate([
            'can_approve_variances' => 'boolean',
            'can_approve_purchases' => 'boolean',
            'can_modify_prices' => 'boolean',
            'can_access_financial_data' => 'boolean',
            'can_export_data' => 'boolean',
            'max_approval_amount' => 'numeric|min:0|max:999999999.99',
            'change_reason' => 'required|string|max:500'
        ]);

        // STEP 5: TRANSACTION FOR UPDATES
        return DB::transaction(function () use ($validatedData, $userId, $targetUser, $isAutoApproved) {

            // Get current values for change tracking
            $currentPermissions = [
                'can_approve_variances' => $targetUser->can_approve_variances,
                'can_approve_purchases' => $targetUser->can_approve_purchases,
                'can_modify_prices' => $targetUser->can_modify_prices,
                'can_access_financial_data' => $targetUser->can_access_financial_data,
                'can_export_data' => $targetUser->can_export_data,
                'max_approval_amount' => $targetUser->max_approval_amount
            ];

            // Build update data with exact column names
            $updateData = [];
            $changes = [];

            foreach (['can_approve_variances', 'can_approve_purchases', 'can_modify_prices', 'can_access_financial_data', 'can_export_data'] as $permission) {
                if (isset($validatedData[$permission])) {
                    $newValue = $validatedData[$permission] ? 1 : 0;
                    if ($currentPermissions[$permission] != $newValue) {
                        $updateData[$permission] = $newValue;
                        $changes[] = "{$permission}: " . ($currentPermissions[$permission] ? 'TRUE' : 'FALSE') . " → " . ($newValue ? 'TRUE' : 'FALSE');
                    }
                }
            }

            if (isset($validatedData['max_approval_amount']) && $currentPermissions['max_approval_amount'] != $validatedData['max_approval_amount']) {
                $updateData['max_approval_amount'] = $validatedData['max_approval_amount'];
                $changes[] = "max_approval_amount: {$currentPermissions['max_approval_amount']} → {$validatedData['max_approval_amount']}";
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();

                // Update user permissions
                DB::table('users')->where('id', $userId)->update($updateData);

                // Log the change with auto-approval
                $changeDescription = 'User permissions updated: ' . implode(', ', $changes) . ' | Reason: ' . $validatedData['change_reason'];
                $this->logPermissionAction('UPDATE', 'users', $userId, $changeDescription, 'AUTO_APPROVED');

                return response()->json([
                    'success' => true,
                    'message' => '✔ Permissions Updated — Auto-Approved by Role',
                    'changes' => $changes
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes detected',
                    'changes' => []
                ]);
            }
        });
    }

    public function updateStationPermissions(Request $request, $userId)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['user_stations', 'stations', 'audit_logs']);

        // STEP 2: MANDATORY PERMISSION CHECK WITH CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // STEP 3: VALIDATE USER EXISTS
        $userExists = DB::table('users')->where('id', $userId)->exists();
        if (!$userExists) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // STEP 4: MANDATORY INPUT VALIDATION WITH EXACT COLUMN NAMES
        $validatedData = $request->validate([
            'assignment_id' => 'required|exists:user_stations,id',
            'can_enter_readings' => 'boolean',
            'can_approve_deliveries' => 'boolean',
            'can_modify_prices' => 'boolean',
            'can_approve_variances' => 'boolean',
            'can_handle_cash' => 'boolean',
            'can_open_station' => 'boolean',
            'can_close_station' => 'boolean',
            'access_level' => 'required|in:read_ONLY,DATA_ENTRY,SUPERVISOR,MANAGER,FULL_ACCESS',
            'change_reason' => 'required|string|max:500'
        ]);

        // STEP 5: VERIFY ASSIGNMENT BELONGS TO USER
        $assignment = DB::table('user_stations')
            ->where('id', $validatedData['assignment_id'])
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Station assignment not found'], 404);
        }

        // STEP 6: TRANSACTION FOR UPDATES
        return DB::transaction(function () use ($validatedData, $assignment, $isAutoApproved) {

            // Get current values for change tracking
            $currentPermissions = [
                'can_enter_readings' => $assignment->can_enter_readings,
                'can_approve_deliveries' => $assignment->can_approve_deliveries,
                'can_modify_prices' => $assignment->can_modify_prices,
                'can_approve_variances' => $assignment->can_approve_variances,
                'can_handle_cash' => $assignment->can_handle_cash,
                'can_open_station' => $assignment->can_open_station,
                'can_close_station' => $assignment->can_close_station,
                'access_level' => $assignment->access_level
            ];

            // Build update data with exact column names
            $updateData = [];
            $changes = [];

            foreach (['can_enter_readings', 'can_approve_deliveries', 'can_modify_prices', 'can_approve_variances', 'can_handle_cash', 'can_open_station', 'can_close_station'] as $permission) {
                if (isset($validatedData[$permission])) {
                    $newValue = $validatedData[$permission] ? 1 : 0;
                    if ($currentPermissions[$permission] != $newValue) {
                        $updateData[$permission] = $newValue;
                        $changes[] = "{$permission}: " . ($currentPermissions[$permission] ? 'TRUE' : 'FALSE') . " → " . ($newValue ? 'TRUE' : 'FALSE');
                    }
                }
            }

            if ($currentPermissions['access_level'] != $validatedData['access_level']) {
                $updateData['access_level'] = $validatedData['access_level'];
                $changes[] = "access_level: {$currentPermissions['access_level']} → {$validatedData['access_level']}";
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();

                // Update station permissions
                DB::table('user_stations')->where('id', $validatedData['assignment_id'])->update($updateData);

                // Get station name for logging
                $stationName = DB::table('stations')
                    ->join('user_stations', 'stations.id', '=', 'user_stations.station_id')
                    ->where('user_stations.id', $validatedData['assignment_id'])
                    ->value('stations.station_name');

                // Log the change with auto-approval
                $changeDescription = "Station permissions updated for {$stationName}: " . implode(', ', $changes) . ' | Reason: ' . $validatedData['change_reason'];
                $this->logPermissionAction('UPDATE', 'user_stations', $validatedData['assignment_id'], $changeDescription, 'AUTO_APPROVED');

                return response()->json([
                    'success' => true,
                    'message' => '✔ Station Permissions Updated — Auto-Approved by Role',
                    'changes' => $changes
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes detected',
                    'changes' => []
                ]);
            }
        });
    }

    public function getCurrentStationPermissions($assignmentId)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['user_stations', 'stations']);

        // STEP 2: MANDATORY PERMISSION CHECK
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // STEP 3: GET ASSIGNMENT DATA - EXACT SCHEMA COLUMNS
        $assignment = DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->select([
                'user_stations.id',
                'user_stations.station_id',
                'user_stations.can_enter_readings',
                'user_stations.can_approve_deliveries',
                'user_stations.can_modify_prices',
                'user_stations.can_approve_variances',
                'user_stations.can_handle_cash',
                'user_stations.can_open_station',
                'user_stations.can_close_station',
                'user_stations.access_level',
                'stations.station_name',
                'stations.station_code'
            ])
            ->where('user_stations.id', $assignmentId)
            ->where('user_stations.is_active', 1)
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Assignment not found'], 404);
        }

        return response()->json([
            'success' => true,
            'assignment_info' => [
                'id' => $assignment->id,
                'station_name' => $assignment->station_name,
                'station_code' => $assignment->station_code
            ],
            'permissions' => [
                'can_enter_readings' => (bool) $assignment->can_enter_readings,
                'can_approve_deliveries' => (bool) $assignment->can_approve_deliveries,
                'can_modify_prices' => (bool) $assignment->can_modify_prices,
                'can_approve_variances' => (bool) $assignment->can_approve_variances,
                'can_handle_cash' => (bool) $assignment->can_handle_cash,
                'can_open_station' => (bool) $assignment->can_open_station,
                'can_close_station' => (bool) $assignment->can_close_station,
                'access_level' => $assignment->access_level ?? 'DATA_ENTRY'
            ]
        ]);
    }

    // MANDATORY SCHEMA VERIFICATION METHOD
    private function verifyRequiredTables(array $tables)
    {
        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                throw new \Exception("Required table '{$table}' not found in database schema");
            }
        }
    }

    // MANDATORY AUDIT LOGGING WITH EXACT COLUMN NAMES FROM AUDIT_LOGS TABLE
    private function logPermissionAction($action, $tableName, $recordId, $description, $approvalStatus = null)
    {
        $currentUser = auth()->user();

        // Get station context if applicable
        $stationId = null;
        if ($tableName === 'user_stations' && $recordId) {
            $stationId = DB::table('user_stations')->where('id', $recordId)->value('station_id');
        }

        DB::table('audit_logs')->insert([
            'user_id' => $currentUser->id,
            'station_id' => $stationId,
            'action_type' => strtoupper($action),
            'action_category' => 'SECURITY',
            'table_name' => $tableName,
            'record_id' => $recordId,
            'change_reason' => $description,
            'new_value_text' => $approvalStatus ? "STATUS: {$approvalStatus} - {$description}" : $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'hash_data' => json_encode([
                'action' => $action,
                'table' => $tableName,
                'record' => $recordId,
                'user_role' => $currentUser->role,
                'description' => $description
            ]),
            'hash_current' => hash('sha256', $action . $tableName . $recordId . $currentUser->id . now()),
            'hash_algorithm' => 'SHA256',
            'risk_level' => 'HIGH',
            'compliance_category' => 'SECURITY'
        ]);
    }
}
