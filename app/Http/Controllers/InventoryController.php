<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use App\Services\CorrectedFIFOService AS FIFOService;

class InventoryController extends Controller
{
    protected $fifoService;

    /**
     *  EXACT SCHEMA FIELD MAPPING - tank_inventory_layers table
     */
    private const LAYER_FIELDS = [
        'id', 'tank_id', 'delivery_id', 'layer_sequence_number', 'delivery_batch_number',
        'opening_quantity_liters', 'current_quantity_liters', 'consumed_quantity_liters',
        'cost_per_liter', 'total_layer_cost', 'remaining_layer_value',
        'delivery_temperature_celsius', 'delivery_density', 'layer_created_at',
        'first_consumption_at', 'is_depleted', 'fully_depleted_at', 'layer_status'
    ];

    /**
     *  EXACT SCHEMA FIELD MAPPING - inventory_movements table
     */
    private const MOVEMENT_FIELDS = [
        'id', 'tank_id', 'movement_type', 'movement_category', 'reference_table_name',
        'reference_record_id', 'tank_inventory_layer_id', 'quantity_liters', 'unit_cost',
        'total_cost', 'running_balance_liters', 'running_balance_value', 'movement_reason',
        'authorized_by', 'movement_timestamp', 'movement_notes', 'created_by'
    ];

    /**
     *  MATHEMATICAL PRECISION CONSTANTS - ZERO TOLERANCE
     */
    private const VOLUME_TOLERANCE = 0.001;      // 0.001L tolerance
    private const COST_TOLERANCE = 0.0001;       // 0.0001 UGX tolerance
    private const PERCENTAGE_TOLERANCE = 0.001;   // 0.001% tolerance
    private const MAX_RESPONSE_TIME = 2.0;       // 2 seconds max response

    /**
     *  AUDIT TRAIL CATEGORIES - EXACT ENUM VALUES FROM SCHEMA
     */
    private const AUDIT_CATEGORIES = [
        'INVENTORY_ACCESS' => 'REPORTING',
        'FIFO_OPERATION' => 'DATA_ENTRY',
        'LAYER_CREATION' => 'DATA_ENTRY',
        'MOVEMENT_TRACKING' => 'REPORTING',
        'VALUATION_CALC' => 'REPORTING',
        'SYSTEM_MONITORING' => 'MAINTENANCE'
    ];

    public function __construct(FIFOService $fifoService)
    {
        $this->fifoService = $fifoService;
    }

    /**
     *  COMPREHENSIVE INVENTORY MANAGEMENT DASHBOARD
     * INTEGRATES WITH DATABASE AUTOMATION SYSTEM
     * PERFORMANCE TARGET: <2s response time
     */
    public function index()
    {
        $startTime = microtime(true);
        $operationId = uniqid('inv_idx_', true);

        DB::beginTransaction();

        try {
            // STEP 1: ENHANCED PERMISSION VALIDATION
            $accessValidation = $this->validateEnhancedAccess();
            if (!$accessValidation['granted']) {
                $this->logSecurityViolation('INVENTORY_ACCESS_DENIED', 0, $accessValidation);
                return redirect()->back()->with('error', 'Access denied');
            }

            // STEP 2: VALIDATE SYSTEM AUTOMATION HEALTH
            $systemHealth = $this->validateSystemHealth();
            if (!$systemHealth['healthy']) {
                return $this->handleCriticalSystemError('AUTOMATION_SYSTEM_FAILURE', $systemHealth);
            }

            // STEP 3: GET USER CONTEXT WITH ENHANCED VALIDATION
            $userContext = $this->getUserContextSecure();
            $isAutoApproved = in_array($userContext['role'], ['CEO', 'SYSTEM_ADMIN']);

            // STEP 4: EXECUTE SYSTEM MONITORING FOR REAL-TIME STATUS
            $this->executeSystemMonitoring();

            // STEP 5: GET COMPREHENSIVE STATION DATA WITH FIFO INTEGRATION
            $stationData = $this->getStationDataWithFIFO($userContext['stations'], $isAutoApproved);

            // STEP 6: CALCULATE REAL-TIME SYSTEM METRICS WITH PRECISION
            $systemMetrics = $this->calculatePrecisionMetrics($stationData);

            // STEP 7: GET REAL-TIME ACTIVITIES WITH AUTOMATION STATUS
            $recentActivities = $this->getRecentActivitiesWithStatus($userContext['stations'], $isAutoApproved);

            // STEP 8: GET VALUATIONS WITH FIFO VALIDATION
            $recentValuations = $this->getValidatedValuations($userContext['stations'], $isAutoApproved);

            // STEP 9: PERFORMANCE VALIDATION
            $responseTime = microtime(true) - $startTime;
            if ($responseTime > self::MAX_RESPONSE_TIME) {
                $this->logPerformanceAlert('SLOW_DASHBOARD_RESPONSE', [
                    'response_time' => $responseTime,
                    'stations_count' => count($stationData),
                    'user_role' => $userContext['role']
                ]);
            }

            // STEP 10: COMPREHENSIVE AUDIT LOGGING WITH HASH CHAIN
            $this->logInventoryActionSecure('INVENTORY_ACCESS', 0, [
                'operation_id' => $operationId,
                'stations_accessed' => array_keys($stationData),
                'total_stations' => count($stationData),
                'total_tanks' => $systemMetrics['total_tanks'],
                'user_role' => $userContext['role'],
                'response_time_seconds' => round($responseTime, 4),
                'system_health_score' => $systemHealth['score'],
                'automation_status' => $systemHealth['automation_enabled']
            ]);

            DB::commit();

            return view('inventory.index', compact(
                'stationData',
                'systemMetrics',
                'recentActivities',
                'recentValuations',
                'systemHealth',
                'isAutoApproved',
                'userContext',
                'responseTime'
            ));

        } catch (Exception $e) {
            DB::rollback();
            return $this->handleCriticalError($e, 'INVENTORY_DASHBOARD_FAILURE', $operationId);
        }
    }

    /**
     *  FIFO LAYER VISUALIZATION WITH AUTOMATION INTEGRATION
     * REAL-TIME VALIDATION WITH DATABASE PROCEDURES
     */
    public function layers($tankId)
    {
        $startTime = microtime(true);
        $operationId = uniqid('fifo_layers_', true);

        DB::beginTransaction();

        try {
            // STEP 1: ENHANCED ACCESS VALIDATION
            $accessValidation = $this->validateTankAccess($tankId);
            if (!$accessValidation['granted']) {
                return redirect()->back()->with('error', $accessValidation['message']);
            }

            $tank = $accessValidation['tank'];

            // STEP 2: EXECUTE ENHANCED FIFO MONITORING
            DB::statement('CALL sp_enhanced_system_monitor()');

            // STEP 3: GET FIFO LAYERS WITH COMPREHENSIVE DETAILS
            $layers = $this->getFIFOLayersWithValidation($tankId);

            // STEP 4: GET REAL-TIME FIFO STATUS FROM SERVICE
            $fifoStatus = $this->fifoService->validateFIFOConsistency($tankId);

            // STEP 5: GET CONSUMPTION RECORDS WITH AUDIT TRAIL
            $consumptionDetails = $this->getConsumptionDetailsSecure($tankId);

            // STEP 6: CALCULATE PRECISION STATISTICS
            $stats = $this->calculateLayerStatistics($layers, $tankId);

            // STEP 7: VALIDATE MATHEMATICAL CONSISTENCY
            $mathValidation = $this->validateMathematicalConsistency($layers, $fifoStatus);
            if (!$mathValidation['passed']) {
                $this->logCriticalAlert('FIFO_MATH_INCONSISTENCY', $tankId, $mathValidation);
            }

            // STEP 8: AUDIT LOGGING
            $this->logInventoryActionSecure('FIFO_OPERATION', $tankId, [
                'operation_id' => $operationId,
                'total_layers' => count($layers),
                'active_layers' => $stats['active_layers'],
                'math_validation_passed' => $mathValidation['passed'],
                'fifo_health_score' => $fifoStatus['validation_passed'] ? 100 : 0
            ]);

            DB::commit();

            return view('inventory.layers', compact(
                'tank',
                'layers',
                'fifoStatus',
                'stats',
                'consumptionDetails',
                'mathValidation'
            ));

        } catch (Exception $e) {
            DB::rollback();
            return $this->handleCriticalError($e, 'FIFO_LAYERS_ERROR', $operationId);
        }
    }

    /**
     *  FIFO CONSUMPTION PROCESSING WITH AUTOMATION INTEGRATION
     * ZERO TOLERANCE FOR MATHEMATICAL ERRORS
     */
    public function consumeFromLayers(Request $request)
    {
        $startTime = microtime(true);
        $operationId = uniqid('fifo_consume_', true);

        DB::beginTransaction();

        try {
            // STEP 1: ENHANCED INPUT VALIDATION WITH PRECISION
            $validated = $this->validateConsumptionRequest($request);

            // STEP 2: PERMISSION VALIDATION
            if (!$this->hasAccess()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied',
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
            }

            // STEP 3: PRE-CONSUMPTION VALIDATION
            $preValidation = $this->validatePreConsumption($validated);
            if (!$preValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => $preValidation['message'],
                    'error_code' => 'PRE_VALIDATION_FAILED'
                ], 400);
            }

