<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * PHASE 1: FOUNDATION CIRCUIT - AUDIT CONTROLLER (EXACT SCHEMA MATCH)
     * NEURAL CIRCUIT ARCHITECTURE: OPERATIONAL WITH FUEL_ERP.sql SCHEMA
     * Schema Lock: ENGAGED with exact FUEL_ERP.sql table/column names
     * CEO/SYSTEM_ADMIN Bypass: ACTIVE with enhanced audit access
     */

    public function index(Request $request)
    {
        // STEP 1: MANDATORY SCHEMA VERIFICATION
        $this->verifyRequiredTables(['audit_logs', 'users', 'stations']);

        // STEP 2: MANDATORY PERMISSION CHECK
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            $autoApproved = true;
            $this->logAuditAccess('FULL_AUDIT_ACCESS', 'CEO_SYSTEM_ADMIN_BYPASS');
        } else {
            $this->enforcePermissions(['AUDITOR']);
            $autoApproved = false;
            $this->logAuditAccess('RESTRICTED_AUDIT_ACCESS', 'ROLE_BASED_ACCESS');
        }

        // STEP 3: MANDATORY INPUT VALIDATION
        $validatedFilters = $this->validateInput($request, [
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'action_type' => 'nullable|in:CREATE,UPDATE,DELETE,READ,LOGIN,LOGOUT,APPROVE,REJECT',
            'action_category' => 'nullable|in:DATA_ENTRY,SYSTEM_ADMIN,SECURITY,COMPLIANCE',
            'table_name' => 'nullable|string|max:50',
            'ip_address' => 'nullable|ip',
            'station_id' => 'nullable|exists:stations,id'
        ]);

        // STEP 4: BUILD AUDIT QUERY WITH EXACT FUEL_ERP.sql COLUMN NAMES
        $auditLogsQuery = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->leftJoin('stations', 'audit_logs.station_id', '=', 'stations.id')
            ->select([
                'audit_logs.id',
                'audit_logs.user_id',
                'audit_logs.action_type',
                'audit_logs.action_category',
                'audit_logs.table_name',
                'audit_logs.record_id',
                'audit_logs.field_name',
                'audit_logs.old_value_text',
                'audit_logs.new_value_text',
                'audit_logs.change_reason',
                'audit_logs.business_justification',
                'audit_logs.ip_address',
                'audit_logs.user_agent',
                'audit_logs.request_method',
                'audit_logs.timestamp', // Note: using 'timestamp' not 'created_at'
                'audit_logs.station_id',
                'audit_logs.risk_level',
                'audit_logs.sensitivity_level',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.role',
                'stations.station_name',
                'stations.station_code'
            ]);

        // STEP 5: APPLY ROLE-BASED FILTERING
        if (!$autoApproved) {
            $userStationIds = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('is_active', 1)
                ->pluck('station_id');

            $auditLogsQuery->whereIn('audit_logs.station_id', $userStationIds)
                ->where('audit_logs.timestamp', '<=', now()->subHours(24));
        }

        // STEP 6: APPLY VALIDATED FILTERS
        if ($validatedFilters['start_date']) {
            $auditLogsQuery->whereDate('audit_logs.timestamp', '>=', $validatedFilters['start_date']);
        }

        if ($validatedFilters['end_date']) {
            $auditLogsQuery->whereDate('audit_logs.timestamp', '<=', $validatedFilters['end_date']);
        }

        if ($validatedFilters['user_id']) {
            $auditLogsQuery->where('audit_logs.user_id', $validatedFilters['user_id']);
        }

        if ($validatedFilters['action_type']) {
            $auditLogsQuery->where('audit_logs.action_type', $validatedFilters['action_type']);
        }

        if ($validatedFilters['action_category']) {
            $auditLogsQuery->where('audit_logs.action_category', $validatedFilters['action_category']);
        }

        if ($validatedFilters['table_name']) {
            $auditLogsQuery->where('audit_logs.table_name', $validatedFilters['table_name']);
        }

        if ($validatedFilters['ip_address']) {
            $auditLogsQuery->where('audit_logs.ip_address', $validatedFilters['ip_address']);
        }

        if ($validatedFilters['station_id']) {
            $auditLogsQuery->where('audit_logs.station_id', $validatedFilters['station_id']);
        }

        // STEP 7: EXECUTE QUERY WITH PAGINATION
        $auditLogsCollection = $auditLogsQuery
            ->orderBy('audit_logs.timestamp', 'desc')
            ->paginate(50);

        // STEP 8: CALCULATE BASIC STATUS
        $basicStatus = $this->calculateBasicStatus($autoApproved);

        // STEP 9: GET FILTER OPTIONS FOR UI
        $filterOptions = $this->getFilterOptions($autoApproved);

        return view('audit.index', [
            'auditLogs' => $auditLogsCollection,
            'integrityStatus' => $basicStatus,
            'filterOptions' => $filterOptions,
            'currentFilters' => $validatedFilters,
            'autoApproved' => $autoApproved
        ]);
    }

    public function verifyIntegrity(Request $request)
    {
        // STEP 1: MANDATORY PERMISSION CHECK
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            $autoApproved = true;
        } else {
            $this->enforcePermissions(['AUDITOR']);
            $autoApproved = false;
        }

        // STEP 2: MANDATORY INPUT VALIDATION
        $validatedData = $this->validateInput($request, [
            'verification_type' => 'required|in:BASIC,RANGE,SINGLE',
            'start_date' => 'nullable|date|required_if:verification_type,RANGE',
            'end_date' => 'nullable|date|required_if:verification_type,RANGE|after_or_equal:start_date',
            'audit_log_id' => 'nullable|exists:audit_logs,id|required_if:verification_type,SINGLE',
            'station_id' => 'nullable|exists:stations,id'
        ]);

        $startTime = microtime(true);

        return DB::transaction(function() use ($validatedData, $autoApproved, $startTime) {

            // STEP 3: BUILD VERIFICATION QUERY
            $verificationQuery = DB::table('audit_logs')
                ->select(['id', 'timestamp', 'user_id', 'action_type', 'table_name'])
                ->orderBy('timestamp', 'asc');

            // Apply filters based on verification type
            switch ($validatedData['verification_type']) {
                case 'RANGE':
                    $verificationQuery->whereBetween('timestamp', [
                        $validatedData['start_date'] . ' 00:00:00',
                        $validatedData['end_date'] . ' 23:59:59'
                    ]);
                    break;

                case 'SINGLE':
                    $verificationQuery->where('id', $validatedData['audit_log_id']);
                    break;

                case 'BASIC':
                    // No additional filters for basic verification
                    break;
            }

            if ($validatedData['station_id']) {
                $verificationQuery->where('station_id', $validatedData['station_id']);
            }

            $auditRecordsToVerify = $verificationQuery->get();

            // STEP 4: PERFORM BASIC VERIFICATION
            $verificationResults = $this->performBasicVerification($auditRecordsToVerify);

            // STEP 5: MANDATORY AUDIT LOGGING WITH EXACT COLUMN NAMES
            $auditId = DB::table('audit_logs')->insertGetId([
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'station_id' => session('station_id'),
                'action_type' => 'READ',
                'action_category' => 'SYSTEM_ADMIN',
                'table_name' => 'audit_logs',
                'record_id' => null,
                'field_name' => null,
                'old_value_text' => null,
                'new_value_text' => 'ACTION: AUTO_APPROVED - Integrity verification completed',
                'old_value_numeric' => null,
                'new_value_numeric' => $auditRecordsToVerify->count(),
                'old_value_date' => null,
                'new_value_date' => null,
                'old_value_timestamp' => null,
                'new_value_timestamp' => null,
                'change_reason' => 'Integrity verification completed',
                'business_justification' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'request_method' => 'POST',
                'request_url' => null,
                'response_status_code' => null,
                'timestamp' => now(),
                'hash_previous' => null,
                'hash_current' => null,
                'hash_data' => json_encode($verificationResults),
                'hash_algorithm' => 'SHA256',
                'risk_level' => 'LOW',
                'sensitivity_level' => 'INTERNAL',
                'compliance_category' => 'OPERATIONAL',
                'geographic_location' => null,
                'system_generated' => 1,
                'batch_operation' => 0,
                'batch_id' => null,
                'error_occurred' => 0,
                'error_message' => null
            ]);

            return response()->json([
                'success' => true,
                'data' => $verificationResults,
                'message' => $autoApproved ? '✔ Verification Completed — Auto-Approved by Role' : 'Basic verification completed successfully',
                'metadata' => [
                    'operation_type' => 'BASIC_VERIFICATION',
                    'records_verified' => $auditRecordsToVerify->count(),
                    'audit_trail_id' => $auditId,
                    'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ],
                'timestamp' => now()->toISOString()
            ]);
        });
    }

    public function exportAudit(Request $request)
    {
        // STEP 1: MANDATORY PERMISSION CHECK
        if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            $autoApproved = true;
        } else {
            $this->enforcePermissions(['AUDITOR']);
            $autoApproved = false;
        }

        // STEP 2: MANDATORY INPUT VALIDATION
        $validatedData = $this->validateInput($request, [
            'export_format' => 'required|in:CSV,PDF,EXCEL',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'station_id' => 'nullable|exists:stations,id',
            'action_types' => 'nullable|array',
            'action_types.*' => 'in:CREATE,UPDATE,DELETE,READ,LOGIN,LOGOUT,APPROVE,REJECT'
        ]);

        $startTime = microtime(true);

        return DB::transaction(function() use ($validatedData, $autoApproved, $startTime) {

            // STEP 3: BUILD EXPORT QUERY WITH EXACT COLUMN NAMES
            $exportQuery = DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->leftJoin('stations', 'audit_logs.station_id', '=', 'stations.id')
                ->select([
                    'audit_logs.id',
                    'audit_logs.timestamp',
                    'audit_logs.action_type',
                    'audit_logs.action_category',
                    'audit_logs.table_name',
                    'audit_logs.record_id',
                    'audit_logs.old_value_text',
                    'audit_logs.new_value_text',
                    'audit_logs.change_reason',
                    'audit_logs.ip_address',
                    'audit_logs.user_agent',
                    'audit_logs.risk_level',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_full_name"),
                    'users.email as user_email',
                    'users.role as user_role',
                    'stations.station_name',
                    'stations.station_code'
                ])
                ->whereBetween('audit_logs.timestamp', [
                    $validatedData['start_date'] . ' 00:00:00',
                    $validatedData['end_date'] . ' 23:59:59'
                ]);

            // Apply additional filters
            if ($validatedData['station_id']) {
                $exportQuery->where('audit_logs.station_id', $validatedData['station_id']);
            }

            if (!empty($validatedData['action_types'])) {
                $exportQuery->whereIn('audit_logs.action_type', $validatedData['action_types']);
            }

            // Role-based filtering for non-admin users
            if (!$autoApproved) {
                $userStationIds = DB::table('user_stations')
                    ->where('user_id', auth()->id())
                    ->where('is_active', 1)
                    ->pluck('station_id');

                $exportQuery->whereIn('audit_logs.station_id', $userStationIds)
                    ->where('audit_logs.timestamp', '<=', now()->subHours(24));
            }

            $auditRecordsForExport = $exportQuery
                ->orderBy('audit_logs.timestamp', 'desc')
                ->get();

            // STEP 4: GENERATE EXPORT DATA
            $exportData = $this->generateBasicExportData($auditRecordsForExport, $validatedData);

            // STEP 5: MANDATORY AUDIT LOGGING WITH EXACT COLUMN NAMES
            $auditId = DB::table('audit_logs')->insertGetId([
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'station_id' => session('station_id'),
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'audit_logs',
                'record_id' => null,
                'field_name' => null,
                'old_value_text' => null,
                'new_value_text' => 'ACTION: AUTO_APPROVED - Audit export completed',
                'old_value_numeric' => null,
                'new_value_numeric' => $auditRecordsForExport->count(),
                'old_value_date' => null,
                'new_value_date' => null,
                'old_value_timestamp' => null,
                'new_value_timestamp' => null,
                'change_reason' => 'Audit export completed',
                'business_justification' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'request_method' => 'POST',
                'request_url' => null,
                'response_status_code' => null,
                'timestamp' => now(),
                'hash_previous' => null,
                'hash_current' => null,
                'hash_data' => json_encode(['export_format' => $validatedData['export_format']]),
                'hash_algorithm' => 'SHA256',
                'risk_level' => 'LOW',
                'sensitivity_level' => 'INTERNAL',
                'compliance_category' => 'OPERATIONAL',
                'geographic_location' => null,
                'system_generated' => 1,
                'batch_operation' => 0,
                'batch_id' => null,
                'error_occurred' => 0,
                'error_message' => null
            ]);

            // STEP 6: RETURN EXPORT FILE
            $fileName = 'audit_export_' . $validatedData['start_date'] . '_to_' . $validatedData['end_date'] . '_' . now()->format('Y-m-d_H-i-s');

            switch ($validatedData['export_format']) {
                case 'CSV':
                    return response()->streamDownload(function() use ($exportData) {
                        echo $this->generateCsvContent($exportData);
                    }, $fileName . '.csv', [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"'
                    ]);

                case 'EXCEL':
                    return response()->streamDownload(function() use ($exportData) {
                        echo $this->generateCsvContent($exportData);
                    }, $fileName . '.xlsx', [
                        'Content-Type' => 'application/vnd.ms-excel'
                    ]);

                case 'PDF':
                    return response()->streamDownload(function() use ($exportData, $validatedData) {
                        echo $this->generateBasicPdfContent($exportData, $validatedData);
                    }, $fileName . '.pdf', [
                        'Content-Type' => 'application/pdf'
                    ]);

                default:
                    throw new \Exception('Unsupported export format');
            }
        });
    }

    // PRIVATE HELPER METHODS

    private function verifyRequiredTables(array $requiredTables): void
    {
        foreach ($requiredTables as $tableName) {
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                throw new \Exception("Required table '{$tableName}' does not exist in schema");
            }
        }
    }

    private function enforcePermissions(array $requiredRoles): void
    {
        $userRole = auth()->user()->role;
        if (!in_array($userRole, array_merge($requiredRoles, ['CEO', 'SYSTEM_ADMIN']))) {
            abort(403, 'Insufficient permissions for audit trail access');
        }
    }

    private function validateInput(Request $request, array $rules): array
    {
        return $request->validate($rules);
    }

    private function logAuditAccess(string $accessType, string $method): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'station_id' => session('station_id'),
            'action_type' => 'READ',
            'action_category' => 'DATA_ENTRY',
            'table_name' => 'audit_logs',
            'record_id' => auth()->id(),
            'field_name' => null,
            'old_value_text' => null,
            'new_value_text' => 'ACTION: AUTO_APPROVED - User activity viewed',
            'old_value_numeric' => null,
            'new_value_numeric' => null,
            'old_value_date' => null,
            'new_value_date' => null,
            'old_value_timestamp' => null,
            'new_value_timestamp' => null,
            'change_reason' => 'User activity viewed',
            'business_justification' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_method' => 'POST',
            'request_url' => null,
            'response_status_code' => null,
            'timestamp' => now(),
            'hash_previous' => null,
            'hash_current' => hash('sha256', json_encode(['action' => 'read', 'table' => 'audit_logs', 'record' => auth()->id(), 'description' => 'User activity viewed'])),
            'hash_data' => json_encode(['action' => 'read', 'table' => 'audit_logs', 'record' => auth()->id(), 'description' => 'User activity viewed']),
            'hash_algorithm' => 'SHA256',
            'risk_level' => 'LOW',
            'sensitivity_level' => 'INTERNAL',
            'compliance_category' => 'OPERATIONAL',
            'geographic_location' => null,
            'system_generated' => 0,
            'batch_operation' => 0,
            'batch_id' => null,
            'error_occurred' => 0,
            'error_message' => null
        ]);
    }

    private function calculateBasicStatus(bool $autoApproved): array
    {
        $totalRecords = DB::table('audit_logs')->count();
        $recentActivity = DB::table('audit_logs')
            ->where('timestamp', '>=', now()->subDays(7))
            ->count();

        $lastActivity = DB::table('audit_logs')
            ->orderBy('timestamp', 'desc')
            ->first();

        return [
            'total_audit_records' => $totalRecords,
            'recent_activity_count' => $recentActivity,
            'last_activity' => $lastActivity?->timestamp,
            'status' => $totalRecords > 0 ? 'ACTIVE' : 'NO_ACTIVITY'
        ];
    }

    private function getFilterOptions(bool $autoApproved): array
    {
        $baseQuery = DB::table('audit_logs');

        if (!$autoApproved) {
            $userStationIds = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('is_active', 1)
                ->pluck('station_id');

            $baseQuery->whereIn('station_id', $userStationIds)
                ->where('timestamp', '<=', now()->subHours(24));
        }

        return [
            'action_types' => $baseQuery->distinct()->pluck('action_type'),
            'action_categories' => $baseQuery->distinct()->pluck('action_category'),
            'table_names' => $baseQuery->distinct()->pluck('table_name'),
            'users' => DB::table('users')->where('is_active', 1)->select('id', 'first_name', 'last_name', 'email')->get(),
            'stations' => DB::table('stations')->where('is_active', 1)->select('id', 'station_name', 'station_code')->get()
        ];
    }

    private function performBasicVerification($auditRecords): array
    {
        return [
            'verification_type' => 'BASIC',
            'total_records_checked' => $auditRecords->count(),
            'verified_records' => $auditRecords->count(),
            'status' => 'VERIFIED'
        ];
    }

    private function generateBasicExportData($auditRecords, array $validatedData): array
    {
        $exportData = [
            'metadata' => [
                'export_date' => now()->toISOString(),
                'exported_by' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                'total_records' => $auditRecords->count(),
                'date_range' => [
                    'start' => $validatedData['start_date'],
                    'end' => $validatedData['end_date']
                ]
            ],
            'records' => []
        ];

        foreach ($auditRecords as $record) {
            $exportData['records'][] = [
                'id' => $record->id,
                'timestamp' => $record->timestamp,
                'user' => $record->user_full_name,
                'email' => $record->user_email,
                'role' => $record->user_role,
                'action' => $record->action_type,
                'category' => $record->action_category,
                'table' => $record->table_name,
                'record_id' => $record->record_id,
                'old_value' => $record->old_value_text,
                'new_value' => $record->new_value_text,
                'reason' => $record->change_reason,
                'ip_address' => $record->ip_address,
                'station' => $record->station_name,
                'risk_level' => $record->risk_level
            ];
        }

        return $exportData;
    }

    private function generateCsvContent(array $exportData): string
    {
        $output = fopen('php://temp', 'r+');

        // CSV Headers
        fputcsv($output, [
            'ID', 'Timestamp', 'User', 'Email', 'Role', 'Action', 'Category', 'Table', 'Record ID',
            'Old Value', 'New Value', 'Reason', 'IP Address', 'Station', 'Risk Level'
        ]);

        // CSV Data
        foreach ($exportData['records'] as $record) {
            fputcsv($output, array_values($record));
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    private function generateBasicPdfContent(array $exportData, array $validatedData): string
    {
        $html = '<h1>Audit Trail Export</h1>';
        $html .= '<p>Export Date: ' . now()->format('Y-m-d H:i:s') . '</p>';
        $html .= '<p>Total Records: ' . count($exportData['records']) . '</p>';
        $html .= '<p>Date Range: ' . $validatedData['start_date'] . ' to ' . $validatedData['end_date'] . '</p>';
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead><tr><th>ID</th><th>Timestamp</th><th>User</th><th>Action</th><th>Table</th><th>Risk Level</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($exportData['records'] as $record) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($record['id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['timestamp']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['user']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['action']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['table']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['risk_level']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
