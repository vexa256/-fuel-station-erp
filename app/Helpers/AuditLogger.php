<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log(array $params)
    {
        $timestamp = now();

        // Build hash data payload
        $hashData = json_encode([
            'user_id' => $params['user_id'] ?? null,
            'session_id' => $params['session_id'] ?? session()->getId(),
            'action_type' => $params['action_type'],
            'action_category' => $params['action_category'],
            'table_name' => $params['table_name'],
            'record_id' => $params['record_id'] ?? null,
            'old_value' => $params['old_value'] ?? null,
            'new_value' => $params['new_value'] ?? null,
            'change_reason' => $params['change_reason'] ?? null,
            'ip_address' => $params['ip_address'] ?? request()->ip(),
            'user_agent' => $params['user_agent'] ?? request()->userAgent(),
            'timestamp' => $timestamp->toISOString()
        ]);

        // Generate hash
        $hashString = implode('|', [
            $params['user_id'] ?? 'anonymous',
            $params['action_type'],
            $params['table_name'],
            $params['record_id'] ?? '',
            $timestamp->timestamp
        ]);

        $insertData = [
            'user_id' => $params['user_id'] ?? null,
            'session_id' => $params['session_id'] ?? session()->getId(),
            'action_type' => $params['action_type'],
            'action_category' => $params['action_category'],
            'table_name' => $params['table_name'],
            'record_id' => $params['record_id'] ?? null,
            'field_name' => $params['field_name'] ?? null,
            'old_value_text' => $params['old_value_text'] ?? null,
            'new_value_text' => $params['new_value_text'] ?? null,
            'old_value_numeric' => $params['old_value_numeric'] ?? null,
            'new_value_numeric' => $params['new_value_numeric'] ?? null,
            'change_reason' => $params['change_reason'] ?? null,
            'business_justification' => $params['business_justification'] ?? null,
            'ip_address' => $params['ip_address'] ?? request()->ip(),
            'user_agent' => $params['user_agent'] ?? request()->userAgent(),
            'request_method' => $params['request_method'] ?? request()->method(),
            'request_url' => $params['request_url'] ?? request()->fullUrl(),
            'timestamp' => $timestamp,
            'hash_data' => $hashData, //  Always included
            'hash_current' => hash('sha256', $hashString),
            'hash_algorithm' => 'SHA256',
            'risk_level' => $params['risk_level'] ?? 'MEDIUM',
            'sensitivity_level' => $params['sensitivity_level'] ?? 'INTERNAL',
            'compliance_category' => $params['compliance_category'] ?? 'OPERATIONAL',
            'system_generated' => $params['system_generated'] ?? 0,
            'batch_operation' => $params['batch_operation'] ?? 0,
            'error_occurred' => $params['error_occurred'] ?? 0
        ];

        // Remove null values to avoid issues
        $insertData = array_filter($insertData, function($value) {
            return $value !== null;
        });

        return DB::table('audit_logs')->insert($insertData);
    }

    public static function logUserAction($userId, $actionType, $tableName, $recordId = null, $data = [], Request $request = null)
    {
        return self::log([
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_category' => 'DATA_ENTRY',
            'table_name' => $tableName,
            'record_id' => $recordId,
            'new_value_text' => json_encode($data),
            'change_reason' => "User {$actionType} on {$tableName}",
            'ip_address' => $request ? $request->ip() : request()->ip(),
            'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
            'risk_level' => 'LOW',
            'compliance_category' => 'OPERATIONAL'
        ]);
    }

    public static function logSecurityEvent($userId, $event, $riskLevel = 'HIGH', Request $request = null)
    {
        return self::log([
            'user_id' => $userId,
            'action_type' => $event,
            'action_category' => 'SECURITY',
            'table_name' => 'users',
            'record_id' => $userId,
            'change_reason' => "Security event: {$event}",
            'ip_address' => $request ? $request->ip() : request()->ip(),
            'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
            'risk_level' => $riskLevel,
            'compliance_category' => 'SECURITY'
        ]);
    }
}