            // STEP 4: EXECUTE FIFO CONSUMPTION VIA AUTOMATION
            $consumptionResult = $this->fifoService->processSaleViaAutomation(
                $validated['tank_id'],
                $validated['pump_id'] ?? null,
                $validated['consumption_liters'],
                $validated['shift'] ?? 'MORNING'
            );

            // STEP 5: POST-CONSUMPTION VALIDATION
            $postValidation = $this->validatePostConsumption($validated['tank_id'], $consumptionResult);
            if (!$postValidation['valid']) {
                throw new Exception('Post-consumption validation failed: ' . $postValidation['message']);
            }

            // STEP 6: AUDIT LOGGING WITH FULL TRAIL
            $this->logInventoryActionSecure('FIFO_OPERATION', $validated['tank_id'], [
                'operation_id' => $operationId,
                'consumption_liters' => $validated['consumption_liters'],
                'layers_affected' => $consumptionResult['layers_consumed'] ?? 0,
                'total_cogs' => $consumptionResult['total_cogs'] ?? 0,
                'weighted_cost' => $consumptionResult['weighted_cost_per_liter'] ?? 0,
                'automation_success' => $consumptionResult['automation_triggered'] ?? false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'operation_id' => $operationId,
                'result' => $consumptionResult,
                'validation' => $postValidation,
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

        } catch (Exception $e) {
            DB::rollback();

            $this->logInventoryActionSecure('FIFO_OPERATION', $validated['tank_id'] ?? 0, [
                'operation_id' => $operationId,
                'error_message' => $e->getMessage(),
                'input_data' => $request->all(),
                'failure_point' => 'CONSUMPTION_PROCESSING'
            ]);

            return response()->json([
                'success' => false,
                'operation_id' => $operationId,
                'error' => 'FIFO consumption failed: ' . $e->getMessage(),
                'error_code' => 'CONSUMPTION_FAILED'
            ], 500);
        }
    }

    /**
     *  INVENTORY MOVEMENTS WITH COMPREHENSIVE AUDIT TRAIL
     */
    public function movements($tankId)
    {
        $startTime = microtime(true);
        $operationId = uniqid('movements_', true);

        try {
            // STEP 1: ACCESS VALIDATION
            $accessValidation = $this->validateTankAccess($tankId);
            if (!$accessValidation['granted']) {
                return redirect()->back()->with('error', $accessValidation['message']);
            }

            $tank = $accessValidation['tank'];

            // STEP 2: GET MOVEMENTS WITH ENHANCED DETAILS
            $movements = $this->getMovementsWithAuditTrail($tankId);

            // STEP 3: CALCULATE MOVEMENT SUMMARY WITH PRECISION
            $summary = $this->calculateMovementSummary($tankId);

            // STEP 4: VALIDATE RUNNING BALANCES
            $balanceValidation = $this->validateRunningBalances($tankId);

            // STEP 5: AUDIT LOGGING
            $this->logInventoryActionSecure('MOVEMENT_TRACKING', $tankId, [
                'operation_id' => $operationId,
                'movements_count' => $movements->total(),
                'balance_validation_passed' => $balanceValidation['passed']
            ]);

            return view('inventory.movements', compact(
                'tank',
                'movements',
                'summary',
                'balanceValidation'
            ));

        } catch (Exception $e) {
            return $this->handleCriticalError($e, 'MOVEMENTS_VIEW_ERROR', $operationId);
        }
    }

    /**
     *  INVENTORY VALUATION WITH FIFO PRECISION
     */
    public function valuation($stationId)
    {
        $startTime = microtime(true);
        $operationId = uniqid('valuation_', true);

        DB::beginTransaction();

        try {
            // STEP 1: ACCESS VALIDATION
            $accessValidation = $this->validateStationAccess($stationId);
            if (!$accessValidation['granted']) {
                return redirect()->back()->with('error', $accessValidation['message']);
            }

            $station = $accessValidation['station'];

            // STEP 2: EXECUTE VALUATION CALCULATION
            $valuation = $this->calculateStationValuation($stationId);

            // STEP 3: GET HISTORICAL VALUATIONS FOR COMPARISON
            $historicalValuations = $this->getHistoricalValuations($stationId);

            // STEP 4: VALIDATE VALUATION ACCURACY
            $accuracyValidation = $this->validateValuationAccuracy($valuation);

            // STEP 5: CREATE VALUATION RECORD
            $valuationId = $this->createValuationRecord($stationId, $valuation);

            // STEP 6: AUDIT LOGGING
            $this->logInventoryActionSecure('VALUATION_CALC', $stationId, [
                'operation_id' => $operationId,
                'valuation_id' => $valuationId,
                'total_value' => $valuation['totals']['total_value'],
                'total_quantity' => $valuation['totals']['total_quantity'],
                'accuracy_passed' => $accuracyValidation['passed']
            ]);

            DB::commit();

            return view('inventory.valuation', compact(
                'station',
                'valuation',
                'historicalValuations',
                'accuracyValidation'
            ));

        } catch (Exception $e) {
            DB::rollback();
            return $this->handleCriticalError($e, 'VALUATION_ERROR', $operationId);
        }
    }

    /**
     *  BATCH CONSUMPTION TRACKING WITH FIFO VALIDATION
     */
    public function batchConsumption($tankId)
    {
        $operationId = uniqid('batch_consumption_', true);

        try {
            // ACCESS VALIDATION
            $accessValidation = $this->validateTankAccess($tankId);
            if (!$accessValidation['granted']) {
                return redirect()->back()->with('error', $accessValidation['message']);
            }

            $tank = $accessValidation['tank'];

            // GET CONSUMPTION RECORDS WITH VALIDATION
            $consumptions = $this->getConsumptionRecordsValidated($tankId);

            // CALCULATE CONSUMPTION SUMMARY
            $summary = $this->calculateConsumptionSummary($tankId);

            // VALIDATE FIFO SEQUENCE
            $sequenceValidation = $this->validateFIFOSequence($tankId);

            // AUDIT LOGGING
            $this->logInventoryActionSecure('FIFO_OPERATION', $tankId, [
                'operation_id' => $operationId,
                'consumption_records' => $consumptions->total(),
                'sequence_validation_passed' => $sequenceValidation['passed']
            ]);

            return view('inventory.batch-consumption', compact(
                'tank',
                'consumptions',
                'summary',
                'sequenceValidation'
            ));

        } catch (Exception $e) {
            return $this->handleCriticalError($e, 'BATCH_CONSUMPTION_ERROR', $operationId);
        }
    }

