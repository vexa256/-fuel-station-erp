<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\ReconciliationService;
use App\Services\AuditService;
use Exception;


class ReconciliationController extends Controller
{
    private ReconciliationService $reconciliationService;
    private AuditService $auditService;

    public function __construct(ReconciliationService $reconciliationService, AuditService $auditService)
    {
        $this->reconciliationService = $reconciliationService;
        $this->auditService = $auditService;
    }

    /**
     * 🔥 CORRECTED: Daily dashboard with PURE service delegation
     */
    public function daily(Request $request, $stationId = null, $date = null)
    {
        try {
            // STEP 1: PERMISSION CHECK WITH DATABASE VALIDATION
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermissionFromDatabase('can_access_financial_data')) {
                return redirect()->back()->with('error', 'Insufficient permissions for reconciliation access');
            }

            // STEP 2: PARAMETER VALIDATION
            $reconciliationDate = $date ?
                Carbon::parse($date)->toDateString() :
                Carbon::now()->toDateString();

            $userStations = $this->getUserAccessibleStations(auth()->id(), $isAutoApproved);

            if ($stationId) {
                if (!in_array($stationId, $userStations)) {
                    return redirect()->back()->with('error', 'Access denied to this station');
                }
                $targetStations = [$stationId];
            } else {
                $targetStations = $userStations;
            }

            // 🔥 CORRECTED: PURE SERVICE DELEGATION - ReconciliationService handles ALL automation
            $dashboardData = $this->reconciliationService->buildCompleteDashboardData(
                $targetStations,
                $reconciliationDate,
                $isAutoApproved
            );

            // STEP 4: AUDIT LOGGING (controller responsibility)
            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $stationId,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'reconciliations',
                'record_id' => null,
                'field_name' => 'dashboard_access',
                'old_values' => null,
                'new_values' => json_encode([
                    'stations_accessed' => $targetStations,
                    'reconciliation_date' => $reconciliationDate,
                    'compliance_score' => $dashboardData['complianceMetrics']['compliance_percentage'] ?? 0
                ]),
                'change_reason' => 'Reconciliation dashboard accessed',
                'business_justification' => 'Daily fuel inventory monitoring',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return view('reconciliation.daily', [
                'stationSummary' => $dashboardData['stationSummary'],
                'complianceMetrics' => $dashboardData['complianceMetrics'],
                'systemViolations' => $dashboardData['systemViolations'],
                'pendingVariances' => $dashboardData['pendingVariances'],
                'automationStatus' => $dashboardData['automationStatus'],
                'reconciliationDate' => $reconciliationDate,
                'targetStations' => $targetStations,
                'isAutoApproved' => $isAutoApproved,
                'fifoHealthStatus' => $dashboardData['fifoHealthStatus']
            ]);

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'error_message' => $e->getMessage(),
                'error_context' => 'Dashboard loading failed',
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Dashboard loading failed: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 CORRECTED: Execute reconciliation with PURE service delegation
     */
    public function execute(Request $request)
    {
        DB::beginTransaction();

        try {
            // STEP 1: PERMISSION CHECK
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermissionFromDatabase('can_approve_variances')) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            // STEP 2: STRICT INPUT VALIDATION - EXACT SCHEMA COMPLIANCE
            $validatedData = $request->validate([
                'station_id' => 'required|integer|exists:stations,id',
                'reconciliation_date' => 'required|date|date_format:Y-m-d',
                'reconciliation_type' => ['required', Rule::in(['DAILY_MORNING', 'DAILY_EVENING', 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'ANNUAL', 'AUDIT', 'SPOT_CHECK'])],
                'reconciliation_scope' => ['required', Rule::in(['SINGLE_TANK', 'ALL_TANKS', 'SINGLE_PRODUCT', 'ALL_PRODUCTS', 'FULL_STATION'])],
                'tank_id' => 'nullable|integer|exists:tanks,id'
            ]);

            // STEP 3: VERIFY STATION ACCESS
            $userStations = $this->getUserAccessibleStations(auth()->id(), $isAutoApproved);
            if (!in_array($validatedData['station_id'], $userStations)) {
                return response()->json(['error' => 'Access denied to this station'], 403);
            }

            // 🔥 CORRECTED: PURE SERVICE DELEGATION - ReconciliationService handles ALL automation
            // Service includes: stored procedures, trigger validation, automation checks, baseline validation
            $reconciliationResult = $this->reconciliationService->processCompleteStationReconciliation(
                $validatedData['station_id'],
                $validatedData['reconciliation_date']
            );

            // STEP 5: CEO/SYSTEM_ADMIN AUTO-APPROVAL WITH CORRECT SCHEMA
            if ($isAutoApproved) {
                // 🔥 CORRECTED: Use correct reconciliations table fields
                DB::table('reconciliations')
                    ->where('id', $reconciliationResult['station_reconciliation_id'])
                    ->update([
                        'approved_by' => auth()->id(),  // ✅ This field exists
                        'updated_at' => now()
                    ]);

                $this->auditService->logAutoApproval([
                    'user_id' => auth()->id(),
                    'table_name' => 'reconciliations',
                    'record_id' => $reconciliationResult['station_reconciliation_id'],
                    'field_name' => 'approved_by',
                    'new_values' => json_encode(['approved_by' => auth()->id()]),
                    'change_reason' => "Auto-approved by {$currentUserRole}",
                    'business_justification' => 'Executive override with complete system validation',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            // STEP 6: AUDIT LOGGING
            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $validatedData['station_id'],
                'action_type' => 'CREATE',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'reconciliations',
                'record_id' => $reconciliationResult['station_reconciliation_id'],
                'field_name' => 'reconciliation_execution',
                'old_values' => null,
                'new_values' => json_encode($validatedData),
                'change_reason' => 'Station reconciliation executed',
                'business_justification' => 'Daily fuel inventory reconciliation with automation',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isAutoApproved ?
                    '✅ Reconciliation completed and auto-approved' :
                    '📋 Reconciliation completed — CEO approval required',
                'data' => [
                    'reconciliation_id' => $reconciliationResult['station_reconciliation_id'],
                    'station_summary' => $reconciliationResult['station_summary'],
                    'overall_status' => $reconciliationResult['overall_status'],
                    'processing_completed_at' => $reconciliationResult['processing_completed_at']
                ],
                'auto_approved' => $isAutoApproved,
                'approved_by_role' => $currentUserRole
            ]);

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'error_message' => $e->getMessage(),
                'error_context' => 'Reconciliation execution failed',
                'request_data' => $validatedData ?? []
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Reconciliation execution failed: ' . $e->getMessage(),
                'details' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * 🔥 CORRECTED: Validate baselines with PURE service delegation
     */
    public function validateBaselines(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'station_id' => 'required|integer|exists:stations,id',
                'reconciliation_date' => 'required|date|date_format:Y-m-d'
            ]);

            // PERMISSION CHECK
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermissionFromDatabase('can_access_financial_data')) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            // VERIFY STATION ACCESS
            $userStations = $this->getUserAccessibleStations(auth()->id(), $isAutoApproved);
            if (!in_array($validatedData['station_id'], $userStations)) {
                return response()->json(['error' => 'Access denied to this station'], 403);
            }

            // 🔥 CORRECTED: PURE SERVICE DELEGATION - ALL automation handled by service
            $baselineValidation = $this->reconciliationService->validateMandatoryBaselines(
                $validatedData['station_id'],
                $validatedData['reconciliation_date']
            );

            // AUDIT LOGGING
            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $validatedData['station_id'],
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'reconciliations',
                'field_name' => 'baseline_validation',
                'new_values' => json_encode($baselineValidation),
                'change_reason' => 'Baseline validation executed',
                'business_justification' => 'Pre-reconciliation baseline compliance verification',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'data' => $baselineValidation
            ]);

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'error_message' => $e->getMessage(),
                'error_context' => 'Baseline validation failed'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Baseline validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 CORRECTED: Show reconciliation with service delegation
     */
    public function show($id)
    {
        try {
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermissionFromDatabase('can_access_financial_data')) {
                return redirect()->back()->with('error', 'Insufficient permissions');
            }

            // 🔥 CORRECTED: Service handles all data retrieval
            $reconciliationDetails = $this->reconciliationService->getReconciliationCompleteDetails($id);

            $userStations = $this->getUserAccessibleStations(auth()->id(), $isAutoApproved);
            if (!in_array($reconciliationDetails['reconciliation']->station_id, $userStations)) {
                return redirect()->back()->with('error', 'Access denied to this reconciliation');
            }

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $reconciliationDetails['reconciliation']->station_id,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'reconciliations',
                'record_id' => $id,
                'field_name' => 'reconciliation_view',
                'change_reason' => 'Reconciliation details accessed',
                'business_justification' => 'Reconciliation review and audit verification',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('reconciliation.show', [
                'reconciliation' => $reconciliationDetails['reconciliation'],
                'variances' => $reconciliationDetails['variances'],
                'tankResults' => $reconciliationDetails['tankResults'],
                'auditTrail' => $reconciliationDetails['auditTrail'],
                'automationStatus' => $reconciliationDetails['automationStatus'],
                'fifoValidation' => $reconciliationDetails['fifoValidation'],
                'isAutoApproved' => $isAutoApproved
            ]);

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'record_id' => $id,
                'error_message' => $e->getMessage(),
                'error_context' => 'Reconciliation details retrieval failed'
            ]);

            return redirect()->back()->with('error', 'Failed to load reconciliation details: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 CORRECTED: Approve reconciliation with correct schema fields
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved) {
                return response()->json(['error' => 'Only CEO and System Admin can approve reconciliations'], 403);
            }

            $validatedData = $request->validate([
                'approval_notes' => 'nullable|string|max:1000',
                'corrective_actions' => 'nullable|string|max:1000'
            ]);

            // 🔥 CORRECTED: Service handles approval logic
            $approvalResult = $this->reconciliationService->approveReconciliationWithCompleteValidation(
                $id,
                auth()->id(),
                $validatedData
            );

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $approvalResult['station_id'],
                'action_type' => 'UPDATE',
                'action_category' => 'APPROVAL',
                'table_name' => 'reconciliations',
                'record_id' => $id,
                'field_name' => 'approved_by',  // ✅ Correct field name
                'old_values' => json_encode(['approved_by' => null]),
                'new_values' => json_encode(['approved_by' => auth()->id()]),
                'change_reason' => 'Reconciliation approved by authorized role',
                'business_justification' => "Reconciliation approved by {$currentUserRole}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Reconciliation approved successfully',
                'data' => $approvalResult
            ]);

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'record_id' => $id,
                'error_message' => $e->getMessage(),
                'error_context' => 'Reconciliation approval failed'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 CORRECTED: Index with service delegation
     */
    public function index(Request $request)
    {
        try {
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermissionFromDatabase('can_access_financial_data')) {
                return redirect()->back()->with('error', 'Insufficient permissions');
            }

            $validated = $request->validate([
                'station_id' => 'nullable|integer|exists:stations,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'status' => 'nullable|string|in:BALANCED,MINOR_VARIANCE,SIGNIFICANT_VARIANCE,CRITICAL_VARIANCE,INVESTIGATION_REQUIRED',
                'per_page' => 'nullable|integer|min:10|max:100'
            ]);

            $userStations = $this->getUserAccessibleStations(auth()->id(), $isAutoApproved);

            // 🔥 CORRECTED: Service handles all data retrieval and filtering
            $reconciliationsData = $this->reconciliationService->getFilteredReconciliationsWithCompleteData(
                $validated,
                $userStations,
                $isAutoApproved
            );

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'reconciliations',
                'field_name' => 'reconciliation_index',
                'new_values' => json_encode($validated),
                'change_reason' => 'Reconciliation index accessed with filters',
                'business_justification' => 'Reconciliation data review and analysis',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return view('reconciliation.index', [
                'reconciliations' => $reconciliationsData['reconciliations'],
                'filters' => $validated,
                'stations' => $reconciliationsData['accessible_stations'],
                'summaryMetrics' => $reconciliationsData['summary_metrics'],
                'isAutoApproved' => $isAutoApproved
            ]);

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'table_name' => 'reconciliations',
                'error_message' => $e->getMessage(),
                'error_context' => 'Reconciliation index loading failed'
            ]);

            return redirect()->back()->with('error', 'Failed to load reconciliations: ' . $e->getMessage());
        }
    }

    // ================================================================================
    // 🔥 CORRECTED HELPER METHODS (EXACT SCHEMA COMPLIANCE)
    // ================================================================================

    /**
     * 🔥 CORRECTED: Database-driven permission check using users table fields
     */
    private function hasPermissionFromDatabase(string $permission): bool
    {
        $user = auth()->user();

        // Use exact schema fields from users table
        switch ($permission) {
            case 'can_access_financial_data':
                return (bool) $user->can_access_financial_data;
            case 'can_approve_variances':
                return (bool) $user->can_approve_variances;
            case 'can_approve_purchases':
                return (bool) $user->can_approve_purchases;
            case 'can_modify_prices':
                return (bool) $user->can_modify_prices;
            case 'can_export_data':
                return (bool) $user->can_export_data;
            default:
                return false;
        }
    }

    /**
     * 🔥 CORRECTED: Get user accessible stations using exact schema
     */
    private function getUserAccessibleStations(int $userId, bool $isAutoApproved): array
    {
        if ($isAutoApproved) {
            // CEO/SYSTEM_ADMIN can access all active stations
            return DB::table('stations')
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();
        }

        // Get stations assigned to user via user_stations table (exact schema)
        return DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->where('user_stations.user_id', $userId)
            ->where('user_stations.is_active', 1)
            ->where('stations.is_active', 1)
            ->pluck('stations.id')
            ->toArray();
    }
}
