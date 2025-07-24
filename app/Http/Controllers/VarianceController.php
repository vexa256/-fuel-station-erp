<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FIFOService;

class VarianceController extends Controller
{
    /**
     * EXACT VARIANCE STATUS from schema
     */
    private const VARIANCE_STATUS = ['PENDING', 'UNDER_INVESTIGATION', 'EXPLAINED', 'APPROVED', 'REJECTED', 'CLOSED'];

    /**
     * EXACT VARIANCE CATEGORIES from schema
     */
    private const VARIANCE_CATEGORIES = ['MINOR', 'MODERATE', 'SIGNIFICANT', 'CRITICAL'];

    /**
     * EXACT VARIANCE TYPES from schema
     */
    private const VARIANCE_TYPES = ['STOCK_DISCREPANCY', 'METER_MISMATCH', 'PRICE_ANOMALY', 'CONSUMPTION_PATTERN', 'DELIVERY_SHORTAGE', 'OVERAGE', 'SHORTAGE'];

    private function getFIFOService()
    {
        return new FIFOService();
    }

    /**
     * Variance management dashboard
     */
    public function index(Request $request)
    {
        // PERMISSION CHECK
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // BUILD VARIANCE QUERY with exact schema fields
        $query = DB::table('variances')
            ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->leftJoin('variance_investigations', 'variances.id', '=', 'variance_investigations.variance_id')
            ->select([
                'variances.id',
                'variances.variance_type',
                'variances.variance_category',
                'variances.calculated_variance_liters',
                'variances.calculated_variance_percentage',
                'variances.variance_direction',
                'variances.financial_impact_net',
                'variances.variance_status',
                'variances.escalation_level',
                'variances.risk_level',
                'variances.recommended_action',
                'variances.investigation_priority',
                'variances.pattern_detected',
                'variances.pattern_type',
                'variances.created_at',
                'tanks.tank_number',
                'stations.station_name',
                'variance_investigations.investigation_number',
                'variance_investigations.investigation_status'
            ]);

        // SEARCH FILTER
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('stations.station_name', 'LIKE', "%{$search}%")
                  ->orWhere('tanks.tank_number', 'LIKE', "%{$search}%")
                  ->orWhere('variance_investigations.investigation_number', 'LIKE', "%{$search}%");
            });
        }

        // STATUS FILTER
        if ($status = $request->get('status')) {
            if (in_array($status, self::VARIANCE_STATUS)) {
                $query->where('variances.variance_status', $status);
            }
        }

        // CATEGORY FILTER
        if ($category = $request->get('category')) {
            if (in_array($category, self::VARIANCE_CATEGORIES)) {
                $query->where('variances.variance_category', $category);
            }
        }

        // PRIORITY FILTER
        if ($request->has('high_priority')) {
            $query->whereIn('variances.investigation_priority', ['HIGH', 'URGENT']);
        }

        $variances = $query->orderBy('variances.created_at', 'desc')
                          ->paginate(25);

        // DASHBOARD STATS
        $stats = [
            'total_variances' => DB::table('variances')->count(),
            'pending_variances' => DB::table('variances')->where('variance_status', 'PENDING')->count(),
            'critical_variances' => DB::table('variances')->where('variance_category', 'CRITICAL')->count(),
            'under_investigation' => DB::table('variances')->where('variance_status', 'UNDER_INVESTIGATION')->count(),
            'high_priority' => DB::table('variances')->where('investigation_priority', 'HIGH')->count(),
            'patterns_detected' => DB::table('variances')->where('pattern_detected', 1)->count()
        ];

        return view('variances.index', compact('variances', 'stats'));
    }

    /**
     * Detailed variance analysis
     */
    public function analyze($id)
    {
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // SHOW VARIANCE WITH AUTHORITY CHECK
        $variance = DB::table('variances')
            ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->join('dip_readings', 'variances.reading_id', '=', 'dip_readings.id')
            ->leftJoin('daily_reconciliations', 'variances.daily_reconciliation_id', '=', 'daily_reconciliations.id')
            ->where('variances.id', $id)
            ->select([
                'variances.*',
                'tanks.tank_number',
                'tanks.capacity_liters',
                'stations.station_name',
                'dip_readings.volume_liters as physical_stock',
                'dip_readings.reading_date',
                'dip_readings.reading_time',
                'daily_reconciliations.meter_sales_liters',
                'daily_reconciliations.book_stock_liters'
            ])
            ->first();

        if (!$variance) {
            return redirect()->route('variances.index')->with('error', 'Variance not found');
        }

        // CHECK RESOLUTION AUTHORITY FOR UI DISPLAY
        $resolutionAuthority = $this->canResolveVariance(
            $variance->calculated_variance_percentage,
            $variance->escalation_level
        );

        // GET FIFO LAYER ANALYSIS
        $fifoAnalysis = $this->analyzeFIFOLayers($variance->tank_id);

        // GET HISTORICAL PATTERN
        $historicalPattern = $this->getHistoricalPattern($variance->tank_id);

        // GET RECENT ACTIVITIES
        $recentActivities = $this->getRecentActivities($variance->tank_id);

        return view('variances.analyze', compact(
            'variance',
            'fifoAnalysis',
            'historicalPattern',
            'recentActivities',
            'resolutionAuthority'
        ));
    }

    /**
     * Investigation workflow initiation
     */
    public function investigate($id)
    {
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        DB::beginTransaction();

        try {
            // GET VARIANCE
            $variance = DB::table('variances')->find($id);
            if (!$variance) {
                return response()->json(['success' => false, 'error' => 'Variance not found'], 404);
            }

            // CHECK IF ALREADY UNDER INVESTIGATION
            $existingInvestigation = DB::table('variance_investigations')
                ->where('variance_id', $id)
                ->first();

            if ($existingInvestigation) {
                return response()->json([
                    'success' => false,
                    'error' => 'Investigation already exists',
                    'investigation_number' => $existingInvestigation->investigation_number
                ], 422);
            }

            // GENERATE INVESTIGATION NUMBER
            $investigationNumber = 'INV-' . date('Ym') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);

            // CREATE INVESTIGATION RECORD
            $investigationId = DB::table('variance_investigations')->insertGetId([
                'variance_id' => $id,
                'investigation_number' => $investigationNumber,
                'investigation_type' => 'ROUTINE',
                'investigation_scope' => 'SINGLE_INCIDENT',
                'lead_investigator_user_id' => auth()->id(),
                'investigation_started_at' => now(),
                'estimated_completion_date' => now()->addDays(7)->toDateString(),
                'investigation_status' => 'ACTIVE',
                'evidence_items_count' => 0,
                'witness_statements_count' => 0,
                'total_cost_investigation' => 0.00
            ]);

            // UPDATE VARIANCE STATUS
            DB::table('variances')
                ->where('id', $id)
                ->update([
                    'variance_status' => 'UNDER_INVESTIGATION',
                    'updated_at' => now()
                ]);

            // LOG ACTION
            $this->logAction('INVESTIGATION_INITIATED', $investigationId, json_encode([
                'variance_id' => $id,
                'investigation_number' => $investigationNumber,
                'lead_investigator' => auth()->id()
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Investigation initiated successfully',
                'investigation_number' => $investigationNumber,
                'investigation_id' => $investigationId
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Investigation creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manager variance explanation
     */
    public function explain($id, Request $request)
    {
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        if ($request->isMethod('POST')) {
            // HANDLE EXPLANATION SUBMISSION
            $request->validate([
                'manager_explanation' => 'required|string|min:10|max:2000',
                'variance_reason_code' => 'required|in:MEASUREMENT_ERROR,TEMPERATURE_CHANGE,EQUIPMENT_MALFUNCTION,CALIBRATION_DRIFT,THEFT_SUSPECTED,EVAPORATION,DELIVERY_DISCREPANCY,CALCULATION_ERROR,DATA_ENTRY_ERROR,PUMP_MALFUNCTION,LEAK_SUSPECTED,UNKNOWN',
                'corrective_action_taken' => 'nullable|string|max:1000',
                'prevention_measures' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            try {
                // UPDATE VARIANCE WITH EXPLANATION
                DB::table('variances')
                    ->where('id', $id)
                    ->update([
                        'manager_explanation' => $request->manager_explanation,
                        'variance_reason_code' => $request->variance_reason_code,
                        'corrective_action_taken' => $request->corrective_action_taken,
                        'prevention_measures' => $request->prevention_measures,
                        'variance_status' => 'EXPLAINED',
                        'updated_at' => now()
                    ]);

                // LOG ACTION
                $this->logAction('VARIANCE_EXPLAINED', $id, json_encode([
                    'reason_code' => $request->variance_reason_code,
                    'explanation_length' => strlen($request->manager_explanation),
                    'explained_by' => auth()->id()
                ]));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Variance explanation saved successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save explanation: ' . $e->getMessage()
                ], 500);
            }
        }

        // GET VARIANCE FOR EXPLANATION FORM
        $variance = DB::table('variances')
            ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->where('variances.id', $id)
            ->select(['variances.*', 'tanks.tank_number', 'stations.station_name'])
            ->first();

        if (!$variance) {
            return redirect()->route('variances.index')->with('error', 'Variance not found');
        }

        return view('variances.explain', compact('variance'));
    }

    /**
     * Variance pattern analysis for station
     */
    public function patterns($stationId)
    {
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // GET STATION INFO
        $station = DB::table('stations')->find($stationId);
        if (!$station) {
            return redirect()->route('variances.index')->with('error', 'Station not found');
        }

        // PATTERN ANALYSIS - Last 30 days
        $patterns = DB::table('variances')
            ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
            ->where('tanks.station_id', $stationId)
            ->where('variances.created_at', '>=', now()->subDays(30))
            ->select([
                'variances.pattern_type',
                'variances.variance_category',
                'variances.variance_direction',
                'tanks.tank_number',
                DB::raw('COUNT(*) as frequency'),
                DB::raw('AVG(ABS(variances.calculated_variance_percentage)) as avg_variance_percentage'),
                DB::raw('SUM(ABS(variances.financial_impact_net)) as total_financial_impact')
            ])
            ->groupBy(['variances.pattern_type', 'variances.variance_category', 'variances.variance_direction', 'tanks.tank_number'])
            ->orderBy('frequency', 'desc')
            ->get();

        // TREND ANALYSIS
        $trends = DB::table('variances')
            ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
            ->where('tanks.station_id', $stationId)
            ->where('variances.created_at', '>=', now()->subDays(30))
            ->select([
                DB::raw('DATE(variances.created_at) as variance_date'),
                'tanks.tank_number',
                DB::raw('COUNT(*) as daily_count'),
                DB::raw('AVG(variances.calculated_variance_percentage) as avg_percentage')
            ])
            ->groupBy(['variance_date', 'tanks.tank_number'])
            ->orderBy('variance_date', 'desc')
            ->get();

        // SUMMARY STATISTICS
        $summary = [
            'total_variances_30_days' => DB::table('variances')
                ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
                ->where('tanks.station_id', $stationId)
                ->where('variances.created_at', '>=', now()->subDays(30))
                ->count(),
            'patterns_detected' => DB::table('variances')
                ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
                ->where('tanks.station_id', $stationId)
                ->where('variances.pattern_detected', 1)
                ->where('variances.created_at', '>=', now()->subDays(30))
                ->count(),
            'critical_variances' => DB::table('variances')
                ->join('tanks', 'variances.tank_id', '=', 'tanks.id')
                ->where('tanks.station_id', $stationId)
                ->where('variances.variance_category', 'CRITICAL')
                ->where('variances.created_at', '>=', now()->subDays(30))
                ->count()
        ];

        return view('variances.patterns', compact('station', 'patterns', 'trends', 'summary'));
    }

    /**
     * ENHANCED VARIANCE RESOLUTION WITH ROLE-BASED THRESHOLDS
     * CEO/SYSTEM_ADMIN: Can resolve any variance
     * STATION_MANAGER: Limited to MINOR/MODERATE variances only
     */
    public function resolve(Request $request, $id)
    {
        if (!$this->hasAccess()) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        $request->validate([
            'resolution_action' => 'required|in:APPROVE,REJECT,ADJUST_FIFO,OVERRIDE',
            'resolution_reason' => 'required|string|min:10|max:500',
            'adjustment_quantity_liters' => 'nullable|numeric'
        ]);

        DB::beginTransaction();

        try {
            // GET VARIANCE WITH ESCALATION INFO
            $variance = DB::table('variances')
                ->where('id', $id)
                ->select([
                    'id', 'calculated_variance_percentage', 'escalation_level',
                    'variance_category', 'variance_status', 'tank_id',
                    'calculated_variance_liters', 'financial_impact_net'
                ])
                ->first();

            if (!$variance) {
                return response()->json(['success' => false, 'error' => 'Variance not found'], 404);
            }

            // CHECK RESOLUTION AUTHORITY
            $resolutionCheck = $this->canResolveVariance(
                $variance->calculated_variance_percentage,
                $variance->escalation_level
            );

            if (!$resolutionCheck['can_resolve']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient authority to resolve this variance',
                    'details' => [
                        'reason' => $resolutionCheck['reason'],
                        'threshold_info' => $resolutionCheck['threshold_info'],
                        'required_role' => $resolutionCheck['required_role'] ?? null,
                        'variance_percentage' => abs($variance->calculated_variance_percentage),
                        'escalation_level' => $variance->escalation_level,
                        'variance_category' => $variance->variance_category
                    ]
                ], 403);
            }

            $resolutionAction = $request->resolution_action;
            $newStatus = 'CLOSED';
            $userRole = auth()->user()->role;

            // RESOLUTION LOGIC WITH ENHANCED LOGGING
            switch ($resolutionAction) {
                case 'APPROVE':
                    $newStatus = 'APPROVED';
                    $this->logAction('VARIANCE_APPROVED', $id, json_encode([
                        'reason' => $request->resolution_reason,
                        'approved_by' => auth()->id(),
                        'user_role' => $userRole,
                        'variance_percentage' => $variance->calculated_variance_percentage,
                        'authority_check' => $resolutionCheck['reason']
                    ]));
                    break;

                case 'REJECT':
                    $newStatus = 'REJECTED';
                    $this->logAction('VARIANCE_REJECTED', $id, json_encode([
                        'reason' => $request->resolution_reason,
                        'rejected_by' => auth()->id(),
                        'user_role' => $userRole,
                        'variance_percentage' => $variance->calculated_variance_percentage
                    ]));
                    break;

                case 'ADJUST_FIFO':
                    // ENHANCED FIFO ADJUSTMENT with authority verification
                    if ($request->adjustment_quantity_liters) {
                        $adjustmentResult = $this->adjustFIFOLayers(
                            $variance->tank_id,
                            $request->adjustment_quantity_liters,
                            $request->resolution_reason
                        );

                        if (!$adjustmentResult['success']) {
                            throw new \Exception('FIFO adjustment failed: ' . $adjustmentResult['error']);
                        }
                    }
                    $newStatus = 'CLOSED';
                    break;

                case 'OVERRIDE':
                    // BUSINESS LOGIC OVERRIDE - Enhanced with role verification
                    $newStatus = 'CLOSED';
                    $this->logAction('VARIANCE_OVERRIDE', $id, json_encode([
                        'reason' => $request->resolution_reason,
                        'override_by' => auth()->id(),
                        'user_role' => $userRole,
                        'original_variance_liters' => $variance->calculated_variance_liters,
                        'override_justification' => 'BUSINESS_LOGIC_OVERRIDE',
                        'escalation_level' => $variance->escalation_level,
                        'financial_impact' => $variance->financial_impact_net,
                        'authority_verification' => $resolutionCheck['reason']
                    ]));
                    break;
            }

            // UPDATE VARIANCE STATUS
            DB::table('variances')
                ->where('id', $id)
                ->update([
                    'variance_status' => $newStatus,
                    'manager_explanation' => $request->resolution_reason,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variance resolved successfully',
                'new_status' => $newStatus,
                'resolution_action' => $resolutionAction,
                'resolved_by' => $userRole,
                'authority_info' => $resolutionCheck['reason']
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Resolution failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // HELPER METHODS
    // ================================

    /**
     * Check user access - RESTRICTED TO CEO, SYSTEM_ADMIN, STATION_MANAGER ONLY
     */
    private function hasAccess(): bool
    {
        $userRole = auth()->user()->role ?? '';
        return in_array($userRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']);
    }

    /**
     * Check if user can resolve variance based on role and thresholds
     */
    private function canResolveVariance(float $variancePercentage, string $escalationLevel): array
    {
        $userRole = auth()->user()->role ?? '';
        $absVariancePercentage = abs($variancePercentage);

        // GET THRESHOLDS FROM SYSTEM CONFIGURATION
        $thresholds = $this->getVarianceThresholds();

        // CEO AND SYSTEM_ADMIN - CAN RESOLVE EVERYTHING
        if (in_array($userRole, ['CEO', 'SYSTEM_ADMIN'])) {
            return [
                'can_resolve' => true,
                'reason' => 'Full authority as ' . $userRole,
                'threshold_info' => null
            ];
        }

        // STATION_MANAGER - LIMITED BY THRESHOLDS
        if ($userRole === 'STATION_MANAGER') {
            // STATION MANAGERS CAN ONLY RESOLVE MINOR AND MODERATE VARIANCES
            if ($absVariancePercentage <= $thresholds['moderate_threshold']) {
                return [
                    'can_resolve' => true,
                    'reason' => 'Within station manager authority',
                    'threshold_info' => "Variance {$absVariancePercentage}% ≤ {$thresholds['moderate_threshold']}% (Moderate threshold)"
                ];
            }

            // CANNOT RESOLVE SIGNIFICANT OR CRITICAL VARIANCES
            if ($absVariancePercentage > $thresholds['moderate_threshold']) {
                $requiredLevel = ($absVariancePercentage >= $thresholds['critical_threshold']) ? 'CEO' : 'REGIONAL/CEO';

                return [
                    'can_resolve' => false,
                    'reason' => "Variance {$absVariancePercentage}% exceeds station manager authority ({$thresholds['moderate_threshold']}%)",
                    'threshold_info' => "Requires {$requiredLevel} approval. Escalation level: {$escalationLevel}",
                    'required_role' => $requiredLevel
                ];
            }
        }

        // DEFAULT DENY
        return [
            'can_resolve' => false,
            'reason' => 'Insufficient role permissions',
            'threshold_info' => null
        ];
    }

    /**
     * Get variance thresholds from system configuration
     */
    private function getVarianceThresholds(): array
    {
        return [
            'minor_threshold' => DB::table('system_configurations')
                ->where('config_key', 'MINOR_VARIANCE_PERCENTAGE')
                ->value('config_value_numeric') ?? 0.5,
            'moderate_threshold' => DB::table('system_configurations')
                ->where('config_key', 'MODERATE_VARIANCE_PERCENTAGE')
                ->value('config_value_numeric') ?? 1.0,
            'significant_threshold' => DB::table('system_configurations')
                ->where('config_key', 'SIGNIFICANT_VARIANCE_PERCENTAGE')
                ->value('config_value_numeric') ?? 2.0,
            'critical_threshold' => DB::table('system_configurations')
                ->where('config_key', 'CRITICAL_VARIANCE_PERCENTAGE')
                ->value('config_value_numeric') ?? 5.0
        ];
    }

    /**
     * Analyze FIFO layers for variance context
     */
    private function analyzeFIFOLayers(int $tankId): array
    {
        try {
            $fifoService = $this->getFIFOService();
            $validation = $fifoService->validateFIFOConsistency($tankId);

            $layers = DB::table('tank_inventory_layers')
                ->where('tank_id', $tankId)
                ->where('is_depleted', 0)
                ->select([
                    'current_quantity_liters',
                    'cost_per_liter',
                    'layer_status',
                    'layer_created_at'
                ])
                ->get();

            return [
                'validation' => $validation,
                'active_layers' => $layers->count(),
                'total_fifo_stock' => $layers->sum('current_quantity_liters'),
                'layers' => $layers
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'validation' => null,
                'active_layers' => 0,
                'total_fifo_stock' => 0,
                'layers' => []
            ];
        }
    }

    /**
     * Get historical variance pattern for tank
     */
    private function getHistoricalPattern(int $tankId): array
    {
        return DB::table('variances')
            ->where('tank_id', $tankId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select([
                'calculated_variance_percentage',
                'variance_direction',
                'variance_category',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get recent tank activities
     */
    private function getRecentActivities(int $tankId): array
    {
        return [
            'deliveries' => DB::table('deliveries')
                ->where('tank_id', $tankId)
                ->where('delivery_date', '>=', now()->subDays(7)->toDateString())
                ->select(['delivery_note_number', 'quantity_delivered_liters', 'delivery_date'])
                ->orderBy('delivery_date', 'desc')
                ->limit(5)
                ->get(),
            'readings' => DB::table('dip_readings')
                ->where('tank_id', $tankId)
                ->where('reading_date', '>=', now()->subDays(7)->toDateString())
                ->select(['volume_liters', 'reading_date', 'reading_shift'])
                ->orderBy('reading_date', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    /**
     * Enhanced FIFO adjustment mechanism with success/error handling
     */
    private function adjustFIFOLayers(int $tankId, float $adjustmentQuantity, string $reason): array
    {
        try {
            // GET MOST RECENT LAYER
            $recentLayer = DB::table('tank_inventory_layers')
                ->where('tank_id', $tankId)
                ->where('is_depleted', 0)
                ->orderBy('layer_created_at', 'desc')
                ->first();

            if (!$recentLayer) {
                return [
                    'success' => false,
                    'error' => 'No active FIFO layers found for adjustment'
                ];
            }

            // CALCULATE NEW QUANTITY
            $newQuantity = max(0, $recentLayer->current_quantity_liters + $adjustmentQuantity);
            $newValue = $newQuantity * $recentLayer->cost_per_liter;

            // UPDATE LAYER
            DB::table('tank_inventory_layers')
                ->where('id', $recentLayer->id)
                ->update([
                    'current_quantity_liters' => $newQuantity,
                    'remaining_layer_value' => $newValue,
                    'is_depleted' => ($newQuantity <= 0) ? 1 : 0,
                    'layer_status' => ($newQuantity <= 0) ? 'DEPLETED' : 'ACTIVE'
                ]);

            // LOG ADJUSTMENT
            $this->logAction('FIFO_LAYER_ADJUSTED', $recentLayer->id, json_encode([
                'tank_id' => $tankId,
                'adjustment_quantity' => $adjustmentQuantity,
                'old_quantity' => $recentLayer->current_quantity_liters,
                'new_quantity' => $newQuantity,
                'old_value' => $recentLayer->remaining_layer_value,
                'new_value' => $newValue,
                'reason' => $reason,
                'adjusted_by' => auth()->id(),
                'user_role' => auth()->user()->role
            ]));

            return [
                'success' => true,
                'old_quantity' => $recentLayer->current_quantity_liters,
                'new_quantity' => $newQuantity,
                'adjustment_applied' => $adjustmentQuantity
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'FIFO adjustment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Audit logging
     */
    private function logAction(string $action, int $recordId, string $details): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'action_type' => 'UPDATE',
                'action_category' => 'VARIANCE_MANAGEMENT',
                'table_name' => 'variances',
                'record_id' => $recordId,
                'new_value_text' => $details,
                'change_reason' => $action,
                'business_justification' => 'Variance management operation',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'request_method' => request()->method(),
                'request_url' => request()->fullUrl(),
                'timestamp' => now(),
                'hash_current' => hash('sha256', $details . now()),
                'hash_algorithm' => 'SHA256',
                'risk_level' => 'MEDIUM',
                'sensitivity_level' => 'INTERNAL',
                'compliance_category' => 'OPERATIONAL',
                'system_generated' => 0,
                'validation_passed' => 1
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit logging failed: ' . $e->getMessage());
        }
    }
}