    /**
     *  GET FIFO LAYERS WITH COMPREHENSIVE VALIDATION - PRODUCTION IMPLEMENTATION
     */
    private function getFIFOLayersWithValidation(int $tankId): array
    {
        $layers = DB::table('tank_inventory_layers as til')
            ->join('deliveries as d', 'til.delivery_id', '=', 'd.id')
            ->join('suppliers as s', 'd.supplier_id', '=', 's.id')
            ->leftJoin('batch_consumption as bc', 'til.id', '=', 'bc.tank_inventory_layer_id')
            ->select([
                'til.id',
                'til.tank_id',
                'til.delivery_id',
                'til.layer_sequence_number',
                'til.delivery_batch_number',
                'til.opening_quantity_liters',
                'til.current_quantity_liters',
                'til.consumed_quantity_liters',
                'til.cost_per_liter',
                'til.total_layer_cost',
                'til.remaining_layer_value',
                'til.delivery_temperature_celsius',
                'til.delivery_density',
                'til.layer_created_at',
                'til.first_consumption_at',
                'til.is_depleted',
                'til.fully_depleted_at',
                'til.layer_status',
                'd.delivery_date',
                'd.delivery_note_number',
                's.company_name as supplier_name',
                DB::raw('COUNT(bc.id) as consumption_transactions'),
                DB::raw('DATEDIFF(NOW(), til.layer_created_at) as age_days'),
                DB::raw('CASE
                    WHEN til.opening_quantity_liters > 0
                    THEN ROUND((til.consumed_quantity_liters / til.opening_quantity_liters) * 100, 2)
                    ELSE 0
                END as consumption_percentage')
            ])
            ->where('til.tank_id', $tankId)
            ->groupBy([
                'til.id', 'til.tank_id', 'til.delivery_id', 'til.layer_sequence_number',
                'til.delivery_batch_number', 'til.opening_quantity_liters', 'til.current_quantity_liters',
                'til.consumed_quantity_liters', 'til.cost_per_liter', 'til.total_layer_cost',
                'til.remaining_layer_value', 'til.delivery_temperature_celsius', 'til.delivery_density',
                'til.layer_created_at', 'til.first_consumption_at', 'til.is_depleted',
                'til.fully_depleted_at', 'til.layer_status', 'd.delivery_date',
                'd.delivery_note_number', 's.company_name'
            ])
            ->orderBy('til.layer_sequence_number')
            ->get()
            ->map(function($layer) {
                return [
                    'id' => $layer->id,
                    'tank_id' => $layer->tank_id,
                    'delivery_id' => $layer->delivery_id,
                    'sequence_number' => $layer->layer_sequence_number,
                    'batch_number' => $layer->delivery_batch_number,
                    'opening_quantity' => round($layer->opening_quantity_liters, 3),
                    'current_quantity' => round($layer->current_quantity_liters, 3),
                    'consumed_quantity' => round($layer->consumed_quantity_liters, 3),
                    'cost_per_liter' => round($layer->cost_per_liter, 4),
                    'total_cost' => round($layer->total_layer_cost, 2),
                    'remaining_value' => round($layer->remaining_layer_value, 2),
                    'temperature' => $layer->delivery_temperature_celsius,
                    'density' => $layer->delivery_density,
                    'created_at' => $layer->layer_created_at,
                    'first_consumption_at' => $layer->first_consumption_at,
                    'is_depleted' => $layer->is_depleted,
                    'depleted_at' => $layer->fully_depleted_at,
                    'status' => $layer->layer_status,
                    'delivery_date' => $layer->delivery_date,
                    'delivery_note' => $layer->delivery_note_number,
                    'supplier_name' => $layer->supplier_name,
                    'consumption_transactions' => $layer->consumption_transactions,
                    'age_days' => $layer->age_days,
                    'consumption_percentage' => $layer->consumption_percentage,
                    'fifo_order' => $layer->layer_sequence_number
                ];
            })
            ->toArray();

        return $layers;
    }

    /**
     *  GET CONSUMPTION DETAILS WITH SECURITY - PRODUCTION IMPLEMENTATION
     */
    private function getConsumptionDetailsSecure(int $tankId): array
    {
        return DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->join('readings as r', 'bc.reading_id', '=', 'r.id')
            ->leftJoin('pumps as p', 'bc.pump_id', '=', 'p.id')
            ->leftJoin('users as u', 'r.entered_by', '=', 'u.id')
            ->select([
                'bc.id as consumption_id',
                'bc.consumption_sequence',
                'bc.quantity_consumed_liters',
                'bc.cost_per_liter',
                'bc.total_cost_consumed',
                'bc.layer_balance_before_liters',
                'bc.layer_balance_after_liters',
                'bc.is_layer_depleted',
                'bc.consumption_method',
                'bc.consumption_timestamp',
                'bc.sale_date',
                'til.layer_sequence_number',
                'til.delivery_batch_number',
                'r.reading_date',
                'r.reading_time',
                'r.reading_shift',
                'r.reading_type',
                'p.pump_number',
                'u.first_name',
                'u.last_name'
            ])
            ->where('til.tank_id', $tankId)
            ->orderBy('bc.consumption_timestamp', 'desc')
            ->limit(100)
            ->get()
            ->map(function($consumption) {
                return [
                    'consumption_id' => $consumption->consumption_id,
                    'sequence' => $consumption->consumption_sequence,
                    'quantity_consumed' => round($consumption->quantity_consumed_liters, 3),
                    'cost_per_liter' => round($consumption->cost_per_liter, 4),
                    'total_cost' => round($consumption->total_cost_consumed, 2),
                    'balance_before' => round($consumption->layer_balance_before_liters, 3),
                    'balance_after' => round($consumption->layer_balance_after_liters, 3),
                    'layer_depleted' => $consumption->is_layer_depleted,
                    'method' => $consumption->consumption_method,
                    'consumed_at' => $consumption->consumption_timestamp,
                    'sale_date' => $consumption->sale_date,
                    'layer_sequence' => $consumption->layer_sequence_number,
                    'batch_number' => $consumption->delivery_batch_number,
                    'reading_date' => $consumption->reading_date,
                    'reading_time' => $consumption->reading_time,
                    'reading_shift' => $consumption->reading_shift,
                    'reading_type' => $consumption->reading_type,
                    'pump_number' => $consumption->pump_number,
                    'entered_by' => $consumption->first_name . ' ' . $consumption->last_name
                ];
            })
            ->toArray();
    }

    /**
     *  CALCULATE LAYER STATISTICS - PRODUCTION IMPLEMENTATION
     */
    private function calculateLayerStatistics(array $layers, int $tankId): array
    {
        $activeLayers = array_filter($layers, function($layer) {
            return !$layer['is_depleted'];
        });

        $depletedLayers = array_filter($layers, function($layer) {
            return $layer['is_depleted'];
        });

        $totalQuantity = array_sum(array_column($activeLayers, 'current_quantity'));
        $totalValue = array_sum(array_column($activeLayers, 'remaining_value'));
        $averageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;

        $oldestActiveLayer = null;
        $newestActiveLayer = null;

        if (!empty($activeLayers)) {
            usort($activeLayers, function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });

            $oldestActiveLayer = $activeLayers[0];
            $newestActiveLayer = end($activeLayers);
        }

        return [
            'total_layers' => count($layers),
            'active_layers' => count($activeLayers),
            'depleted_layers' => count($depletedLayers),
            'total_active_quantity' => round($totalQuantity, 3),
            'total_active_value' => round($totalValue, 2),
            'weighted_average_cost' => round($averageCost, 4),
            'oldest_active_layer' => $oldestActiveLayer,
            'newest_active_layer' => $newestActiveLayer,
            'average_layer_age_days' => !empty($activeLayers) ?
                round(array_sum(array_column($activeLayers, 'age_days')) / count($activeLayers), 1) : 0,
            'fifo_compliance_score' => $this->calculateFIFOComplianceScore($layers),
            'turnover_estimate_days' => $this->calculateTurnoverEstimate($layers)
        ];
    }

    /**
     *  VALIDATE MATHEMATICAL CONSISTENCY - PRODUCTION IMPLEMENTATION
     */
    private function validateMathematicalConsistency(array $layers, array $fifoStatus): array
    {
        $errors = [];
        $warnings = [];
        $totalErrors = 0;

        foreach ($layers as $layer) {
            // Check opening = current + consumed
            $calculatedOpening = $layer['current_quantity'] + $layer['consumed_quantity'];
            $openingDifference = abs($layer['opening_quantity'] - $calculatedOpening);

            if ($openingDifference > self::VOLUME_TOLERANCE) {
                $errors[] = [
                    'layer_id' => $layer['id'],
                    'type' => 'VOLUME_INCONSISTENCY',
                    'message' => "Layer {$layer['sequence_number']}: Opening quantity mismatch",
                    'expected' => $layer['opening_quantity'],
                    'calculated' => round($calculatedOpening, 3),
                    'difference' => round($openingDifference, 3)
                ];
                $totalErrors++;
            }

            // Check value calculation
            if ($layer['current_quantity'] > 0) {
                $calculatedValue = $layer['current_quantity'] * $layer['cost_per_liter'];
                $valueDifference = abs($layer['remaining_value'] - $calculatedValue);

                if ($valueDifference > self::COST_TOLERANCE) {
                    $errors[] = [
                        'layer_id' => $layer['id'],
                        'type' => 'VALUE_INCONSISTENCY',
                        'message' => "Layer {$layer['sequence_number']}: Value calculation mismatch",
                        'expected' => $layer['remaining_value'],
                        'calculated' => round($calculatedValue, 2),
                        'difference' => round($valueDifference, 2)
                    ];
                    $totalErrors++;
                }
            }

            // Check depletion status
            if ($layer['is_depleted'] && $layer['current_quantity'] > self::VOLUME_TOLERANCE) {
                $warnings[] = [
                    'layer_id' => $layer['id'],
                    'type' => 'DEPLETION_FLAG_WARNING',
                    'message' => "Layer {$layer['sequence_number']}: Marked depleted but has quantity",
                    'current_quantity' => $layer['current_quantity']
                ];
            }

            if (!$layer['is_depleted'] && $layer['current_quantity'] <= self::VOLUME_TOLERANCE) {
                $warnings[] = [
                    'layer_id' => $layer['id'],
                    'type' => 'DEPLETION_FLAG_WARNING',
                    'message' => "Layer {$layer['sequence_number']}: Not marked depleted but quantity near zero",
                    'current_quantity' => $layer['current_quantity']
                ];
            }
        }

        $passed = $totalErrors === 0;

        return [
            'passed' => $passed,
            'total_errors' => $totalErrors,
            'total_warnings' => count($warnings),
            'errors' => $errors,
            'warnings' => $warnings,
            'fifo_status_integration' => $fifoStatus['validation_passed'] ?? false,
            'validation_timestamp' => now(),
            'compliance_percentage' => $passed ? 100 : max(0, 100 - ($totalErrors * 10))
        ];
    }

    /**
     *  VALIDATE CONSUMPTION REQUEST - PRODUCTION IMPLEMENTATION
     */
    private function validateConsumptionRequest(Request $request): array
    {
        $rules = [
            'tank_id' => 'required|integer|exists:tanks,id',
            'pump_id' => 'nullable|integer|exists:pumps,id',
            'consumption_liters' => 'required|numeric|min:0.001|max:999999.999',
            'shift' => 'required|in:MORNING,EVENING'
        ];

        $validated = $request->validate($rules);

        // Additional business validation
        if (isset($validated['pump_id'])) {
            $pumpTankMatch = DB::table('pumps')
                ->where('id', $validated['pump_id'])
                ->where('tank_id', $validated['tank_id'])
                ->where('is_operational', 1)
                ->exists();

            if (!$pumpTankMatch) {
                throw new Exception("Pump {$validated['pump_id']} is not connected to tank {$validated['tank_id']} or not operational");
            }
        }

        return $validated;
    }

