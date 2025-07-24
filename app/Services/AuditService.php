<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AuditService
{

    public function logAction(array $data): void
    {
        try {
            // Validate required fields
            $this->validateAuditData($data);

            // Get previous hash for chain integrity
            $previousHash = $this->getPreviousHash(
                $data['user_id'] ?? null,
                $data['station_id'] ?? null
            );

            // Generate current timestamp with microseconds
            $currentTimestamp = now()->format('Y-m-d H:i:s.u');
            $microtime = microtime(true);

            // Build hash data for integrity
            $hashData = json_encode([
                'user_id' => $data['user_id'],
                'action_type' => $data['action_type'],
                'action_category' => $data['action_category'],
                'table_name' => $data['table_name'],
                'record_id' => $data['record_id'] ?? null,
                'old_values' => $data['old_values'] ?? null,
                'new_values' => $data['new_values'] ?? null,
                'change_reason' => $data['change_reason'] ?? '',
                'business_justification' => $data['business_justification'] ?? null,
                'timestamp' => $currentTimestamp,
                'microtime' => $microtime,
                'session_id' => session()->getId(),
                'request_id' => request()->header('X-Request-ID') ?? uniqid()
            ]);

            // Generate cryptographic hashes
            $hashCurrent = hash('sha256', $hashData . ($previousHash ?? ''));
            $hashDataOnly = hash('sha256', $hashData);

            // Extract numeric values if present
            $oldValueNumeric = $this->extractNumericValue($data['old_values'] ?? null);
            $newValueNumeric = $this->extractNumericValue($data['new_values'] ?? null);

            // Extract date values if present
            $oldValueDate = $this->extractDateValue($data['old_values'] ?? null);
            $newValueDate = $this->extractDateValue($data['new_values'] ?? null);

            // Extract timestamp values if present
            $oldValueTimestamp = $this->extractTimestampValue($data['old_values'] ?? null);
            $newValueTimestamp = $this->extractTimestampValue($data['new_values'] ?? null);

            // Determine risk and sensitivity levels
            $riskLevel = $this->calculateRiskLevel($data);
            $sensitivityLevel = $this->calculateSensitivityLevel($data);
            $complianceCategory = $this->determineComplianceCategory($data);

            // Insert audit log record with EXACT schema compliance
            DB::table('audit_logs')->insert([
                'user_id' => $data['user_id'],
                'session_id' => session()->getId(),
                'station_id' => $data['station_id'] ?? null,
                'action_type' => $data['action_type'],
                'action_category' => $data['action_category'],
                'table_name' => $data['table_name'],
                'record_id' => $data['record_id'] ?? null,
                'field_name' => $data['field_name'] ?? null,
                'old_value_text' => $this->sanitizeForStorage($data['old_values'] ?? null),
                'new_value_text' => $this->sanitizeForStorage($data['new_values'] ?? null),
                'old_value_numeric' => $oldValueNumeric,
                'new_value_numeric' => $newValueNumeric,
                'old_value_date' => $oldValueDate,
                'new_value_date' => $newValueDate,
                'old_value_timestamp' => $oldValueTimestamp,
                'new_value_timestamp' => $newValueTimestamp,
                'change_reason' => $data['change_reason'] ?? '',
                'business_justification' => $data['business_justification'] ?? null,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'request_method' => request()->method(),
                'request_url' => request()->fullUrl(),
                'response_status_code' => null, // Will be updated by middleware
                'hash_previous' => $previousHash,
                'hash_current' => $hashCurrent,
                'hash_data' => $hashDataOnly,
                'hash_algorithm' => 'SHA256',
                'risk_level' => $riskLevel,
                'sensitivity_level' => $sensitivityLevel,
                'compliance_category' => $complianceCategory,
                'geographic_location' => $this->getGeographicLocation($data['ip_address'] ?? request()->ip()),
                'system_generated' => $data['system_generated'] ?? false,
                'batch_operation' => $data['batch_operation'] ?? false,
                'batch_id' => $data['batch_id'] ?? null,
                'error_occurred' => false,
                'error_message' => null,
                'timestamp' => $currentTimestamp
            ]);

            // Log automation context if provided
            if (isset($data['automation_context']) && is_array($data['automation_context'])) {
                $this->logAutomationContext($hashCurrent, $data['automation_context']);
            }

        } catch (Exception $e) {
            // Error logging should not fail the main operation
            $this->logAuditError($e, $data);
        }
    }

    /**
     * 🔥 CRITICAL: Security violation logging
     */
    public function logSecurityViolation(array $data): void
    {
        $securityData = array_merge($data, [
            'action_type' => 'SECURITY_VIOLATION',
            'action_category' => 'SECURITY',
            'table_name' => 'security_violations',
            'change_reason' => 'Security violation detected',
            'risk_level' => 'CRITICAL',
            'sensitivity_level' => 'RESTRICTED',
            'compliance_category' => 'SECURITY'
        ]);

        $this->logAction($securityData);

        // Also create security alert
        $this->createSecurityAlert($data);
    }

    /**
     * 🔥 CRITICAL: Error logging with system context
     */
  public function logError(array $data): void
{
    $errorData = array_merge($data, [
        'action_type' => 'CREATE', // FIXED: Use valid action type instead of 'ERROR'
        'action_category' => 'SYSTEM',
        'table_name' => $data['table_name'] ?? 'system_errors', // Keep original table if provided
        'record_id' => null, // Error logs don't have specific record IDs
        'change_reason' => $data['change_reason'] ?? 'System error occurred',
        'business_justification' => 'Error logging for system monitoring and debugging',
        'risk_level' => $data['risk_level'] ?? 'HIGH',
        'sensitivity_level' => $data['sensitivity_level'] ?? 'INTERNAL',
        'compliance_category' => $data['compliance_category'] ?? 'OPERATIONAL',
        'system_generated' => true, // Error logs are system generated
        'old_values' => null,
        'new_values' => json_encode([
            'error_message' => $data['error_message'] ?? 'Unknown error',
            'error_context' => $data['error_context'] ?? '',
            'timestamp' => now()->toISOString()
        ])
    ]);

    try {
        $this->logAction($errorData);
    } catch (Exception $e) {
        // If audit logging fails, don't throw exception to prevent infinite loops
        // Log to system monitoring table directly as fallback
        $this->logToSystemMonitoring($data);
    }

    // Also log to system monitoring for redundancy
    $this->logToSystemMonitoring($data);
}

    /**
     * 🔥 CRITICAL: CEO/SYSTEM_ADMIN auto-approval logging
     */
    public function logAutoApproval(array $data): void
    {
        $approvalData = array_merge($data, [
            'action_type' => 'APPROVE',
            'action_category' => 'APPROVAL',
            'business_justification' => 'Auto-approved by role: ' . (auth()->user()->role ?? 'UNKNOWN'),
            'change_reason' => 'Automatic approval by elevated role',
            'risk_level' => 'MEDIUM',
            'sensitivity_level' => 'CONFIDENTIAL',
            'compliance_category' => 'REGULATORY'
        ]);

        $this->logAction($approvalData);
    }

    /**
     * 🔥 CRITICAL: Batch operation logging
     */
    public function logBatchOperation(string $batchId, array $operations): void
    {
        foreach ($operations as $operation) {
            $batchData = array_merge($operation, [
                'batch_operation' => true,
                'batch_id' => $batchId,
                'system_generated' => true,
                'change_reason' => 'Batch operation execution'
            ]);

            $this->logAction($batchData);
        }
    }

    /**
     * 🔥 CRITICAL: Get previous hash for chain integrity
     */
    private function getPreviousHash($userId = null, $stationId = null): ?string
    {
        try {
            $query = DB::table('audit_logs')
                ->select('hash_current')
                ->orderBy('timestamp', 'desc')
                ->orderBy('id', 'desc');

            // Chain by user if provided, otherwise global chain
            if ($userId) {
                $query->where('user_id', $userId);
            }

            // Also consider station-specific chains for data locality
            if ($stationId) {
                $query->where('station_id', $stationId);
            }

            $previous = $query->first();
            return $previous->hash_current ?? null;

        } catch (Exception $e) {
            // If hash chain retrieval fails, continue without previous hash
            return null;
        }
    }

    /**
     * 🔥 CRITICAL: Audit data validation
     */
    private function validateAuditData(array $data): void
    {
        $required = ['action_type', 'action_category', 'table_name', 'user_id'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Required audit field missing: {$field}");
            }
        }

        // Validate enum values against EXACT schema
        $validActionTypes = ['CEO_BYPASS','SYSTEM', 'CREATE', 'READ', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'APPROVE', 'REJECT', 'ESCALATE', 'EXPORT', 'IMPORT', 'BACKUP', 'RESTORE'];
        if (!in_array($data['action_type'], $validActionTypes)) {
            throw new Exception("Invalid action_type: {$data['action_type']}");
        }

        $validCategories = ['CEO_BYPASS','SYSTEM', 'DATA_ENTRY', 'APPROVAL', 'CONFIGURATION', 'SECURITY', 'REPORTING', 'MAINTENANCE', 'INVESTIGATION'];
        if (!in_array($data['action_category'], $validCategories)) {
            throw new Exception("Invalid action_category: {$data['action_category']}");
        }
    }

    /**
     * 🔥 CRITICAL: Extract numeric values from data
     */
    private function extractNumericValue($value): ?float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_numeric($item)) {
                        return (float)$item;
                    }
                }
            }
        }

        return null;
    }

    /**
     * 🔥 CRITICAL: Extract date values from data
     */
    private function extractDateValue($value): ?string
    {
        if ($value && is_string($value)) {
            try {
                $date = Carbon::parse($value);
                return $date->format('Y-m-d');
            } catch (Exception $e) {
                // Not a valid date
            }
        }

        return null;
    }

    /**
     * 🔥 CRITICAL: Extract timestamp values from data
     */
    private function extractTimestampValue($value): ?string
    {
        if ($value && is_string($value)) {
            try {
                $timestamp = Carbon::parse($value);
                return $timestamp->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Not a valid timestamp
            }
        }

        return null;
    }

    /**
     * 🔥 CRITICAL: Calculate risk level based on action
     */
    private function calculateRiskLevel(array $data): string
    {
        // CEO/SYSTEM_ADMIN actions
        if (isset($data['is_auto_approved']) && $data['is_auto_approved']) {
            return 'HIGH';
        }

        // Security-related actions
        if ($data['action_category'] === 'SECURITY') {
            return 'CRITICAL';
        }

        // Financial data modifications
        if (in_array($data['table_name'], ['deliveries', 'selling_prices', 'purchase_prices', 'supplier_invoices', 'supplier_payments'])) {
            return 'HIGH';
        }

        // Configuration changes
        if ($data['table_name'] === 'system_configurations') {
            return 'CRITICAL';
        }

        // User management
        if ($data['table_name'] === 'users') {
            return 'HIGH';
        }

        // Critical operational tables
        if (in_array($data['table_name'], ['readings', 'variances', 'tank_inventory_layers'])) {
            return 'MEDIUM';
        }

        // Data corrections
        if ($data['action_type'] === 'UPDATE' && isset($data['change_reason'])) {
            return 'MEDIUM';
        }

        return 'LOW';
    }

    /**
     * 🔥 CRITICAL: Calculate sensitivity level
     */
    private function calculateSensitivityLevel(array $data): string
    {
        // Security and audit data
        if (in_array($data['action_category'], ['SECURITY', 'INVESTIGATION'])) {
            return 'RESTRICTED';
        }

        // Financial and pricing data
        if (in_array($data['table_name'], ['selling_prices', 'purchase_prices', 'supplier_invoices', 'margin_analysis'])) {
            return 'CONFIDENTIAL';
        }

        // Operational data
        if (in_array($data['table_name'], ['readings', 'deliveries', 'variances', 'reconciliations'])) {
            return 'INTERNAL';
        }

        // Configuration and user data
        if (in_array($data['table_name'], ['system_configurations', 'users', 'user_stations'])) {
            return 'CONFIDENTIAL';
        }

        return 'INTERNAL';
    }

    /**
     * 🔥 CRITICAL: Determine compliance category
     */
    private function determineComplianceCategory(array $data): string
    {
        // Financial transactions and pricing
        if (in_array($data['table_name'], ['selling_prices', 'purchase_prices', 'supplier_invoices', 'supplier_payments', 'margin_analysis'])) {
            return 'FINANCIAL';
        }

        // Security and access control
        if (in_array($data['action_category'], ['SECURITY', 'LOGIN', 'LOGOUT']) || $data['table_name'] === 'users') {
            return 'SECURITY';
        }

        // Regulatory compliance (readings, variances, reconciliations)
        if (in_array($data['table_name'], ['readings', 'variances', 'reconciliations', 'audit_logs', 'approvals'])) {
            return 'REGULATORY';
        }

        // Operational processes
        if (in_array($data['table_name'], ['deliveries', 'tank_inventory_layers', 'batch_consumption', 'pumps', 'tanks'])) {
            return 'OPERATIONAL';
        }

        return 'NONE';
    }

    /**
     * 🔥 CRITICAL: Sanitize data for storage
     */
    private function sanitizeForStorage($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        // Truncate if too long (audit_logs.old_value_text is TEXT type)
        $stringValue = (string)$value;
        return strlen($stringValue) > 65535 ? substr($stringValue, 0, 65532) . '...' : $stringValue;
    }

    /**
     * 🔥 CRITICAL: Get geographic location
     */
    private function getGeographicLocation($ipAddress): ?string
    {
        // For production, integrate with IP geolocation service
        // For now, return basic location info
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return 'Local Development';
        }

        // Default to Uganda for fuel station context
        return 'Uganda';
    }

    /**
     * 🔥 CRITICAL: Log automation context
     */
    private function logAutomationContext(string $auditHash, array $context): void
    {
        try {
            DB::table('system_health_monitoring')->insert([
                'check_timestamp' => now(),
                'check_type' => 'AUDIT_AUTOMATION_CONTEXT',
                'check_status' => 'PASSED',
                'check_details' => json_encode($context),
                'affected_records' => 1,
                'execution_time_ms' => 0,
                'severity' => 'LOW',
                'auto_resolved' => true,
                'resolution_action' => 'Automation context logged for audit hash: ' . $auditHash
            ]);
        } catch (Exception $e) {
            // Silent failure for context logging
        }
    }

    /**
     * 🔥 CRITICAL: Create security alert
     */
    private function createSecurityAlert(array $data): void
    {
        try {
            DB::table('system_accuracy_alerts')->insert([
                'alert_type' => 'SECURITY_VIOLATION',
                'severity' => 'CRITICAL',
                'tank_id' => null,
                'layer_id' => null,
                'error_description' => json_encode($data),
                'current_values' => json_encode([
                    'user_id' => $data['user_id'] ?? null,
                    'ip_address' => $data['ip_address'] ?? request()->ip(),
                    'action_attempted' => $data['action'] ?? 'UNKNOWN'
                ]),
                'expected_values' => json_encode([
                    'authorized_access' => true,
                    'valid_permissions' => true
                ]),
                'variance_amount' => null,
                'detected_at' => now(),
                'resolved_at' => null,
                'resolved_by' => null
            ]);
        } catch (Exception $e) {
            // Silent failure for security alerts
        }
    }

    /**
     * 🔥 CRITICAL: Log to system monitoring
     */
    private function logToSystemMonitoring(array $data): void
    {
        try {
            DB::table('system_health_monitoring')->insert([
                'check_timestamp' => now(),
                'check_type' => 'AUDIT_ERROR',
                'check_status' => 'FAILED',
                'check_details' => json_encode([
                    'error_message' => $data['error_message'] ?? 'Unknown error',
                    'error_context' => $data['error_context'] ?? '',
                    'user_id' => $data['user_id'] ?? null,
                    'table_name' => $data['table_name'] ?? 'unknown'
                ]),
                'affected_records' => 1,
                'execution_time_ms' => 0,
                'severity' => 'HIGH',
                'auto_resolved' => false,
                'resolution_action' => null
            ]);
        } catch (Exception $e) {
            // Silent failure for system monitoring
        }
    }

    /**
     * 🔥 CRITICAL: Log audit errors without causing infinite loops
     */
    private function logAuditError(Exception $e, array $originalData): void
    {
        try {
            // Log to Laravel's default log file instead of database to avoid loops
            \Log::error('Audit Service Error', [
                'error_message' => $e->getMessage(),
                'original_data' => $originalData,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            // Also try to log to system monitoring if possible
            DB::table('system_health_monitoring')->insert([
                'check_timestamp' => now(),
                'check_type' => 'AUDIT_SERVICE_ERROR',
                'check_status' => 'FAILED',
                'check_details' => 'Audit logging failed: ' . $e->getMessage(),
                'affected_records' => 0,
                'execution_time_ms' => 0,
                'severity' => 'CRITICAL',
                'auto_resolved' => false,
                'resolution_action' => 'Manual intervention required for audit system'
            ]);
        } catch (Exception $secondaryError) {
            // Ultimate fallback - just log to file
            \Log::emergency('Critical Audit System Failure', [
                'primary_error' => $e->getMessage(),
                'secondary_error' => $secondaryError->getMessage(),
                'timestamp' => now()
            ]);
        }
    }

    /**
     * 🔥 UTILITY: Verify hash chain integrity
     */
    public function verifyHashChainIntegrity($userId = null, $limit = 100): array
    {
        try {
            $query = DB::table('audit_logs')
                ->select(['id', 'hash_previous', 'hash_current', 'hash_data', 'timestamp'])
                ->orderBy('timestamp', 'desc')
                ->orderBy('id', 'desc')
                ->limit($limit);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $records = $query->get();
            $brokenChains = [];
            $validChains = 0;

            foreach ($records as $index => $record) {
                if ($index < count($records) - 1) {
                    $nextRecord = $records[$index + 1];

                    if ($record->hash_previous !== $nextRecord->hash_current) {
                        $brokenChains[] = [
                            'record_id' => $record->id,
                            'expected_previous' => $nextRecord->hash_current,
                            'actual_previous' => $record->hash_previous,
                            'timestamp' => $record->timestamp
                        ];
                    } else {
                        $validChains++;
                    }
                }
            }

            return [
                'total_checked' => count($records),
                'valid_chains' => $validChains,
                'broken_chains' => count($brokenChains),
                'broken_chain_details' => $brokenChains,
                'integrity_percentage' => count($records) > 0 ? ($validChains / (count($records) - 1)) * 100 : 100,
                'verified_at' => now()
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Hash chain verification failed: ' . $e->getMessage(),
                'verified_at' => now()
            ];
        }
    }

    /**
     * 🔥 UTILITY: Get audit statistics
     */
    public function getAuditStatistics($dateFrom = null, $dateTo = null): array
    {
        try {
            $dateFrom = $dateFrom ?? Carbon::now()->subDays(30);
            $dateTo = $dateTo ?? Carbon::now();

            $stats = DB::table('audit_logs')
                ->selectRaw('
                    COUNT(*) as total_actions,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT station_id) as unique_stations,
                    COUNT(DISTINCT table_name) as unique_tables,
                    COUNT(CASE WHEN action_type = "CREATE" THEN 1 END) as creates,
                    COUNT(CASE WHEN action_type = "UPDATE" THEN 1 END) as updates,
                    COUNT(CASE WHEN action_type = "DELETE" THEN 1 END) as deletes,
                    COUNT(CASE WHEN risk_level = "CRITICAL" THEN 1 END) as critical_actions,
                    COUNT(CASE WHEN risk_level = "HIGH" THEN 1 END) as high_risk_actions,
                    COUNT(CASE WHEN error_occurred = 1 THEN 1 END) as error_count
                ')
                ->whereBetween('timestamp', [$dateFrom, $dateTo])
                ->first();

            return [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ],
                'statistics' => $stats,
                'generated_at' => now()
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Statistics generation failed: ' . $e->getMessage(),
                'generated_at' => now()
            ];
        }
    }
}