    /**
     *  VALIDATE PRE-CONSUMPTION - PRODUCTION IMPLEMENTATION
     */
    private function validatePreConsumption(array $validated): array
    {
        $tankId = $validated['tank_id'];
        $requestedQuantity = $validated['consumption_liters'];

        // Check available inventory
        $availableQuantity = DB::table('tank_inventory_layers')
            ->where('tank_id', $tankId)
            ->where('is_depleted', 0)
            ->sum('current_quantity_liters');

        if ($availableQuantity < $requestedQuantity) {
            return [
                'valid' => false,
                'message' => "Insufficient inventory. Available: {$availableQuantity}L, Requested: {$requestedQuantity}L",
                'available_quantity' => round($availableQuantity, 3),
                'requested_quantity' => round($requestedQuantity, 3),
                'shortage' => round($requestedQuantity - $availableQuantity, 3)
            ];
        }

        // Check if tank has active layers
        $activeLayers = DB::table('tank_inventory_layers')
            ->where('tank_id', $tankId)
            ->where('is_depleted', 0)
            ->count();

        if ($activeLayers === 0) {
            return [
                'valid' => false,
                'message' => "No active inventory layers found for tank {$tankId}",
                'active_layers' => 0
            ];
        }

        // Check FIFO processing configuration
        $fifoEnabled = DB::table('system_configurations')
            ->where('config_key', 'ENHANCED_FIFO_PROCESSING_ENABLED')
            ->value('config_value_boolean');

        if (!$fifoEnabled) {
            return [
                'valid' => false,
                'message' => "FIFO processing is disabled in system configuration",
                'fifo_enabled' => false
            ];
        }

        return [
            'valid' => true,
            'available_quantity' => round($availableQuantity, 3),
            'active_layers' => $activeLayers,
            'fifo_enabled' => $fifoEnabled
        ];
    }

    /**
     *  VALIDATE POST-CONSUMPTION - PRODUCTION IMPLEMENTATION
     */
    private function validatePostConsumption(int $tankId, array $consumptionResult): array
    {
        if (!$consumptionResult['success']) {
            return [
                'valid' => false,
                'message' => 'Consumption processing reported failure',
                'result' => $consumptionResult
            ];
        }

        // Verify consumption records were created
        $consumptionRecords = DB::table('batch_consumption')
            ->where('reading_id', $consumptionResult['reading_id'])
            ->count();

        if ($consumptionRecords === 0) {
            return [
                'valid' => false,
                'message' => 'No consumption records created by automation',
                'reading_id' => $consumptionResult['reading_id']
            ];
        }

        // Verify layer updates
        $updatedLayers = DB::table('tank_inventory_layers')
            ->where('tank_id', $tankId)
            ->where('consumed_quantity_liters', '>', 0)
            ->count();

        if ($updatedLayers === 0) {
            return [
                'valid' => false,
                'message' => 'No layers show consumption updates',
                'tank_id' => $tankId
            ];
        }

        // Re-run system monitoring to check consistency
        try {
            DB::statement('CALL sp_enhanced_system_monitor()');

            $recentErrors = DB::table('system_health_monitoring')
                ->whereIn('check_type', ['ENHANCED_FIFO_MATH_CHECK', 'ENHANCED_VALUE_CALC_CHECK'])
                ->where('check_status', 'FAILED')
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(2))
                ->count();

            if ($recentErrors > 0) {
                return [
                    'valid' => false,
                    'message' => 'Post-consumption system monitoring detected errors',
                    'monitoring_errors' => $recentErrors
                ];
            }

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Post-consumption monitoring failed: ' . $e->getMessage()
            ];
        }

        return [
            'valid' => true,
            'consumption_records' => $consumptionRecords,
            'updated_layers' => $updatedLayers,
            'monitoring_passed' => true,
            'processing_result' => $consumptionResult
        ];
    }

    /**
     *  GET MOVEMENTS WITH AUDIT TRAIL - PRODUCTION IMPLEMENTATION
     */
    private function getMovementsWithAuditTrail(int $tankId): object
    {
        return DB::table('inventory_movements as im')
            ->leftJoin('users as authorized', 'im.authorized_by', '=', 'authorized.id')
            ->leftJoin('users as created', 'im.created_by', '=', 'created.id')
            ->leftJoin('tank_inventory_layers as til', 'im.tank_inventory_layer_id', '=', 'til.id')
            ->select([
                'im.id',
                'im.movement_type',
                'im.movement_category',
                'im.reference_table_name',
                'im.reference_record_id',
                'im.quantity_liters',
                'im.unit_cost',
                'im.total_cost',
                'im.running_balance_liters',
                'im.running_balance_value',
                'im.movement_reason',
                'im.movement_timestamp',
                'im.movement_notes',
                'authorized.first_name as authorized_by_first_name',
                'authorized.last_name as authorized_by_last_name',
                'created.first_name as created_by_first_name',
                'created.last_name as created_by_last_name',
                'til.layer_sequence_number',
                'til.delivery_batch_number'
            ])
            ->where('im.tank_id', $tankId)
            ->orderBy('im.movement_timestamp', 'desc')
            ->paginate(50);
    }

    /**
     *  CALCULATE MOVEMENT SUMMARY - PRODUCTION IMPLEMENTATION
     */
    private function calculateMovementSummary(int $tankId): array
    {
        $summary = DB::table('inventory_movements')
            ->selectRaw('
                movement_type,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN quantity_liters > 0 THEN quantity_liters ELSE 0 END) as total_inbound,
                SUM(CASE WHEN quantity_liters < 0 THEN ABS(quantity_liters) ELSE 0 END) as total_outbound,
                SUM(quantity_liters) as net_movement,
                SUM(total_cost) as total_value_impact,
                MIN(movement_timestamp) as earliest_movement,
                MAX(movement_timestamp) as latest_movement
            ')
            ->where('tank_id', $tankId)
            ->groupBy('movement_type')
            ->get();

        $totals = DB::table('inventory_movements')
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN quantity_liters > 0 THEN quantity_liters ELSE 0 END) as total_inbound,
                SUM(CASE WHEN quantity_liters < 0 THEN ABS(quantity_liters) ELSE 0 END) as total_outbound,
                SUM(quantity_liters) as net_movement,
                SUM(total_cost) as total_value_impact,
                MIN(movement_timestamp) as earliest_movement,
                MAX(movement_timestamp) as latest_movement
            ')
            ->where('tank_id', $tankId)
            ->first();

        return [
            'by_type' => $summary->map(function($item) {
                return [
                    'movement_type' => $item->movement_type,
                    'transaction_count' => $item->transaction_count,
                    'total_inbound' => round($item->total_inbound, 3),
                    'total_outbound' => round($item->total_outbound, 3),
                    'net_movement' => round($item->net_movement, 3),
                    'total_value_impact' => round($item->total_value_impact, 2),
                    'earliest_movement' => $item->earliest_movement,
                    'latest_movement' => $item->latest_movement
                ];
            })->toArray(),
            'totals' => [
                'total_transactions' => $totals->total_transactions,
                'total_inbound' => round($totals->total_inbound, 3),
                'total_outbound' => round($totals->total_outbound, 3),
                'net_movement' => round($totals->net_movement, 3),
                'total_value_impact' => round($totals->total_value_impact, 2),
                'earliest_movement' => $totals->earliest_movement,
                'latest_movement' => $totals->latest_movement
            ]
        ];
    }

    /**
     *  VALIDATE RUNNING BALANCES - PRODUCTION IMPLEMENTATION
     */
    private function validateRunningBalances(int $tankId): array
    {
        $movements = DB::table('inventory_movements')
            ->select(['id', 'quantity_liters', 'running_balance_liters', 'running_balance_value', 'movement_timestamp'])
            ->where('tank_id', $tankId)
            ->orderBy('movement_timestamp')
            ->get();

        $errors = [];
        $previousBalance = 0;

        foreach ($movements as $movement) {
            $expectedBalance = $previousBalance + $movement->quantity_liters;
            $balanceDifference = abs($movement->running_balance_liters - $expectedBalance);

            if ($balanceDifference > self::VOLUME_TOLERANCE) {
                $errors[] = [
                    'movement_id' => $movement->id,
                    'expected_balance' => round($expectedBalance, 3),
                    'recorded_balance' => round($movement->running_balance_liters, 3),
                    'difference' => round($balanceDifference, 3),
                    'movement_timestamp' => $movement->movement_timestamp
                ];
            }

            $previousBalance = $movement->running_balance_liters;
        }

        return [
            'passed' => count($errors) === 0,
            'total_movements_checked' => $movements->count(),
            'balance_errors' => count($errors),
            'errors' => $errors,
            'final_balance' => round($previousBalance, 3),
            'validation_timestamp' => now()
        ];
    }

    /**
     *  CALCULATE STATION VALUATION - PRODUCTION IMPLEMENTATION
     */
    private function calculateStationValuation(int $stationId): array
    {
        $tankValuations = DB::table('tanks as t')
            ->join('products as p', 't.product_id', '=', 'p.id')
            ->leftJoin('tank_inventory_layers as til', function($join) {
                $join->on('t.id', '=', 'til.tank_id')
                     ->where('til.is_depleted', 0);
            })
            ->select([
                't.id as tank_id',
                't.tank_number',
                'p.product_type',
                'p.product_name',
                DB::raw('COALESCE(SUM(til.current_quantity_liters), 0) as current_quantity'),
                DB::raw('COALESCE(SUM(til.remaining_layer_value), 0) as current_value'),
                DB::raw('COALESCE(AVG(til.cost_per_liter), 0) as weighted_cost'),
                DB::raw('COUNT(til.id) as active_layers')
            ])
            ->where('t.station_id', $stationId)
            ->where('t.is_active', 1)
            ->groupBy(['t.id', 't.tank_number', 'p.product_type', 'p.product_name'])
            ->get();

        $productTotals = [];
        $grandTotals = [
            'total_quantity' => 0,
            'total_value' => 0,
            'total_tanks' => 0,
            'total_active_layers' => 0
        ];

        foreach ($tankValuations as $tank) {
            $productType = $tank->product_type;

            if (!isset($productTotals[$productType])) {
                $productTotals[$productType] = [
                    'product_name' => $tank->product_name,
                    'tank_count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                    'weighted_average_cost' => 0,
                    'active_layers' => 0
                ];
            }

            $productTotals[$productType]['tank_count']++;
            $productTotals[$productType]['total_quantity'] += $tank->current_quantity;
            $productTotals[$productType]['total_value'] += $tank->current_value;
            $productTotals[$productType]['active_layers'] += $tank->active_layers;

            $grandTotals['total_quantity'] += $tank->current_quantity;
            $grandTotals['total_value'] += $tank->current_value;
            $grandTotals['total_tanks']++;
            $grandTotals['total_active_layers'] += $tank->active_layers;
        }

        // Calculate weighted average costs for products
        foreach ($productTotals as $productType => &$product) {
            $product['weighted_average_cost'] = $product['total_quantity'] > 0
                ? $product['total_value'] / $product['total_quantity']
                : 0;
        }

        $grandTotals['weighted_average_cost'] = $grandTotals['total_quantity'] > 0
            ? $grandTotals['total_value'] / $grandTotals['total_quantity']
            : 0;

        return [
            'station_id' => $stationId,
            'valuation_date' => now()->format('Y-m-d'),
            'valuation_time' => now()->format('H:i:s'),
            'tank_details' => $tankValuations->map(function($tank) {
                return [
                    'tank_id' => $tank->tank_id,
                    'tank_number' => $tank->tank_number,
                    'product_type' => $tank->product_type,
                    'product_name' => $tank->product_name,
                    'current_quantity' => round($tank->current_quantity, 3),
                    'current_value' => round($tank->current_value, 2),
                    'weighted_cost' => round($tank->weighted_cost, 4),
                    'active_layers' => $tank->active_layers
                ];
            })->toArray(),
            'product_totals' => array_map(function($product) {
                return [
                    'product_name' => $product['product_name'],
                    'tank_count' => $product['tank_count'],
                    'total_quantity' => round($product['total_quantity'], 3),
                    'total_value' => round($product['total_value'], 2),
                    'weighted_average_cost' => round($product['weighted_average_cost'], 4),
                    'active_layers' => $product['active_layers']
                ];
            }, $productTotals),
            'totals' => [
                'total_quantity' => round($grandTotals['total_quantity'], 3),
                'total_value' => round($grandTotals['total_value'], 2),
                'weighted_average_cost' => round($grandTotals['weighted_average_cost'], 4),
                'total_tanks' => $grandTotals['total_tanks'],
                'total_active_layers' => $grandTotals['total_active_layers']
            ]
        ];
    }

    /**
     *  GET HISTORICAL VALUATIONS - PRODUCTION IMPLEMENTATION
     */
    private function getHistoricalValuations(int $stationId): array
    {
        return DB::table('inventory_valuations')
            ->select([
                'id',
                'valuation_date',
                'valuation_time',
                'valuation_type',
                'total_fuel_quantity_liters',
                'total_fuel_value',
                'valuation_method',
                'valuation_variance_percentage',
                'created_at'
            ])
            ->where('station_id', $stationId)
            ->orderBy('valuation_date', 'desc')
            ->orderBy('valuation_time', 'desc')
            ->limit(30)
            ->get()
            ->map(function($valuation) {
                return [
                    'id' => $valuation->id,
                    'valuation_date' => $valuation->valuation_date,
                    'valuation_time' => $valuation->valuation_time,
                    'valuation_type' => $valuation->valuation_type,
                    'total_quantity' => round($valuation->total_fuel_quantity_liters, 3),
                    'total_value' => round($valuation->total_fuel_value, 2),
                    'valuation_method' => $valuation->valuation_method,
                    'variance_percentage' => round($valuation->valuation_variance_percentage ?? 0, 3),
                    'created_at' => $valuation->created_at
                ];
            })
            ->toArray();
    }

    /**
     *  VALIDATE VALUATION ACCURACY - PRODUCTION IMPLEMENTATION
     */
    private function validateValuationAccuracy(array $valuation): array
    {
        $errors = [];
        $warnings = [];

        // Cross-check with FIFO layers
        $fifoTotals = DB::table('tank_inventory_layers as til')
            ->join('tanks as t', 'til.tank_id', '=', 't.id')
            ->selectRaw('
                SUM(til.current_quantity_liters) as fifo_quantity,
                SUM(til.remaining_layer_value) as fifo_value
            ')
            ->where('t.station_id', $valuation['station_id'])
            ->where('til.is_depleted', 0)
            ->first();

        $quantityDifference = abs($valuation['totals']['total_quantity'] - $fifoTotals->fifo_quantity);
        $valueDifference = abs($valuation['totals']['total_value'] - $fifoTotals->fifo_value);

        if ($quantityDifference > self::VOLUME_TOLERANCE) {
            $errors[] = [
                'type' => 'QUANTITY_MISMATCH',
                'message' => 'Valuation quantity does not match FIFO layers',
                'valuation_quantity' => $valuation['totals']['total_quantity'],
                'fifo_quantity' => round($fifoTotals->fifo_quantity, 3),
                'difference' => round($quantityDifference, 3)
            ];
        }

        if ($valueDifference > self::COST_TOLERANCE) {
            $errors[] = [
                'type' => 'VALUE_MISMATCH',
                'message' => 'Valuation value does not match FIFO layers',
                'valuation_value' => $valuation['totals']['total_value'],
                'fifo_value' => round($fifoTotals->fifo_value, 2),
                'difference' => round($valueDifference, 2)
            ];
        }

        // Check for negative values
        foreach ($valuation['tank_details'] as $tank) {
            if ($tank['current_quantity'] < 0) {
                $errors[] = [
                    'type' => 'NEGATIVE_QUANTITY',
                    'message' => "Tank {$tank['tank_number']} has negative quantity",
                    'tank_id' => $tank['tank_id'],
                    'quantity' => $tank['current_quantity']
                ];
            }

            if ($tank['current_value'] < 0) {
                $errors[] = [
                    'type' => 'NEGATIVE_VALUE',
                    'message' => "Tank {$tank['tank_number']} has negative value",
                    'tank_id' => $tank['tank_id'],
                    'value' => $tank['current_value']
                ];
            }
        }

        return [
            'passed' => count($errors) === 0,
            'total_errors' => count($errors),
            'total_warnings' => count($warnings),
            'errors' => $errors,
            'warnings' => $warnings,
            'fifo_cross_check' => [
                'fifo_quantity' => round($fifoTotals->fifo_quantity, 3),
                'fifo_value' => round($fifoTotals->fifo_value, 2),
                'quantity_difference' => round($quantityDifference, 3),
                'value_difference' => round($valueDifference, 2)
            ],
            'validation_timestamp' => now()
        ];
    }

    /**
     *  CREATE VALUATION RECORD - PRODUCTION IMPLEMENTATION
     */
    private function createValuationRecord(int $stationId, array $valuation): int
    {
        return DB::table('inventory_valuations')->insertGetId([
            'station_id' => $stationId,
            'valuation_date' => now()->format('Y-m-d'),
            'valuation_time' => now()->format('H:i:s'),
            'valuation_type' => 'SPOT_CHECK',
            'total_fuel_quantity_liters' => $valuation['totals']['total_quantity'],
            'total_fuel_value' => $valuation['totals']['total_value'],
            'petrol_95_quantity_liters' => $valuation['product_totals']['PETROL_95']['total_quantity'] ?? 0,
            'petrol_95_weighted_cost' => $valuation['product_totals']['PETROL_95']['weighted_average_cost'] ?? 0,
            'petrol_95_total_value' => $valuation['product_totals']['PETROL_95']['total_value'] ?? 0,
            'petrol_98_quantity_liters' => $valuation['product_totals']['PETROL_98']['total_quantity'] ?? 0,
            'petrol_98_weighted_cost' => $valuation['product_totals']['PETROL_98']['weighted_average_cost'] ?? 0,
            'petrol_98_total_value' => $valuation['product_totals']['PETROL_98']['total_value'] ?? 0,
            'diesel_quantity_liters' => $valuation['product_totals']['DIESEL']['total_quantity'] ?? 0,
            'diesel_weighted_cost' => $valuation['product_totals']['DIESEL']['weighted_average_cost'] ?? 0,
            'diesel_total_value' => $valuation['product_totals']['DIESEL']['total_value'] ?? 0,
            'kerosene_quantity_liters' => $valuation['product_totals']['KEROSENE']['total_quantity'] ?? 0,
            'kerosene_weighted_cost' => $valuation['product_totals']['KEROSENE']['weighted_average_cost'] ?? 0,
            'kerosene_total_value' => $valuation['product_totals']['KEROSENE']['total_value'] ?? 0,
            'valuation_method' => 'FIFO',
            'created_at' => now(),
            'created_by' => auth()->id()
        ]);
    }

    /**
     *  GET CONSUMPTION RECORDS VALIDATED - PRODUCTION IMPLEMENTATION
     */
    private function getConsumptionRecordsValidated(int $tankId): object
    {
        return DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->join('readings as r', 'bc.reading_id', '=', 'r.id')
            ->leftJoin('pumps as p', 'bc.pump_id', '=', 'p.id')
            ->leftJoin('users as u', 'r.entered_by', '=', 'u.id')
            ->select([
                'bc.id',
                'bc.consumption_sequence',
                'bc.quantity_consumed_liters',
                'bc.cost_per_liter',
                'bc.total_cost_consumed',
                'bc.layer_balance_before_liters',
                'bc.layer_balance_after_liters',
                'bc.is_layer_depleted',
                'bc.consumption_method',
                'bc.consumption_timestamp',
                'bc.sale_date',
                'til.layer_sequence_number',
                'til.delivery_batch_number',
                'r.reading_date',
                'r.reading_shift',
                'r.reading_type',
                'p.pump_number',
                'u.first_name',
                'u.last_name'
            ])
            ->where('til.tank_id', $tankId)
            ->orderBy('bc.consumption_timestamp', 'desc')
            ->paginate(50);
    }

    /**
     *  CALCULATE CONSUMPTION SUMMARY - PRODUCTION IMPLEMENTATION
     */
    private function calculateConsumptionSummary(int $tankId): array
    {
        $last30Days = DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(bc.quantity_consumed_liters) as total_quantity,
                SUM(bc.total_cost_consumed) as total_cogs,
                AVG(bc.cost_per_liter) as average_cost,
                MIN(bc.consumption_timestamp) as earliest_consumption,
                MAX(bc.consumption_timestamp) as latest_consumption,
                COUNT(DISTINCT bc.tank_inventory_layer_id) as layers_consumed
            ')
            ->where('til.tank_id', $tankId)
            ->where('bc.consumption_timestamp', '>=', Carbon::now()->subDays(30))
            ->first();

        $byMethod = DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->selectRaw('
                bc.consumption_method,
                COUNT(*) as transaction_count,
                SUM(bc.quantity_consumed_liters) as total_quantity,
                SUM(bc.total_cost_consumed) as total_cost
            ')
            ->where('til.tank_id', $tankId)
            ->where('bc.consumption_timestamp', '>=', Carbon::now()->subDays(30))
            ->groupBy('bc.consumption_method')
            ->get();

        return [
            'last_30_days' => [
                'total_transactions' => $last30Days->total_transactions,
                'total_quantity' => round($last30Days->total_quantity ?? 0, 3),
                'total_cogs' => round($last30Days->total_cogs ?? 0, 2),
                'average_cost' => round($last30Days->average_cost ?? 0, 4),
                'layers_consumed' => $last30Days->layers_consumed,
                'earliest_consumption' => $last30Days->earliest_consumption,
                'latest_consumption' => $last30Days->latest_consumption
            ],
            'by_method' => $byMethod->map(function($method) {
                return [
                    'consumption_method' => $method->consumption_method,
                    'transaction_count' => $method->transaction_count,
                    'total_quantity' => round($method->total_quantity, 3),
                    'total_cost' => round($method->total_cost, 2),
                    'average_cost' => $method->total_quantity > 0 ?
                        round($method->total_cost / $method->total_quantity, 4) : 0
                ];
            })->toArray()
        ];
    }

    /**
     *  VALIDATE FIFO SEQUENCE - PRODUCTION IMPLEMENTATION
     */
    private function validateFIFOSequence(int $tankId): array
    {
        $errors = [];

        // Get consumption records ordered by timestamp
        $consumptions = DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->select([
                'bc.id',
                'bc.consumption_timestamp',
                'bc.consumption_sequence',
                'til.layer_sequence_number',
                'til.layer_created_at'
            ])
            ->where('til.tank_id', $tankId)
            ->orderBy('bc.consumption_timestamp')
            ->get();

        $previousLayerSequence = 0;

        foreach ($consumptions as $consumption) {
            // Check if consumption follows FIFO order (should consume from oldest layers first)
            if ($consumption->layer_sequence_number < $previousLayerSequence) {
                $errors[] = [
                    'consumption_id' => $consumption->id,
                    'type' => 'FIFO_ORDER_VIOLATION',
                    'message' => 'Consumption from newer layer before older layer depleted',
                    'layer_sequence' => $consumption->layer_sequence_number,
                    'previous_sequence' => $previousLayerSequence,
                    'consumption_timestamp' => $consumption->consumption_timestamp
                ];
            }

            $previousLayerSequence = max($previousLayerSequence, $consumption->layer_sequence_number);
        }

        // Check for gaps in consumption sequence within the same reading
        $sequenceGaps = DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->selectRaw('
                bc.reading_id,
                COUNT(*) as consumption_count,
                MIN(bc.consumption_sequence) as min_sequence,
                MAX(bc.consumption_sequence) as max_sequence
            ')
            ->where('til.tank_id', $tankId)
            ->groupBy('bc.reading_id')
            ->havingRaw('(max_sequence - min_sequence + 1) != consumption_count')
            ->get();

        foreach ($sequenceGaps as $gap) {
            $errors[] = [
                'reading_id' => $gap->reading_id,
                'type' => 'SEQUENCE_GAP',
                'message' => 'Missing consumption sequence numbers within reading',
                'expected_count' => $gap->max_sequence - $gap->min_sequence + 1,
                'actual_count' => $gap->consumption_count
            ];
        }

        return [
            'passed' => count($errors) === 0,
            'total_errors' => count($errors),
            'errors' => $errors,
            'total_consumptions_checked' => $consumptions->count(),
            'validation_timestamp' => now()
        ];
    }

    /**
     *  CALCULATE FIFO COMPLIANCE SCORE - PRODUCTION IMPLEMENTATION
     */
    private function calculateFIFOComplianceScore(array $layers): int
    {
        $score = 100;
        $totalLayers = count($layers);

        if ($totalLayers === 0) return 100;

        // Deduct points for layers consumed out of order
        $activeLayers = array_filter($layers, function($layer) {
            return !$layer['is_depleted'];
        });

        $depletedLayers = array_filter($layers, function($layer) {
            return $layer['is_depleted'];
        });

        // Check if older layers are depleted before newer ones
        foreach ($activeLayers as $activeLayer) {
            foreach ($depletedLayers as $depletedLayer) {
                if ($activeLayer['sequence_number'] < $depletedLayer['sequence_number']) {
                    $score -= 10; // Deduct 10 points for each violation
                }
            }
        }

        // Deduct points for mathematical inconsistencies
        foreach ($layers as $layer) {
            $calculatedOpening = $layer['current_quantity'] + $layer['consumed_quantity'];
            $difference = abs($layer['opening_quantity'] - $calculatedOpening);

            if ($difference > self::VOLUME_TOLERANCE) {
                $score -= 5; // Deduct 5 points for each math error
            }
        }

        return max(0, $score);
    }

    /**
     *  CALCULATE TURNOVER ESTIMATE - PRODUCTION IMPLEMENTATION
     */
    private function calculateTurnoverEstimate(array $layers): float
    {
        if (empty($layers)) return 0;

        $activeLayers = array_filter($layers, function($layer) {
            return !$layer['is_depleted'];
        });

        if (empty($activeLayers)) return 0;

        $totalQuantity = array_sum(array_column($activeLayers, 'current_quantity'));

        // Get average daily consumption for last 30 days
        $avgDailyConsumption = DB::table('batch_consumption as bc')
            ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
            ->where('til.tank_id', $activeLayers[0]['tank_id'])
            ->where('bc.consumption_timestamp', '>=', Carbon::now()->subDays(30))
            ->avg('quantity_consumed_liters');

        if (!$avgDailyConsumption || $avgDailyConsumption <= 0) return 0;

        return round($totalQuantity / $avgDailyConsumption, 1);
    }

    /**
     *  LOG CRITICAL ALERT - PRODUCTION IMPLEMENTATION
     */
    private function logCriticalAlert(string $alertType, int $tankId, array $data): void
    {
        DB::table('system_accuracy_alerts')->insert([
            'alert_type' => 'FIFO_MATH_ERROR',
            'severity' => 'CRITICAL',
            'tank_id' => $tankId,
            'error_description' => json_encode($data),
            'current_values' => json_encode($data['errors'] ?? []),
            'variance_amount' => $data['total_errors'] ?? 0,
            'detected_at' => now()
        ]);

        // Also log to system health monitoring
        DB::table('system_health_monitoring')->insert([
            'check_timestamp' => now(),
            'check_type' => $alertType,
            'check_status' => 'FAILED',
            'check_details' => json_encode($data),
            'affected_records' => $data['total_errors'] ?? 1,
            'severity' => 'CRITICAL'
        ]);
    }

    /**
     *  ENHANCED ACCESS VALIDATION WITH ROLE-BASED PERMISSIONS
     */
    private function validateEnhancedAccess(): array
    {
        $user = auth()->user();

        if (!$user || !$user->is_active) {
            return [
                'granted' => false,
                'reason' => 'USER_INACTIVE',
                'user_id' => $user->id ?? null
            ];
        }

        $allowedRoles = ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER', 'DELIVERY_SUPERVISOR', 'AUDITOR'];

        if (!in_array($user->role, $allowedRoles)) {
            return [
                'granted' => false,
                'reason' => 'INSUFFICIENT_ROLE',
                'user_role' => $user->role,
                'required_roles' => $allowedRoles
            ];
        }

        // Additional security checks
        $sessionValid = $this->validateUserSession($user);
        if (!$sessionValid) {
            return [
                'granted' => false,
                'reason' => 'INVALID_SESSION'
            ];
        }

        return [
            'granted' => true,
            'user_id' => $user->id,
            'role' => $user->role,
            'security_clearance' => $user->security_clearance_level
        ];
    }

    /**
     *  SYSTEM HEALTH VALIDATION WITH AUTOMATION STATUS
     */
    private function validateSystemHealth(): array
    {
        try {
            // Check automation configurations
            $configs = DB::table('system_configurations')
                ->whereIn('config_key', [
                    'ENHANCED_FIFO_PROCESSING_ENABLED',
                    'ENHANCED_MONITORING_ENABLED',
                    'AUTO_DELIVERY_LAYER_CREATION',
                    'ENHANCED_CLEANUP_ENABLED'
                ])
                ->pluck('config_value_boolean', 'config_key');

            // Check recent monitoring results
            $recentMonitoring = DB::table('system_health_monitoring')
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(30))
                ->whereIn('check_type', [
                    'ENHANCED_FIFO_MATH_CHECK',
                    'ENHANCED_VALUE_CALC_CHECK',
                    'ENHANCED_CAPACITY_CHECK'
                ])
                ->get();

            // Check for critical alerts
            $criticalAlerts = DB::table('system_accuracy_alerts')
                ->where('resolved_at', null)
                ->where('severity', 'CRITICAL')
                ->count();

            $automationEnabled = $configs->every(function($value) { return $value == 1; });
            $recentFailures = $recentMonitoring->where('check_status', 'FAILED')->count();

            $healthScore = 100;
            if ($criticalAlerts > 0) $healthScore -= 50;
            if ($recentFailures > 0) $healthScore -= ($recentFailures * 10);
            if (!$automationEnabled) $healthScore -= 30;

            return [
                'healthy' => $healthScore >= 70,
                'score' => max(0, $healthScore),
                'automation_enabled' => $automationEnabled,
                'critical_alerts' => $criticalAlerts,
                'recent_failures' => $recentFailures,
                'configs_status' => $configs->toArray()
            ];

        } catch (Exception $e) {
            return [
                'healthy' => false,
                'score' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     *  GET USER CONTEXT WITH ENHANCED SECURITY
     */
    private function getUserContextSecure(): array
    {
        $user = auth()->user();
        $userRole = $user->role;

        // Get user stations with enhanced validation
        if (in_array($userRole, ['CEO', 'SYSTEM_ADMIN'])) {
            $stations = DB::table('stations')
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();
        } else {
            $stations = DB::table('user_stations')
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->pluck('station_id')
                ->toArray();
        }

        return [
            'user_id' => $user->id,
            'role' => $userRole,
            'stations' => $stations,
            'security_clearance' => $user->security_clearance_level,
            'can_approve_variances' => $user->can_approve_variances,
            'can_access_financial_data' => $user->can_access_financial_data,
            'max_approval_amount' => $user->max_approval_amount
        ];
    }

    /**
     *  EXECUTE SYSTEM MONITORING FOR REAL-TIME STATUS
     */
    private function executeSystemMonitoring(): void
    {
        try {
            DB::statement('CALL sp_enhanced_system_monitor()');
        } catch (Exception $e) {
            Log::error('System monitoring failed: ' . $e->getMessage());

            // Log to system health monitoring manually
            DB::table('system_health_monitoring')->insert([
                'check_timestamp' => now(),
                'check_type' => 'MANUAL_MONITORING_FAILURE',
                'check_status' => 'FAILED',
                'check_details' => 'Manual monitoring execution failed: ' . $e->getMessage(),
                'severity' => 'HIGH'
            ]);
        }
    }

    /**
     *  GET STATION DATA WITH FIFO INTEGRATION - EXACT SCHEMA FIELDS
     */
    private function getStationDataWithFIFO(array $userStations, bool $isAutoApproved): array
    {
        $query = DB::table('stations')
            ->join('tanks', 'stations.id', '=', 'tanks.station_id')
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->leftJoin('tank_inventory_layers', function($join) {
                $join->on('tanks.id', '=', 'tank_inventory_layers.tank_id')
                     ->where('tank_inventory_layers.is_depleted', 0);
            })
            ->where('stations.is_active', 1)
            ->where('tanks.is_active', 1);

        if (!$isAutoApproved && !empty($userStations)) {
            $query->whereIn('stations.id', $userStations);
        }

        $results = $query->select([
            'stations.id as station_id',
            'stations.station_name',
            'stations.station_code',
            'tanks.id as tank_id',
            'tanks.tank_number',
            'tanks.capacity_liters',
            'tanks.minimum_stock_level_liters',
            'tanks.critical_low_level_liters',
            'products.product_name',
            'products.product_type',
            DB::raw('COALESCE(SUM(tank_inventory_layers.current_quantity_liters), 0) as current_quantity'),
            DB::raw('COALESCE(SUM(tank_inventory_layers.remaining_layer_value), 0) as current_value'),
            DB::raw('COUNT(tank_inventory_layers.id) as active_layers')
        ])
        ->groupBy([
            'stations.id', 'stations.station_name', 'stations.station_code',
            'tanks.id', 'tanks.tank_number', 'tanks.capacity_liters',
            'tanks.minimum_stock_level_liters', 'tanks.critical_low_level_liters',
            'products.product_name', 'products.product_type'
        ])
        ->get();

        return $results->groupBy('station_id')->map(function($tanks, $stationId) {
            $firstTank = $tanks->first();

            return [
                'station_id' => $stationId,
                'station_name' => $firstTank->station_name,
                'station_code' => $firstTank->station_code,
                'tank_count' => $tanks->count(),
                'tanks' => $tanks->map(function($tank) {
                    $fillPercentage = $tank->capacity_liters > 0
                        ? round($tank->current_quantity / $tank->capacity_liters * 100, 2)
                        : 0;

                    return [
                        'tank_id' => $tank->tank_id,
                        'tank_number' => $tank->tank_number,
                        'capacity_liters' => round($tank->capacity_liters, 3),
                        'product_name' => $tank->product_name,
                        'product_type' => $tank->product_type,
                        'current_quantity' => round($tank->current_quantity, 3),
                        'current_value' => round($tank->current_value, 2),
                        'active_layers' => $tank->active_layers,
                        'fill_percentage' => $fillPercentage,
                        'stock_status' => $this->getStockStatus($tank),
                        'minimum_level' => round($tank->minimum_stock_level_liters, 3),
                        'critical_level' => round($tank->critical_low_level_liters, 3)
                    ];
                })->toArray(),
                'total_quantity' => round($tanks->sum('current_quantity'), 3),
                'total_value' => round($tanks->sum('current_value'), 2),
                'total_active_layers' => $tanks->sum('active_layers')
            ];
        })->toArray();
    }

    /**
     *  CALCULATE PRECISION METRICS WITH MATHEMATICAL ACCURACY
     */
    private function calculatePrecisionMetrics(array $stationData): array
    {
        $totalStations = count($stationData);
        $totalTanks = 0;
        $totalQuantity = 0.0;
        $totalValue = 0.0;
        $totalActiveLayers = 0;
        $criticalLowStations = 0;
        $overCapacityTanks = 0;

        foreach ($stationData as $station) {
            $totalTanks += $station['tank_count'];
            $totalQuantity += $station['total_quantity'];
            $totalValue += $station['total_value'];
            $totalActiveLayers += $station['total_active_layers'];

            foreach ($station['tanks'] as $tank) {
                if ($tank['current_quantity'] <= $tank['critical_level']) {
                    $criticalLowStations++;
                }
                if ($tank['fill_percentage'] > 100) {
                    $overCapacityTanks++;
                }
            }
        }

        return [
            'total_stations' => $totalStations,
            'total_tanks' => $totalTanks,
            'total_inventory_quantity' => round($totalQuantity, 3),
            'total_inventory_value' => round($totalValue, 2),
            'total_active_layers' => $totalActiveLayers,
            'average_value_per_liter' => $totalQuantity > 0 ? round($totalValue / $totalQuantity, 4) : 0,
            'critical_low_tanks' => $criticalLowStations,
            'over_capacity_tanks' => $overCapacityTanks,
            'system_utilization_percentage' => $totalTanks > 0 ? round(($totalActiveLayers / $totalTanks) * 100, 2) : 0
        ];
    }

    /**
     *  GET RECENT ACTIVITIES WITH AUTOMATION STATUS - EXACT SCHEMA FIELDS
     */
    private function getRecentActivitiesWithStatus(array $userStations, bool $isAutoApproved): array
    {
        $query = DB::table('readings as r')
            ->join('tanks as t', 'r.tank_id', '=', 't.id')
            ->join('stations as s', 't.station_id', '=', 's.id')
            ->join('users as u', 'r.entered_by', '=', 'u.id')
            ->leftJoin('products as p', 't.product_id', '=', 'p.id')
            ->where('r.reading_date', '>=', Carbon::now()->subDays(7));

        if (!$isAutoApproved && !empty($userStations)) {
            $query->whereIn('s.id', $userStations);
        }

        return $query->select([
            'r.id as reading_id',
            'r.reading_date',
            'r.reading_time',
            'r.reading_type',
            'r.reading_shift',
            'r.reading_status',
            'r.calculated_sales_liters',
            'r.variance_from_expected_percentage',
            't.tank_number',
            's.station_name',
            'p.product_type',
            'u.first_name',
            'u.last_name',
            'r.created_at'
        ])
        ->orderBy('r.created_at', 'desc')
        ->limit(50)
        ->get()
        ->map(function($activity) {
            return [
                'reading_id' => $activity->reading_id,
                'reading_date' => $activity->reading_date,
                'reading_time' => $activity->reading_time,
                'reading_type' => $activity->reading_type,
                'reading_shift' => $activity->reading_shift,
                'reading_status' => $activity->reading_status,
                'calculated_sales_liters' => round($activity->calculated_sales_liters ?? 0, 3),
                'variance_percentage' => round($activity->variance_from_expected_percentage ?? 0, 3),
                'tank_number' => $activity->tank_number,
                'station_name' => $activity->station_name,
                'product_type' => $activity->product_type,
                'entered_by' => $activity->first_name . ' ' . $activity->last_name,
                'created_at' => $activity->created_at,
                'automation_triggered' => in_array($activity->reading_type, ['ENHANCED_METER_AUTO', 'DELIVERY_AFTER'])
            ];
        })
        ->toArray();
    }

    /**
     *  GET VALIDATED VALUATIONS - EXACT SCHEMA FIELDS
     */
    private function getValidatedValuations(array $userStations, bool $isAutoApproved): array
    {
        $query = DB::table('inventory_valuations as iv')
            ->join('stations as s', 'iv.station_id', '=', 's.id')
            ->join('users as u', 'iv.created_by', '=', 'u.id')
            ->where('iv.valuation_date', '>=', Carbon::now()->subDays(30));

        if (!$isAutoApproved && !empty($userStations)) {
            $query->whereIn('s.id', $userStations);
        }

        return $query->select([
            'iv.id as valuation_id',
            'iv.valuation_date',
            'iv.valuation_time',
            'iv.valuation_type',
            'iv.total_fuel_quantity_liters',
            'iv.total_fuel_value',
            'iv.valuation_method',
            'iv.valuation_variance_percentage',
            's.station_name',
            'u.first_name',
            'u.last_name',
            'iv.created_at'
        ])
        ->orderBy('iv.created_at', 'desc')
        ->limit(20)
        ->get()
        ->map(function($valuation) {
            return [
                'valuation_id' => $valuation->valuation_id,
                'valuation_date' => $valuation->valuation_date,
                'valuation_time' => $valuation->valuation_time,
                'valuation_type' => $valuation->valuation_type,
                'total_quantity' => round($valuation->total_fuel_quantity_liters, 3),
                'total_value' => round($valuation->total_fuel_value, 2),
                'valuation_method' => $valuation->valuation_method,
                'variance_percentage' => round($valuation->valuation_variance_percentage ?? 0, 3),
                'station_name' => $valuation->station_name,
                'created_by' => $valuation->first_name . ' ' . $valuation->last_name,
                'created_at' => $valuation->created_at
            ];
        })
        ->toArray();
    }

    /**
     *  COMPREHENSIVE AUDIT LOGGING WITH HASH CHAIN - EXACT SCHEMA FIELDS
     */
    private function logInventoryActionSecure(string $actionType, int $recordId, array $details): void
    {
        try {
            $previousHash = DB::table('audit_logs')
                ->orderBy('id', 'desc')
                ->value('hash_current');

            $auditData = [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'station_id' => $details['station_id'] ?? null,
                'action_type' => 'READ',
                'table_name' => 'tank_inventory_layers',
                'record_id' => $recordId,
                'details' => $details,
                'timestamp' => now()->format('Y-m-d H:i:s.u'),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];

            $hashData = json_encode($auditData, JSON_UNESCAPED_UNICODE);
            $currentHash = hash('sha256', ($previousHash ?? '') . $hashData);

            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'station_id' => $details['station_id'] ?? null,
                'action_type' => 'READ',
                'action_category' => self::AUDIT_CATEGORIES[$actionType] ?? 'REPORTING',
                'table_name' => 'tank_inventory_layers',
                'record_id' => $recordId,
                'new_value_text' => json_encode($details, JSON_UNESCAPED_UNICODE),
                'change_reason' => 'Inventory management operation: ' . $actionType,
                'business_justification' => $actionType,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'request_method' => request()->method(),
                'request_url' => request()->fullUrl(),
                'response_status_code' => 200,
                'timestamp' => now(),
                'hash_previous' => $previousHash,
                'hash_current' => $currentHash,
                'hash_data' => $hashData,
                'hash_algorithm' => 'SHA256',
                'risk_level' => 'LOW',
                'sensitivity_level' => 'INTERNAL',
                'compliance_category' => 'OPERATIONAL',
                'system_generated' => 0,
                'batch_operation' => 0,
                'error_occurred' => 0
            ]);

        } catch (Exception $e) {
            Log::error('Audit logging failed: ' . $e->getMessage(), [
                'action_type' => $actionType,
                'record_id' => $recordId,
                'user_id' => auth()->id()
            ]);

            // Fallback logging to system health monitoring
            DB::table('system_health_monitoring')->insert([
                'check_timestamp' => now(),
                'check_type' => 'AUDIT_LOGGING_FAILURE',
                'check_status' => 'FAILED',
                'check_details' => 'Audit logging failed: ' . $e->getMessage(),
                'severity' => 'HIGH'
            ]);
        }
    }

    /**
     *  HANDLE CRITICAL ERRORS WITH FULL ROLLBACK
     */
    private function handleCriticalError(Exception $e, string $errorType, string $operationId): \Illuminate\Http\RedirectResponse
    {
        Log::error("Critical inventory error: {$errorType}", [
            'operation_id' => $operationId,
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip()
        ]);

        // Log to system health monitoring
        DB::table('system_health_monitoring')->insert([
            'check_timestamp' => now(),
            'check_type' => $errorType,
            'check_status' => 'FAILED',
            'check_details' => $e->getMessage(),
            'severity' => 'CRITICAL'
        ]);

        return redirect()->back()->with('error', 'A critical system error occurred. The operation has been safely rolled back. Please contact system administrator.');
    }

    /**
     *  HANDLE CRITICAL SYSTEM ERROR
     */
    private function handleCriticalSystemError(string $errorType, array $systemHealth): \Illuminate\Http\RedirectResponse
    {
        Log::critical("Critical system error: {$errorType}", $systemHealth);

        // Log to system health monitoring
        DB::table('system_health_monitoring')->insert([
            'check_timestamp' => now(),
            'check_type' => $errorType,
            'check_status' => 'FAILED',
            'check_details' => json_encode($systemHealth),
            'severity' => 'CRITICAL'
        ]);

        return redirect()->back()->with('error', 'Critical system failure detected. Automation systems may be offline. Contact system administrator immediately.');
    }

    /**
     *  LOG SECURITY VIOLATION
     */
    private function logSecurityViolation(string $violationType, int $recordId, array $details): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'action_type' => 'read',
            'action_category' => 'SECURITY',
            'table_name' => 'inventory_access_attempt',
            'record_id' => $recordId,
            'new_value_text' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'change_reason' => $violationType,
            'business_justification' => 'Security violation detected',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'hash_current' => hash('sha256', json_encode($details) . now()),
            'hash_data' => json_encode($details),
            'risk_level' => 'HIGH',
            'sensitivity_level' => 'RESTRICTED',
            'compliance_category' => 'SECURITY'
        ]);
    }

    /**
     *  LOG PERFORMANCE ALERT
     */
    private function logPerformanceAlert(string $alertType, array $metrics): void
    {
        DB::table('system_health_monitoring')->insert([
            'check_timestamp' => now(),
            'check_type' => $alertType,
            'check_status' => 'WARNING',
            'check_details' => json_encode($metrics),
            'severity' => 'MEDIUM'
        ]);
    }

    /**
     *  HELPER METHODS WITH EXACT SCHEMA COMPLIANCE
     */

    private function hasAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->is_active && in_array($user->role, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER', 'DELIVERY_SUPERVISOR', 'AUDITOR']);
    }

    private function getStockStatus($tank): string
    {
        if ($tank->current_quantity <= $tank->critical_low_level_liters) return 'CRITICAL';
        if ($tank->current_quantity <= $tank->minimum_stock_level_liters) return 'LOW';
        if ($tank->current_quantity >= $tank->capacity_liters * 0.9) return 'HIGH';
        return 'NORMAL';
    }

    private function validateUserSession($user): bool
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>', time() - 7200)
            ->exists();
    }

    private function validateTankAccess(int $tankId): array
    {
        $tank = DB::table('tanks')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->where('tanks.id', $tankId)
            ->where('tanks.is_active', 1)
            ->select([
                'tanks.*',
                'stations.station_name',
                'products.product_name',
                'products.product_type'
            ])
            ->first();

        if (!$tank) {
            return [
                'granted' => false,
                'message' => 'Tank not found or inactive'
            ];
        }

        // Check user access to station
        $userContext = $this->getUserContextSecure();
        if (!in_array($userContext['role'], ['CEO', 'SYSTEM_ADMIN']) &&
            !in_array($tank->station_id, $userContext['stations'])) {
            return [
                'granted' => false,
                'message' => 'Access denied to this tank'
            ];
        }

        return [
            'granted' => true,
            'tank' => $tank
        ];
    }

    private function validateStationAccess(int $stationId): array
    {
        $station = DB::table('stations')
            ->where('id', $stationId)
            ->where('is_active', 1)
            ->first();

        if (!$station) {
            return [
                'granted' => false,
                'message' => 'Station not found or inactive'
            ];
        }

        $userContext = $this->getUserContextSecure();
        if (!in_array($userContext['role'], ['CEO', 'SYSTEM_ADMIN']) &&
            !in_array($stationId, $userContext['stations'])) {
            return [
                'granted' => false,
                'message' => 'Access denied to this station'
            ];
        }

        return [
            'granted' => true,
            'station' => $station
        ];
    }
}
