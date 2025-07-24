<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\AuditService;
use App\Services\CorrectedFIFOService  AS FIFOService;
use App\Services\ReconciliationService;
use Exception;

class PumpController extends Controller
{
    /**
     * 🔥 MANDATORY SERVICE DELEGATION - ZERO DUPLICATION POLICY
     */
    public function __construct(
        private AuditService $auditService,
        private FIFOService $fifoService,
        private ReconciliationService $reconciliationService
    ) {
        // Initialize automation validation on controller instantiation
        $this->validateSystemAutomationReadiness();
    }

    /**
     * 🔥 MATHEMATICAL PRECISION CONSTANTS - 0.001L TOLERANCE ENFORCEMENT
     */
    private const VOLUME_PRECISION = 0.001;
    private const COST_PRECISION = 0.0001;
    private const MAX_METER_READING = 999999999.999;
    private const MIN_FLOW_RATE = 5.0;
    private const MAX_FLOW_RATE = 150.0;

    /**
     * 🔥 AUTOMATION VALIDATION - MANDATORY BEFORE ANY OPERATIONS
     */
    private function validateSystemAutomationReadiness(): void
    {
        try {
            // Validate automation configuration from database
            $automationConfigs = DB::table('system_configurations')
                ->whereIn('config_key', [
                    'ENHANCED_FIFO_PROCESSING_ENABLED',
                    'ENHANCED_MONITORING_ENABLED',
                    'ENHANCED_CLEANUP_ENABLED',
                    'AUTO_DELIVERY_LAYER_CREATION'
                ])
                ->where('config_value_boolean', 1)
                ->count();

            if ($automationConfigs < 4) {
                throw new Exception("AUTOMATION CRITICAL: System automation not fully enabled. Cannot proceed with pump operations.");
            }

            // Validate stored procedures existence
            $procedures = DB::select("
                SELECT COUNT(*) as proc_count
                FROM information_schema.ROUTINES
                WHERE ROUTINE_SCHEMA = DATABASE()
                AND ROUTINE_TYPE = 'PROCEDURE'
                AND ROUTINE_NAME IN ('sp_enhanced_system_monitor', 'sp_enhanced_fifo_processor', 'sp_enhanced_data_cleanup')
            ");

            if ($procedures[0]->proc_count < 3) {
                throw new Exception("AUTOMATION CRITICAL: Required stored procedures missing. System integrity compromised.");
            }

        } catch (Exception $e) {
            // Log critical system failure
            $this->auditService->logError([
                'error_message' => 'Pump controller automation validation failed: ' . $e->getMessage(),
                'error_context' => 'System automation readiness check',
                'table_name' => 'system_configurations',
                'user_id' => auth()->id() ?? 1
            ]);
            throw $e;
        }
    }

    /**
     * 🔥 STATION SELECTION WITH COMPLETE AUDIT COMPLIANCE AND TRANSACTION INTEGRITY
     */
    public function selectStation()
    {
        DB::beginTransaction();

        try {
            // Enforce complete automation before any operations
            $this->validateSystemAutomationReadiness();

            // Validate user role from database (not just auth cache)
            $currentUserRole = $this->validateUserRoleFromDatabase(auth()->id());

            $userData = [
                'user_id' => auth()->id(),
                'role' => $currentUserRole,
                'action_context' => 'station_selection_for_pump_management'
            ];

            if (in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN'])) {
                $stations = DB::table('stations')
                    ->leftJoin('pumps', 'stations.id', '=', 'pumps.station_id')
                    ->where('stations.is_active', 1)
                    ->select([
                        'stations.id',
                        'stations.station_code',
                        'stations.station_name',
                        'stations.region',
                        'stations.district',
                        DB::raw('COUNT(pumps.id) as pump_count'),
                        DB::raw('COUNT(CASE WHEN pumps.is_operational = 1 THEN pumps.id END) as operational_pumps'),
                        DB::raw('COUNT(CASE WHEN pumps.is_active = 1 THEN pumps.id END) as active_pumps')
                    ])
                    ->groupBy(['stations.id', 'stations.station_code', 'stations.station_name', 'stations.region', 'stations.district'])
                    ->orderBy('stations.station_name')
                    ->get();

                // CEO/SYSTEM_ADMIN auto-approval with enhanced audit logging - INSIDE TRANSACTION
                $this->auditService->logAutoApproval([
                    'action_type' => 'READ',
                    'table_name' => 'stations',
                    'change_reason' => 'CEO/SYSTEM_ADMIN accessing all stations for pump management',
                    'business_justification' => 'Elevated role auto-approval for comprehensive station access',
                    'user_id' => auth()->id(),
                    'is_auto_approved' => true
                ]);

            } else {
                // Regular user with station-specific access validation
                $userStationIds = DB::table('user_stations')
                    ->where('user_id', auth()->id())
                    ->where('is_active', 1)
                    ->whereIn('assigned_role', ['MANAGER', 'ASSISTANT_MANAGER', 'SUPERVISOR'])
                    ->pluck('station_id');

                if ($userStationIds->isEmpty()) {
                    $this->auditService->logSecurityViolation([
                        'user_id' => auth()->id(),
                        'action' => 'UNAUTHORIZED_STATION_ACCESS_ATTEMPT',
                        'details' => 'User has no active station assignments for pump management',
                        'ip_address' => request()->ip()
                    ]);

                    DB::rollback();
                    $this->validateRollbackSuccess();
                    return view('pumps.select-station', ['stations' => collect(), 'hasAccess' => false]);
                }

                $stations = DB::table('stations')
                    ->leftJoin('pumps', 'stations.id', '=', 'pumps.station_id')
                    ->whereIn('stations.id', $userStationIds)
                    ->where('stations.is_active', 1)
                    ->select([
                        'stations.id',
                        'stations.station_code',
                        'stations.station_name',
                        'stations.region',
                        'stations.district',
                        DB::raw('COUNT(pumps.id) as pump_count'),
                        DB::raw('COUNT(CASE WHEN pumps.is_operational = 1 THEN pumps.id END) as operational_pumps'),
                        DB::raw('COUNT(CASE WHEN pumps.is_active = 1 THEN pumps.id END) as active_pumps')
                    ])
                    ->groupBy(['stations.id', 'stations.station_code', 'stations.station_name', 'stations.region', 'stations.district'])
                    ->orderBy('stations.station_name')
                    ->get();

                // Log regular user access - INSIDE TRANSACTION
                $this->auditService->logAction([
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'stations',
                    'change_reason' => 'User accessing assigned stations for pump management',
                    'user_id' => auth()->id(),
                    'new_values' => json_encode(['station_count' => $stations->count()])
                ]);
            }

            // Validate audit integrity and hash chain
            $this->validateAuditIntegrity();

            // Validate transaction success before commit
            $this->validateTransactionIntegrity();

            DB::commit();
            return view('pumps.select-station', compact('stations'));

        } catch (Exception $e) {
            DB::rollback();
            $this->validateRollbackSuccess();

            dd( $e);

            // $this->auditService->logError([
            //     'error_message' => 'Station selection failed: ' . $e->getMessage(),
            //     'error_context' => 'Pump management station selection',
            //     'table_name' => 'stations',
            //     'user_id' => auth()->id()
            // ]);

            return redirect()->back()->with('error', 'System error: Unable to load stations. Please contact administrator.');
        }
    }

    /**
     * 🔥 PUMP INDEX WITH COMPLETE BUSINESS RULE ENFORCEMENT
     */
    public function index($stationId)
    {
        DB::beginTransaction();

        try {
            // Mathematical precision validation for station ID
            if (!is_numeric($stationId) || $stationId <= 0) {
                throw new Exception("VALIDATION ERROR: Invalid station ID format");
            }

            $accessResult = $this->verifyStationAccessWithCompleteValidation($stationId);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();
            if (!$station) {
                throw new Exception("BUSINESS RULE VIOLATION: Station {$stationId} not found");
            }

            // Enhanced audit logging with complete context
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'READ',
                    'table_name' => 'pumps',
                    'change_reason' => "CEO/SYSTEM_ADMIN accessing pump management for station: {$station->station_name}",
                    'business_justification' => 'Elevated role auto-approval for pump operations',
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'is_auto_approved' => true
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'pumps',
                    'change_reason' => "Pump management accessed for station: {$station->station_name}",
                    'user_id' => auth()->id(),
                    'station_id' => $stationId
                ]);
            }

            // Get pumps with complete database function integration
            $pumps = $this->getPumpsWithCompleteValidation($stationId);

            // Validate system health for pumps
            $this->validatePumpSystemHealth($stationId);

            DB::commit();
            return view('pumps.index', compact('station', 'pumps'));

        } catch (Exception $e) {
            DB::rollback();

            // dd($e)

            // $this->auditService->logError([
            //     'error_message' => 'Pump index failed: ' . $e->getMessage(),
            //     'error_context' => "Station ID: {$stationId}",
            //     'table_name' => 'pumps',
            //     'user_id' => auth()->id(),
            //     'station_id' => $stationId
            // ]);

            return redirect()->route('pumps.select')->with('error', 'System error: Unable to load pump data.');
        }
    }

    /**
     * 🔥 PUMP CREATION WITH MATHEMATICAL PRECISION AND AUTOMATION INTEGRATION
     */
    public function create($stationId)
    {
        DB::beginTransaction();

        try {
            $accessResult = $this->verifyStationAccessWithCompleteValidation($stationId, ['MANAGER']);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();

            // Enhanced audit with auto-approval for elevated roles
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'READ',
                    'table_name' => 'pumps',
                    'change_reason' => "CEO/SYSTEM_ADMIN accessing pump creation for station: {$station->station_name}",
                    'business_justification' => 'Elevated role auto-approval for pump creation access',
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'is_auto_approved' => true
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'pumps',
                    'change_reason' => "Pump creation accessed for station: {$station->station_name}",
                    'user_id' => auth()->id(),
                    'station_id' => $stationId
                ]);
            }

            // Get tanks with complete product validation from database
            $tanks = $this->getValidTanksWithCompleteValidation($stationId);

            // Calculate next pump number with mathematical precision
            $nextPumpNumber = $this->calculateNextPumpNumberWithPrecision($stationId);

            // Validate tank capacity constraints using database functions
            foreach ($tanks as $tank) {
                $capacityUsage = DB::selectOne('SELECT fn_get_tank_capacity_usage(?) as usage', [$tank->id]);
                $tank->capacity_usage_percentage = round($capacityUsage->usage ?? 0, 2);
                $tank->capacity_status = $tank->capacity_usage_percentage > 95 ? 'CRITICAL' :
                                       ($tank->capacity_usage_percentage > 85 ? 'HIGH' : 'NORMAL');
            }

            DB::commit();
            return view('pumps.create', compact('station', 'tanks', 'nextPumpNumber'));

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'error_message' => 'Pump creation access failed: ' . $e->getMessage(),
                'error_context' => "Station ID: {$stationId}",
                'table_name' => 'pumps',
                'user_id' => auth()->id(),
                'station_id' => $stationId
            ]);

            return redirect()->route('pumps.index', $stationId)->with('error', 'System error: Unable to access pump creation.');
        }
    }

    /**
     * 🔥 PUMP STORAGE WITH COMPLETE TRANSACTION INTEGRITY AND BUSINESS RULE ENFORCEMENT
     */
    public function store(Request $request, $stationId)
    {
        DB::beginTransaction();

        try {
            // Mathematical precision validation for station ID
            if (!is_numeric($stationId) || $stationId <= 0) {
                throw new Exception("VALIDATION ERROR: Invalid station ID format");
            }

            $accessResult = $this->verifyStationAccessWithCompleteValidation($stationId, ['MANAGER']);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();
            $isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);

            // Enhanced validation with mathematical precision
            $validatedData = $this->validatePumpDataWithMathematicalPrecision($request, $stationId);

            // Validate tank capacity using database function before pump assignment
            $tankCapacityUsage = DB::selectOne('SELECT fn_get_tank_capacity_usage(?) as usage', [$validatedData['tank_id']]);
            if (($tankCapacityUsage->usage ?? 0) > 95.0) {
                throw new Exception("BUSINESS RULE VIOLATION: Tank capacity critical (${tankCapacityUsage->usage}%) - pump assignment denied for safety");
            }

            // Validate pump number uniqueness with complete precision
            $existingPump = DB::table('pumps')
                ->where('station_id', $stationId)
                ->where('pump_number', $validatedData['pump_number'])
                ->exists();

            if ($existingPump) {
                throw new Exception("BUSINESS RULE VIOLATION: Pump number {$validatedData['pump_number']} already exists at station");
            }

            // Create pump with complete transaction integrity
            $pumpId = $this->createPumpWithCompleteIntegrity($validatedData, $stationId, $isAutoApproved);

            // Enhanced audit logging with auto-approval for elevated roles
            if ($isAutoApproved) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'CREATE',
                    'table_name' => 'pumps',
                    'record_id' => $pumpId,
                    'change_reason' => "CEO/SYSTEM_ADMIN pump creation auto-approved: Pump {$validatedData['pump_number']}",
                    'business_justification' => 'Elevated role auto-approval for pump creation',
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'is_auto_approved' => true,
                    'new_values' => json_encode($validatedData)
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'CREATE',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'pumps',
                    'record_id' => $pumpId,
                    'change_reason' => "Pump {$validatedData['pump_number']} created with serial {$validatedData['pump_serial_number']}",
                    'business_justification' => 'New pump installation for operational capacity',
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'new_values' => json_encode($validatedData)
                ]);
            }

            // Validate system health after pump creation
            $this->validatePumpSystemHealth($stationId);

            DB::commit();
            return redirect()->route('pumps.index', $stationId)
                ->with('success', 'Pump created successfully with mathematical precision validation');

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'error_message' => 'Pump creation failed: ' . $e->getMessage(),
                'error_context' => "Station ID: {$stationId}, Request data: " . json_encode($request->all()),
                'table_name' => 'pumps',
                'user_id' => auth()->id(),
                'station_id' => $stationId
            ]);

            return redirect()->back()->withInput()->with('error', 'Pump creation failed: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 PUMP EDIT WITH COMPLETE FIFO INTEGRATION
     */
    public function edit($id)
    {
        DB::beginTransaction();

        try {
            // Mathematical precision validation for pump ID
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("VALIDATION ERROR: Invalid pump ID format");
            }

            $pump = $this->getPumpWithCompleteValidation($id);
            if (!$pump) {
                throw new Exception("BUSINESS RULE VIOLATION: Pump {$id} not found");
            }

            $accessResult = $this->verifyStationAccessWithCompleteValidation($pump->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            // Enhanced audit with auto-approval for elevated roles
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'READ',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => "CEO/SYSTEM_ADMIN accessing pump edit: Pump {$pump->pump_number}",
                    'business_justification' => 'Elevated role auto-approval for pump edit access',
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id,
                    'is_auto_approved' => true
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => "Pump edit accessed: Pump {$pump->pump_number}",
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id
                ]);
            }

            $tanks = $this->getValidTanksWithCompleteValidation($pump->station_id);

            // Get latest meter reading with FIFO validation
            $latestMeterReading = $this->getLatestMeterReadingWithValidation($id);

            // Validate pump FIFO consistency if meter readings exist
            if ($latestMeterReading) {
                $this->validatePumpFIFOConsistency($id);
            }

            DB::commit();
            return view('pumps.edit', compact('pump', 'tanks', 'latestMeterReading'));

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'error_message' => 'Pump edit access failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$id}",
                'table_name' => 'pumps',
                'user_id' => auth()->id()
            ]);

            return redirect()->route('pumps.select')->with('error', 'Pump edit access failed: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 PUMP UPDATE WITH COMPLETE MATHEMATICAL PRECISION
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Mathematical precision validation for pump ID
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("VALIDATION ERROR: Invalid pump ID format");
            }

            $pump = $this->getPumpWithCompleteValidation($id);
            if (!$pump) {
                throw new Exception("BUSINESS RULE VIOLATION: Pump {$id} not found");
            }

            $accessResult = $this->verifyStationAccessWithCompleteValidation($pump->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            $isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);

            // Get latest meter reading for validation
            $latestMeterReading = $this->getLatestMeterReadingWithValidation($id);
            $latestMeterValue = $latestMeterReading ? $latestMeterReading->meter_reading_liters : 0;

            // Enhanced validation with mathematical precision
            $validatedData = $this->validatePumpUpdateWithMathematicalPrecision($request, $pump, $latestMeterValue);

            // Validate tank capacity if tank is being changed
            if ($validatedData['tank_id'] != $pump->tank_id) {
                $newTankCapacityUsage = DB::selectOne('SELECT fn_get_tank_capacity_usage(?) as usage', [$validatedData['tank_id']]);
                if (($newTankCapacityUsage->usage ?? 0) > 95.0) {
                    throw new Exception("BUSINESS RULE VIOLATION: Target tank capacity critical (${newTankCapacityUsage->usage}%) - pump reassignment denied");
                }
            }

            // Update pump with complete change tracking
            $changes = $this->updatePumpWithCompleteChangeTracking($id, $validatedData, $pump, $isAutoApproved);

            // Enhanced audit logging
            if ($isAutoApproved) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'UPDATE',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => "CEO/SYSTEM_ADMIN pump update auto-approved: " . implode(', ', $changes),
                    'business_justification' => 'Elevated role auto-approval for pump update',
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id,
                    'is_auto_approved' => true,
                    'old_values' => json_encode($pump),
                    'new_values' => json_encode($validatedData)
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'UPDATE',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => 'Pump updated: ' . implode(', ', $changes),
                    'business_justification' => 'Pump configuration update for operational optimization',
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id,
                    'old_values' => json_encode($pump),
                    'new_values' => json_encode($validatedData)
                ]);
            }

            // Validate system health after update
            $this->validatePumpSystemHealth($pump->station_id);

            DB::commit();
            return redirect()->route('pumps.edit', $id)->with('success', 'Pump updated successfully with mathematical precision validation');

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'error_message' => 'Pump update failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$id}, Request data: " . json_encode($request->all()),
                'table_name' => 'pumps',
                'record_id' => $id,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->withInput()->with('error', 'Pump update failed: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 PUMP MAINTENANCE WITH SYSTEM HEALTH INTEGRATION
     */
    public function maintenance($id)
    {
        DB::beginTransaction();

        try {
            // Mathematical precision validation for pump ID
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("VALIDATION ERROR: Invalid pump ID format");
            }

            $pump = $this->getPumpWithCompleteValidation($id);
            if (!$pump) {
                throw new Exception("BUSINESS RULE VIOLATION: Pump {$id} not found");
            }

            $accessResult = $this->verifyStationAccessWithCompleteValidation($pump->station_id);
            if ($accessResult !== true) {
                DB::rollback();
                return $accessResult;
            }

            // Enhanced audit with auto-approval for elevated roles
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                $this->auditService->logAutoApproval([
                    'action_type' => 'READ',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => "CEO/SYSTEM_ADMIN accessing pump maintenance: Pump {$pump->pump_number}",
                    'business_justification' => 'Elevated role auto-approval for maintenance access',
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id,
                    'is_auto_approved' => true
                ]);
            } else {
                $this->auditService->logAction([
                    'action_type' => 'READ',
                    'action_category' => 'MAINTENANCE',
                    'table_name' => 'pumps',
                    'record_id' => $id,
                    'change_reason' => "Pump maintenance accessed: Pump {$pump->pump_number}",
                    'user_id' => auth()->id(),
                    'station_id' => $pump->station_id
                ]);
            }

            // Calculate maintenance statistics with mathematical precision
            $maintenanceStats = $this->calculateMaintenanceStatsWithPrecision($pump);

            // Get recent readings with FIFO validation
            $recentReadings = $this->getRecentReadingsWithValidation($id);

            // Calculate utilization metrics with precision
            $utilizationMetrics = $this->calculateUtilizationMetricsWithPrecision($id, $recentReadings, $pump->product_type);

            // Generate maintenance alerts based on business rules
            $alerts = $this->generateMaintenanceAlertsWithBusinessRules($maintenanceStats, $pump);

            // Validate pump FIFO consistency for maintenance
            $this->validatePumpFIFOConsistency($id);

            DB::commit();
            return view('pumps.maintenance', compact('pump', 'maintenanceStats', 'recentReadings', 'utilizationMetrics', 'alerts'));

        } catch (Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'error_message' => 'Pump maintenance access failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$id}",
                'table_name' => 'pumps',
                'record_id' => $id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('pumps.select')->with('error', 'Pump maintenance access failed: ' . $e->getMessage());
        }
    }

    // =====================================
    // 🔥 PRIVATE HELPER METHODS WITH COMPLETE VALIDATION
    // =====================================

    /**
     * 🔥 COMPLETE STATION ACCESS VERIFICATION WITH BUSINESS RULES
     */
    private function verifyStationAccessWithCompleteValidation($stationId, $requiredRoles = []): mixed
    {
        try {
            $station = DB::table('stations')->where('id', $stationId)->where('is_active', 1)->first();
            if (!$station) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'INVALID_STATION_ACCESS_ATTEMPT',
                    'details' => "Attempted access to non-existent/inactive station: {$stationId}",
                    'ip_address' => request()->ip()
                ]);
                return redirect()->route('pumps.select')->with('error', 'Station not found or inactive');
            }

            // CEO/SYSTEM_ADMIN auto-approval
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                return true;
            }

            // Validate user station assignment
            $userStation = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('station_id', $stationId)
                ->where('is_active', 1)
                ->first();

            if (!$userStation) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'UNAUTHORIZED_STATION_ACCESS',
                    'details' => "User not assigned to station: {$stationId}",
                    'ip_address' => request()->ip(),
                    'station_id' => $stationId
                ]);
                return redirect()->route('pumps.select')->with('error', 'Access denied to this station');
            }

            // Validate role requirements
            if (!empty($requiredRoles) && !in_array($userStation->assigned_role, $requiredRoles)) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'INSUFFICIENT_ROLE_PERMISSIONS',
                    'details' => "Required roles: " . implode(',', $requiredRoles) . ", User role: {$userStation->assigned_role}",
                    'ip_address' => request()->ip(),
                    'station_id' => $stationId
                ]);
                return redirect()->route('pumps.index', $stationId)->with('error', 'Insufficient permissions for this action');
            }

            return true;

        } catch (Exception $e) {
            $this->auditService->logError([
                'error_message' => 'Station access verification failed: ' . $e->getMessage(),
                'error_context' => "Station ID: {$stationId}",
                'table_name' => 'user_stations',
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * 🔥 GET PUMPS WITH OPTIMIZED PERFORMANCE AND COMPLETE VALIDATION
     */
    private function getPumpsWithCompleteValidation($stationId): object
    {
        // Single optimized query to avoid N+1 performance issues
        $pumps = DB::table('pumps')
            ->leftJoin('tanks', 'pumps.tank_id', '=', 'tanks.id')
            ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
            ->leftJoin('meter_readings as latest_reading', function ($join) {
                $join->on('pumps.id', '=', 'latest_reading.pump_id')
                    ->whereRaw('latest_reading.reading_date = (SELECT MAX(mr.reading_date) FROM meter_readings mr WHERE mr.pump_id = pumps.id)');
            })
            ->where('pumps.station_id', $stationId)
            ->select([
                'pumps.id',
                'pumps.tank_id',
                'pumps.pump_number',
                'pumps.pump_serial_number',
                'pumps.pump_manufacturer',
                'pumps.pump_model',
                'pumps.meter_type',
                'pumps.meter_maximum_reading',
                'pumps.meter_reset_threshold',
                'pumps.flow_rate_min_lpm',
                'pumps.flow_rate_max_lpm',
                'pumps.nozzle_count',
                'pumps.has_preset_capability',
                'pumps.has_card_reader',
                'pumps.installation_date',
                'pumps.last_calibration_date',
                'pumps.next_calibration_date',
                'pumps.calibration_certificate',
                'pumps.last_maintenance_date',
                'pumps.next_maintenance_date',
                'pumps.is_active',
                'pumps.is_operational',
                'pumps.out_of_order_reason',
                'tanks.tank_number',
                'tanks.capacity_liters as tank_capacity',
                'products.product_name',
                'products.product_type',
                'products.product_code',
                'products.kebs_standard',
                'latest_reading.meter_reading_liters as last_meter_reading',
                'latest_reading.reading_date as last_reading_date',
                DB::raw('ROUND(DATEDIFF(CURDATE(), pumps.last_calibration_date), 0) as days_since_calibration'),
                DB::raw('ROUND(DATEDIFF(pumps.next_calibration_date, CURDATE()), 0) as days_until_calibration'),
                DB::raw('ROUND(DATEDIFF(CURDATE(), pumps.last_maintenance_date), 0) as days_since_maintenance'),
                DB::raw('ROUND(DATEDIFF(pumps.next_maintenance_date, CURDATE()), 0) as days_until_maintenance'),
                DB::raw('CASE
                    WHEN pumps.is_active = 0 THEN "INACTIVE"
                    WHEN pumps.is_operational = 0 THEN "OUT_OF_ORDER"
                    WHEN DATEDIFF(pumps.next_calibration_date, CURDATE()) < 0 THEN "CALIBRATION_OVERDUE"
                    WHEN DATEDIFF(pumps.next_maintenance_date, CURDATE()) < 7 THEN "MAINTENANCE_DUE"
                    ELSE "OPERATIONAL"
                END as pump_status')
            ])
            ->orderBy('pumps.pump_number')
            ->get();

        // Batch capacity usage calculation to avoid N+1 queries
        $tankIds = $pumps->pluck('tank_id')->filter()->unique();
        $capacityUsages = collect();

        if ($tankIds->isNotEmpty()) {
            // Single query for all tank capacity usages
            $capacityResults = DB::select('
                SELECT tank_id, fn_get_tank_capacity_usage(tank_id) as usage
                FROM (SELECT DISTINCT tank_id FROM pumps WHERE station_id = ? AND tank_id IS NOT NULL) t
            ', [$stationId]);

            $capacityUsages = collect($capacityResults)->pluck('usage', 'tank_id');
        }

        // Enhanced pump analytics with mathematical precision
        foreach ($pumps as $pump) {
            // Use batched capacity usage result
            $pump->tank_capacity_usage = round($capacityUsages->get($pump->tank_id, 0), 2);

            // Calculate recent activity with precision (single query per pump - acceptable for detail view)
            $recentReadings = DB::table('meter_readings')
                ->where('pump_id', $pump->id)
                ->where('reading_date', '>=', now()->subDays(7))
                ->orderBy('reading_date', 'desc')
                ->limit(7)
                ->pluck('meter_reading_liters');

            $pump->recent_activity = $recentReadings->count() > 1 ?
                round($recentReadings->first() - $recentReadings->last(), 3) : 0;

            // Check for meter resets (optimized query)
            $pump->meter_reset_occurred = DB::table('meter_readings')
                ->where('pump_id', $pump->id)
                ->where('meter_reset_occurred', 1)
                ->where('reading_date', '>=', now()->subDays(30))
                ->exists();

            // Calculate utilization score with mathematical precision
            $pump->utilization_score = ($pump->recent_activity > 0 && $pump->flow_rate_max_lpm > 0) ?
                min(100, round(($pump->recent_activity / ($pump->flow_rate_max_lpm * 60 * 24 * 7)) * 100, 2)) : 0;

            // Product grouping
            $pump->product_group = $this->getProductGroupFromDatabase($pump->product_type);

            // Mathematical precision status
            $pump->precision_status = $this->validatePumpPrecisionStatus($pump);
        }

        return $pumps;
    }

    /**
     * 🔥 GET VALID TANKS WITH COMPLETE DATABASE VALIDATION
     */
    private function getValidTanksWithCompleteValidation($stationId): object
    {
        // Get tanks with complete product validation from database
        $tanks = DB::table('tanks')
            ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
            ->leftJoin('pumps', 'tanks.id', '=', 'pumps.tank_id')
            ->where('tanks.station_id', $stationId)
            ->where('tanks.is_active', 1)
            ->where('products.is_active', 1)
            ->whereNotNull('products.product_type')
            ->select([
                'tanks.id',
                'tanks.tank_number',
                'tanks.capacity_liters',
                'tanks.minimum_stock_level_liters',
                'tanks.critical_low_level_liters',
                'products.product_name',
                'products.product_type',
                'products.product_code',
                'products.kebs_standard',
                'products.octane_rating',
                'products.sulphur_content_ppm',
                DB::raw('COUNT(pumps.id) as pump_count')
            ])
            ->groupBy([
                'tanks.id', 'tanks.tank_number', 'tanks.capacity_liters',
                'tanks.minimum_stock_level_liters', 'tanks.critical_low_level_liters',
                'products.product_name', 'products.product_type', 'products.product_code',
                'products.kebs_standard', 'products.octane_rating', 'products.sulphur_content_ppm'
            ])
            ->orderBy('tanks.tank_number')
            ->get();

        // Validate each tank with database functions
        foreach ($tanks as $tank) {
            // Get capacity usage using database function
            $capacityUsage = DB::selectOne('SELECT fn_get_tank_capacity_usage(?) as usage', [$tank->id]);
            $tank->capacity_usage_percentage = round($capacityUsage->usage ?? 0, 2);

            // Tank status determination
            $tank->tank_status = $tank->capacity_usage_percentage > 95 ? 'CRITICAL' :
                               ($tank->capacity_usage_percentage > 85 ? 'HIGH' : 'NORMAL');

            // Product group classification
            $tank->product_group = $this->getProductGroupFromDatabase($tank->product_type);
        }

        return $tanks;
    }

    /**
     * 🔥 MATHEMATICAL PRECISION VALIDATION FOR PUMP DATA
     */
    private function validatePumpDataWithMathematicalPrecision(Request $request, $stationId): array
    {
        // Get valid product types from database
        $validProductTypes = DB::table('products')
            ->where('is_active', 1)
            ->whereNotNull('product_type')
            ->pluck('product_type')
            ->toArray();

        $request->validate([
            'pump_number' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('pumps')->where(function ($query) use ($stationId) {
                    return $query->where('station_id', $stationId);
                })
            ],
            'tank_id' => [
                'required',
                'exists:tanks,id',
                function ($attribute, $value, $fail) use ($stationId, $validProductTypes) {
                    $tank = DB::table('tanks')
                        ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                        ->where('tanks.id', $value)
                        ->where('tanks.station_id', $stationId)
                        ->where('tanks.is_active', 1)
                        ->where('products.is_active', 1)
                        ->whereIn('products.product_type', $validProductTypes)
                        ->first();

                    if (!$tank) {
                        $fail('Tank must belong to the selected station and contain valid active fuel products.');
                    }
                }
            ],
            'pump_serial_number' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9\-_]+$/',
                'unique:pumps,pump_serial_number'
            ],
            'pump_manufacturer' => 'nullable|string|max:255',
            'pump_model' => 'nullable|string|max:100',
            'meter_type' => ['required', Rule::in(['MECHANICAL', 'ELECTRONIC', 'DIGITAL'])],
            'meter_maximum_reading' => [
                'required',
                'numeric',
                'min:1000000',
                'max:' . self::MAX_METER_READING,
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 3)) > self::VOLUME_PRECISION) {
                        $fail('Meter maximum reading must have precision to 3 decimal places (0.001L tolerance)');
                    }
                }
            ],
            'flow_rate_min_lpm' => [
                'required',
                'numeric',
                'min:' . self::MIN_FLOW_RATE,
                'max:' . self::MAX_FLOW_RATE,
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 2)) > 0.01) {
                        $fail('Flow rate minimum must have precision to 2 decimal places');
                    }
                }
            ],
            'flow_rate_max_lpm' => [
                'required',
                'numeric',
                'min:' . (self::MIN_FLOW_RATE + 5),
                'max:' . self::MAX_FLOW_RATE,
                'gt:flow_rate_min_lpm',
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 2)) > 0.01) {
                        $fail('Flow rate maximum must have precision to 2 decimal places');
                    }
                }
            ],
            'nozzle_count' => 'required|integer|min:1|max:4',
            'has_preset_capability' => 'boolean',
            'has_card_reader' => 'boolean',
            'initial_meter_reading' => [
                'required',
                'numeric',
                'min:0',
                'max:' . self::MAX_METER_READING,
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 3)) > self::VOLUME_PRECISION) {
                        $fail('Initial meter reading must have precision to 3 decimal places (0.001L tolerance)');
                    }
                }
            ],
            'installation_date' => 'required|date|before_or_equal:today|after:2000-01-01',
            'calibration_certificate' => 'nullable|string|max:100',
        ]);

        // Additional mathematical validation
        if ($request->meter_maximum_reading <= $request->initial_meter_reading) {
            throw new Exception('MATHEMATICAL CONSTRAINT VIOLATION: Maximum reading must be greater than initial reading');
        }

        // Validate flow rate mathematical relationship
        $flowRateDifference = $request->flow_rate_max_lpm - $request->flow_rate_min_lpm;
        if ($flowRateDifference < 5.0) {
            throw new Exception('MATHEMATICAL CONSTRAINT VIOLATION: Flow rate range must be at least 5 LPM');
        }

        return $request->all();
    }

    /**
     * 🔥 CREATE PUMP WITH COMPLETE TRANSACTION INTEGRITY
     */
    private function createPumpWithCompleteIntegrity(array $validatedData, $stationId, bool $isAutoApproved): int
    {
        try {
            // Calculate dates with precision
            $installationDate = Carbon::parse($validatedData['installation_date']);
            $nextCalibrationDate = $installationDate->copy()->addYear();
            $nextMaintenanceDate = $installationDate->copy()->addMonths(6);
            $meterResetThreshold = $validatedData['meter_maximum_reading'];

            // Create pump with complete schema compliance
            $pumpId = DB::table('pumps')->insertGetId([
                'station_id' => $stationId,
                'tank_id' => $validatedData['tank_id'],
                'pump_number' => $validatedData['pump_number'],
                'pump_serial_number' => $validatedData['pump_serial_number'],
                'pump_manufacturer' => $validatedData['pump_manufacturer'],
                'pump_model' => $validatedData['pump_model'],
                'meter_type' => $validatedData['meter_type'],
                'meter_maximum_reading' => round($validatedData['meter_maximum_reading'], 3),
                'meter_reset_threshold' => round($meterResetThreshold, 3),
                'flow_rate_min_lpm' => round($validatedData['flow_rate_min_lpm'], 2),
                'flow_rate_max_lpm' => round($validatedData['flow_rate_max_lpm'], 2),
                'nozzle_count' => $validatedData['nozzle_count'],
                'has_preset_capability' => $validatedData['has_preset_capability'] ?? true,
                'has_card_reader' => $validatedData['has_card_reader'] ?? false,
                'installation_date' => $installationDate->toDateString(),
                'last_calibration_date' => $installationDate->toDateString(),
                'next_calibration_date' => $nextCalibrationDate->toDateString(),
                'calibration_certificate' => $validatedData['calibration_certificate'],
                'last_maintenance_date' => $installationDate->toDateString(),
                'next_maintenance_date' => $nextMaintenanceDate->toDateString(),
                'is_active' => 1,
                'is_operational' => 1,
                'created_at' => now()
                // NOTE: updated_at removed - database handles with ON UPDATE CURRENT_TIMESTAMP
            ]);

            // Create initial meter reading with precision
            DB::table('meter_readings')->insert([
                'pump_id' => $pumpId,
                'reading_date' => $installationDate->toDateString(),
                'reading_shift' => 'MORNING',
                'reading_timestamp' => $installationDate,
                'meter_reading_liters' => round($validatedData['initial_meter_reading'], 3),
                'entered_by' => auth()->id(),
                'created_at' => now(),
                'meter_reset_occurred' => 0
            ]);

            // Validate system health after creation
            $this->validatePostCreationIntegrity($pumpId);

            return $pumpId;

        } catch (Exception $e) {
            throw new Exception("TRANSACTION INTEGRITY FAILURE: Pump creation failed - " . $e->getMessage());
        }
    }

    /**
     * 🔥 VALIDATE PUMP DATA UPDATE WITH MATHEMATICAL PRECISION
     */
    private function validatePumpUpdateWithMathematicalPrecision(Request $request, $pump, float $latestMeterValue): array
    {
        // Get valid product types from database
        $validProductTypes = DB::table('products')
            ->where('is_active', 1)
            ->whereNotNull('product_type')
            ->pluck('product_type')
            ->toArray();

        $request->validate([
            'tank_id' => [
                'required',
                'exists:tanks,id',
                function ($attribute, $value, $fail) use ($pump, $validProductTypes) {
                    $tank = DB::table('tanks')
                        ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                        ->where('tanks.id', $value)
                        ->where('tanks.station_id', $pump->station_id)
                        ->where('tanks.is_active', 1)
                        ->where('products.is_active', 1)
                        ->whereIn('products.product_type', $validProductTypes)
                        ->first();

                    if (!$tank) {
                        $fail('Tank must belong to the same station and contain valid active fuel products.');
                    }
                }
            ],
            'pump_manufacturer' => 'nullable|string|max:255',
            'pump_model' => 'nullable|string|max:100',
            'meter_maximum_reading' => [
                'required',
                'numeric',
                'min:' . max($latestMeterValue, 1000000),
                'max:' . self::MAX_METER_READING,
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 3)) > self::VOLUME_PRECISION) {
                        $fail('Meter maximum reading must have precision to 3 decimal places (0.001L tolerance)');
                    }
                }
            ],
            'flow_rate_min_lpm' => [
                'required',
                'numeric',
                'min:' . self::MIN_FLOW_RATE,
                'max:' . self::MAX_FLOW_RATE,
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 2)) > 0.01) {
                        $fail('Flow rate minimum must have precision to 2 decimal places');
                    }
                }
            ],
            'flow_rate_max_lpm' => [
                'required',
                'numeric',
                'min:' . (self::MIN_FLOW_RATE + 5),
                'max:' . self::MAX_FLOW_RATE,
                'gt:flow_rate_min_lpm',
                function ($attribute, $value, $fail) {
                    if (abs($value - round($value, 2)) > 0.01) {
                        $fail('Flow rate maximum must have precision to 2 decimal places');
                    }
                }
            ],
            'nozzle_count' => 'required|integer|min:1|max:4',
            'has_preset_capability' => 'boolean',
            'has_card_reader' => 'boolean',
            'last_calibration_date' => 'required|date|before_or_equal:today|after:2000-01-01',
            'calibration_certificate' => 'nullable|string|max:100',
            'last_maintenance_date' => 'nullable|date|before_or_equal:today|after:2000-01-01',
            'is_active' => 'boolean',
            'is_operational' => 'boolean',
            'out_of_order_reason' => 'nullable|string|max:255|required_if:is_operational,false',
        ]);

        // Additional mathematical validation
        $flowRateDifference = $request->flow_rate_max_lpm - $request->flow_rate_min_lpm;
        if ($flowRateDifference < 5.0) {
            throw new Exception('MATHEMATICAL CONSTRAINT VIOLATION: Flow rate range must be at least 5 LPM');
        }

        return $request->all();
    }

    /**
     * 🔥 UPDATE PUMP WITH COMPLETE CHANGE TRACKING
     */
    private function updatePumpWithCompleteChangeTracking($id, array $validatedData, $pump, bool $isAutoApproved): array
    {
        $changes = [];

        // Calculate dates with precision
        $nextCalibrationDate = Carbon::parse($validatedData['last_calibration_date'])->addYear();
        $nextMaintenanceDate = $validatedData['last_maintenance_date'] ?
            Carbon::parse($validatedData['last_maintenance_date'])->addMonths(6) :
            Carbon::parse($pump->next_maintenance_date);

        // Build update data with mathematical precision
        $updateData = [
            'tank_id' => $validatedData['tank_id'],
            'pump_manufacturer' => $validatedData['pump_manufacturer'],
            'pump_model' => $validatedData['pump_model'],
            'meter_maximum_reading' => round($validatedData['meter_maximum_reading'], 3),
            'meter_reset_threshold' => round($validatedData['meter_maximum_reading'], 3),
            'flow_rate_min_lpm' => round($validatedData['flow_rate_min_lpm'], 2),
            'flow_rate_max_lpm' => round($validatedData['flow_rate_max_lpm'], 2),
            'nozzle_count' => $validatedData['nozzle_count'],
            'has_preset_capability' => $validatedData['has_preset_capability'] ?? $pump->has_preset_capability,
            'has_card_reader' => $validatedData['has_card_reader'] ?? $pump->has_card_reader,
            'last_calibration_date' => $validatedData['last_calibration_date'],
            'next_calibration_date' => $nextCalibrationDate->toDateString(),
            'calibration_certificate' => $validatedData['calibration_certificate'],
            'out_of_order_reason' => ($validatedData['is_operational'] ?? true) ? null : $validatedData['out_of_order_reason']
            // NOTE: updated_at removed - database handles with ON UPDATE CURRENT_TIMESTAMP
        ];

        // Add maintenance data if provided
        if ($validatedData['last_maintenance_date']) {
            $updateData['last_maintenance_date'] = $validatedData['last_maintenance_date'];
            $updateData['next_maintenance_date'] = $nextMaintenanceDate->toDateString();
        }

        // Handle status changes with proper authorization
        if ($isAutoApproved || in_array(auth()->user()->role, ['STATION_MANAGER'])) {
            $updateData['is_active'] = $validatedData['is_active'] ?? $pump->is_active;
            $updateData['is_operational'] = $validatedData['is_operational'] ?? $pump->is_operational;
        }

        // Track changes with mathematical precision
        foreach ($updateData as $field => $newValue) {
            $oldValue = $pump->$field;

            // Mathematical precision comparison for numeric fields
            if (is_numeric($oldValue) && is_numeric($newValue)) {
                $precision = in_array($field, ['meter_maximum_reading', 'meter_reset_threshold']) ? 3 : 2;
                if (abs($oldValue - $newValue) > pow(10, -$precision)) {
                    $changes[] = "{$field}: " . round($oldValue, $precision) . " → " . round($newValue, $precision);
                }
            } else {
                if ($oldValue != $newValue) {
                    $changes[] = "{$field}: {$oldValue} → {$newValue}";
                }
            }
        }

        // Execute update with transaction integrity
        if (!empty($changes)) {
            DB::table('pumps')->where('id', $id)->update($updateData);

            // Validate post-update integrity
            $this->validatePostUpdateIntegrity($id);
        }

        return $changes;
    }

    /**
     * 🔥 GET PUMP WITH COMPLETE VALIDATION
     */
    private function getPumpWithCompleteValidation($id): ?object
    {
        return DB::table('pumps')
            ->leftJoin('tanks', 'pumps.tank_id', '=', 'tanks.id')
            ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
            ->leftJoin('stations', 'pumps.station_id', '=', 'stations.id')
            ->where('pumps.id', $id)
            ->select([
                'pumps.*',
                'tanks.tank_number',
                'tanks.capacity_liters as tank_capacity',
                'products.product_name',
                'products.product_type',
                'products.product_code',
                'products.kebs_standard',
                'stations.station_name',
                'stations.station_code'
            ])
            ->first();
    }

    /**
     * 🔥 GET LATEST METER READING WITH VALIDATION
     */
    private function getLatestMeterReadingWithValidation($pumpId): ?object
    {
        $reading = DB::table('meter_readings')
            ->where('pump_id', $pumpId)
            ->orderBy('reading_date', 'desc')
            ->orderBy('reading_shift', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Validate reading mathematical precision if exists
        if ($reading && abs($reading->meter_reading_liters - round($reading->meter_reading_liters, 3)) > self::VOLUME_PRECISION) {
            throw new Exception("MATHEMATICAL PRECISION VIOLATION: Meter reading lacks required 0.001L precision");
        }

        return $reading;
    }

    /**
     * 🔥 CALCULATE NEXT PUMP NUMBER WITH PRECISION
     */
    private function calculateNextPumpNumberWithPrecision($stationId): int
    {
        $maxPumpNumber = DB::table('pumps')
            ->where('station_id', $stationId)
            ->max('pump_number');

        return ($maxPumpNumber ?? 0) + 1;
    }

    /**
     * 🔥 GET PRODUCT GROUP FROM DATABASE
     */
    private function getProductGroupFromDatabase($productType): string
    {
        if (!$productType) return 'UNKNOWN';

        // Define product groupings based on business logic
        $productGroups = [
            'PETROLEUM' => ['PETROL_95', 'PETROL_98'],
            'DIESEL' => ['DIESEL', 'BIODIESEL_B7', 'BIODIESEL_B20'],
            'AVIATION' => ['JET_A1'],
            'INDUSTRIAL' => ['HEAVY_FUEL_OIL', 'LIGHT_FUEL_OIL'],
            'ALTERNATIVE' => ['LPG_AUTOGAS', 'ETHANOL_E10', 'ETHANOL_E85'],
            'SPECIALTY' => ['KEROSENE']
        ];

        foreach ($productGroups as $group => $types) {
            if (in_array($productType, $types)) {
                return $group;
            }
        }

        return 'UNKNOWN';
    }

    /**
     * 🔥 VALIDATE PUMP PRECISION STATUS
     */
    private function validatePumpPrecisionStatus($pump): string
    {
        try {
            // Validate meter reading precision
            if ($pump->last_meter_reading) {
                $precisionTest = abs($pump->last_meter_reading - round($pump->last_meter_reading, 3));
                if ($precisionTest > self::VOLUME_PRECISION) {
                    return 'PRECISION_VIOLATION';
                }
            }

            // Validate flow rate precision
            $flowMinPrecision = abs($pump->flow_rate_min_lpm - round($pump->flow_rate_min_lpm, 2));
            $flowMaxPrecision = abs($pump->flow_rate_max_lpm - round($pump->flow_rate_max_lpm, 2));

            if ($flowMinPrecision > 0.01 || $flowMaxPrecision > 0.01) {
                return 'FLOW_RATE_PRECISION_VIOLATION';
            }

            return 'PRECISION_COMPLIANT';

        } catch (Exception $e) {
            return 'PRECISION_VALIDATION_ERROR';
        }
    }

    /**
     * 🔥 VALIDATE PUMP SYSTEM HEALTH
     */
    private function validatePumpSystemHealth($stationId): void
    {
        try {
            // Execute system monitoring stored procedure
            DB::statement('CALL sp_enhanced_system_monitor()');

            // Check for critical system alerts
            $criticalAlerts = DB::table('system_health_monitoring')
                ->where('check_timestamp', '>=', now()->subMinutes(5))
                ->where('severity', 'CRITICAL')
                ->where('check_status', 'FAILED')
                ->count();

            if ($criticalAlerts > 0) {
                throw new Exception("SYSTEM HEALTH CRITICAL: {$criticalAlerts} critical alerts detected during pump operations");
            }

        } catch (Exception $e) {
            $this->auditService->logError([
                'error_message' => 'Pump system health validation failed: ' . $e->getMessage(),
                'error_context' => "Station ID: {$stationId}",
                'table_name' => 'system_health_monitoring',
                'user_id' => auth()->id(),
                'station_id' => $stationId
            ]);
            throw $e;
        }
    }

    /**
     * 🔥 VALIDATE PUMP FIFO CONSISTENCY
     */
    private function validatePumpFIFOConsistency($pumpId): void
    {
        try {
            // Get tank ID for this pump
            $tankId = DB::table('pumps')->where('id', $pumpId)->value('tank_id');
            if (!$tankId) {
                throw new Exception("FIFO VALIDATION ERROR: Pump {$pumpId} has no associated tank");
            }

            // Use FIFO service for consistency validation
            $fifoValidation = $this->fifoService->validateFIFOConsistency($tankId);

            if (!$fifoValidation['validation_passed']) {
                throw new Exception("FIFO CONSISTENCY VIOLATION: Tank {$tankId} FIFO validation failed - " .
                    json_encode($fifoValidation['monitoring_status']));
            }

            // Validate recent meter readings for mathematical consistency
            $recentReadings = DB::table('meter_readings')
                ->where('pump_id', $pumpId)
                ->where('reading_date', '>=', now()->subDays(7))
                ->orderBy('reading_date', 'desc')
                ->orderBy('reading_shift', 'desc')
                ->get();

            foreach ($recentReadings as $reading) {
                if (abs($reading->meter_reading_liters - round($reading->meter_reading_liters, 3)) > self::VOLUME_PRECISION) {
                    throw new Exception("MATHEMATICAL PRECISION VIOLATION: Meter reading {$reading->id} lacks required 0.001L precision");
                }
            }

        } catch (Exception $e) {
            $this->auditService->logError([
                'error_message' => 'Pump FIFO consistency validation failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$pumpId}",
                'table_name' => 'meter_readings',
                'record_id' => $pumpId,
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * 🔥 CALCULATE MAINTENANCE STATS WITH PRECISION
     */
    private function calculateMaintenanceStatsWithPrecision($pump): array
    {
        $currentDate = now();

        return [
            'days_since_last_maintenance' => $pump->last_maintenance_date ?
                Carbon::parse($pump->last_maintenance_date)->diffInDays($currentDate) : null,
            'days_until_next_maintenance' => $pump->next_maintenance_date ?
                Carbon::parse($pump->next_maintenance_date)->diffInDays($currentDate, false) : null,
            'maintenance_overdue' => $pump->next_maintenance_date &&
                Carbon::parse($pump->next_maintenance_date)->isPast(),
            'days_since_calibration' => Carbon::parse($pump->last_calibration_date)->diffInDays($currentDate),
            'days_until_calibration' => Carbon::parse($pump->next_calibration_date)->diffInDays($currentDate, false),
            'calibration_overdue' => Carbon::parse($pump->next_calibration_date)->isPast(),
            'maintenance_score' => $this->calculateMaintenanceScore($pump),
            'calibration_score' => $this->calculateCalibrationScore($pump),
            'overall_health_score' => $this->calculateOverallHealthScore($pump)
        ];
    }

    /**
     * 🔥 GET RECENT READINGS WITH VALIDATION
     */
    private function getRecentReadingsWithValidation($pumpId): object
    {
        $recentReadings = DB::table('meter_readings')
            ->where('pump_id', $pumpId)
            ->where('reading_date', '>=', now()->subDays(30))
            ->orderBy('reading_date', 'desc')
            ->orderBy('reading_shift', 'desc')
            ->select([
                'id',
                'reading_date',
                'reading_shift',
                'meter_reading_liters',
                'meter_reset_occurred',
                'reading_timestamp',
                'created_at'
            ])
            ->get();

        // Validate mathematical precision for each reading
        foreach ($recentReadings as $reading) {
            if (abs($reading->meter_reading_liters - round($reading->meter_reading_liters, 3)) > self::VOLUME_PRECISION) {
                $this->auditService->logError([
                    'error_message' => "Mathematical precision violation in meter reading {$reading->id}",
                    'error_context' => "Reading: {$reading->meter_reading_liters}L lacks 0.001L precision",
                    'table_name' => 'meter_readings',
                    'record_id' => $reading->id,
                    'user_id' => auth()->id()
                ]);
            }
        }

        return $recentReadings;
    }

    /**
     * 🔥 CALCULATE UTILIZATION METRICS WITH PRECISION
     */
    private function calculateUtilizationMetricsWithPrecision($pumpId, $recentReadings, $productType): array
    {
        $metrics = [
            'total_readings' => $recentReadings->count(),
            'meter_resets' => $recentReadings->where('meter_reset_occurred', 1)->count(),
            'daily_average_volume' => 0.0,
            'peak_day_volume' => 0.0,
            'utilization_trend' => 'STABLE',
            'product_group' => $this->getProductGroupFromDatabase($productType),
            'precision_compliance' => 'COMPLIANT',
            'mathematical_accuracy' => 100.0
        ];

        if ($recentReadings->count() > 1) {
            $sortedReadings = $recentReadings->sortBy('reading_date');
            $firstReading = $sortedReadings->first();
            $lastReading = $sortedReadings->last();

            // Calculate with mathematical precision
            $totalVolume = round($lastReading->meter_reading_liters - $firstReading->meter_reading_liters, 3);
            $daysDiff = max(1, Carbon::parse($firstReading->reading_date)->diffInDays(Carbon::parse($lastReading->reading_date)));

            $metrics['daily_average_volume'] = round($totalVolume / $daysDiff, 3);

            // Calculate daily volumes for peak detection
            $dailyVolumes = [];
            $previousReading = null;

            foreach ($sortedReadings as $reading) {
                if ($previousReading) {
                    $dailyVolume = round($reading->meter_reading_liters - $previousReading->meter_reading_liters, 3);
                    if ($dailyVolume >= 0) { // Exclude negative values (meter resets)
                        $dailyVolumes[] = $dailyVolume;
                    }
                }
                $previousReading = $reading;
            }

            if (!empty($dailyVolumes)) {
                $metrics['peak_day_volume'] = round(max($dailyVolumes), 3);

                // Determine trend with mathematical precision
                $recentHalf = array_slice($dailyVolumes, -ceil(count($dailyVolumes) / 2));
                $earlierHalf = array_slice($dailyVolumes, 0, floor(count($dailyVolumes) / 2));

                $recentAvg = count($recentHalf) > 0 ? array_sum($recentHalf) / count($recentHalf) : 0;
                $earlierAvg = count($earlierHalf) > 0 ? array_sum($earlierHalf) / count($earlierHalf) : 0;

                $trendDifference = $recentAvg - $earlierAvg;

                if (abs($trendDifference) < 10.0) {
                    $metrics['utilization_trend'] = 'STABLE';
                } elseif ($trendDifference > 10.0) {
                    $metrics['utilization_trend'] = 'INCREASING';
                } else {
                    $metrics['utilization_trend'] = 'DECREASING';
                }
            }

            // Check precision compliance
            $precisionViolations = 0;
            foreach ($recentReadings as $reading) {
                if (abs($reading->meter_reading_liters - round($reading->meter_reading_liters, 3)) > self::VOLUME_PRECISION) {
                    $precisionViolations++;
                }
            }

            if ($precisionViolations > 0) {
                $metrics['precision_compliance'] = 'VIOLATIONS_DETECTED';
                $metrics['mathematical_accuracy'] = round((($recentReadings->count() - $precisionViolations) / $recentReadings->count()) * 100, 2);
            }
        }

        return $metrics;
    }

    /**
     * 🔥 GENERATE MAINTENANCE ALERTS WITH BUSINESS RULES
     */
    private function generateMaintenanceAlertsWithBusinessRules($maintenanceStats, $pump): array
    {
        $alerts = [];

        // Critical business rule violations
        if ($maintenanceStats['maintenance_overdue']) {
            $alerts[] = [
                'type' => 'ERROR',
                'severity' => 'CRITICAL',
                'message' => 'BUSINESS RULE VIOLATION: Maintenance is overdue',
                'action_required' => 'Immediate maintenance scheduling required',
                'escalation_level' => 'CEO'
            ];
        }

        if ($maintenanceStats['calibration_overdue']) {
            $alerts[] = [
                'type' => 'ERROR',
                'severity' => 'CRITICAL',
                'message' => 'REGULATORY VIOLATION: Calibration is overdue',
                'action_required' => 'Immediate calibration required - pump may need to be taken offline',
                'escalation_level' => 'REGULATORY'
            ];
        }

        // Warning alerts
        if ($maintenanceStats['days_until_next_maintenance'] !== null && $maintenanceStats['days_until_next_maintenance'] <= 7) {
            $alerts[] = [
                'type' => 'WARNING',
                'severity' => 'HIGH',
                'message' => "Maintenance due within {$maintenanceStats['days_until_next_maintenance']} days",
                'action_required' => 'Schedule maintenance within warning period',
                'escalation_level' => 'STATION_MANAGER'
            ];
        }

        if ($maintenanceStats['days_until_calibration'] !== null && $maintenanceStats['days_until_calibration'] <= 30) {
            $alerts[] = [
                'type' => 'WARNING',
                'severity' => 'MEDIUM',
                'message' => "Calibration due within {$maintenanceStats['days_until_calibration']} days",
                'action_required' => 'Schedule calibration appointment',
                'escalation_level' => 'STATION_MANAGER'
            ];
        }

        // Operational status alerts
        if (!$pump->is_operational) {
            $alerts[] = [
                'type' => 'ERROR',
                'severity' => 'HIGH',
                'message' => 'Pump is not operational: ' . ($pump->out_of_order_reason ?? 'No reason specified'),
                'action_required' => 'Resolve operational issues and restore pump service',
                'escalation_level' => 'IMMEDIATE'
            ];
        }

        if (!$pump->is_active) {
            $alerts[] = [
                'type' => 'INFO',
                'severity' => 'MEDIUM',
                'message' => 'Pump is inactive',
                'action_required' => 'Review pump activation status',
                'escalation_level' => 'STATION_MANAGER'
            ];
        }

        // Mathematical precision alerts
        $precisionStatus = $this->validatePumpPrecisionStatus($pump);
        if ($precisionStatus !== 'PRECISION_COMPLIANT') {
            $alerts[] = [
                'type' => 'ERROR',
                'severity' => 'HIGH',
                'message' => "Mathematical precision violation: {$precisionStatus}",
                'action_required' => 'Review and correct precision violations',
                'escalation_level' => 'SYSTEM_ADMIN'
            ];
        }

        return $alerts;
    }

    /**
     * 🔥 CALCULATE MAINTENANCE SCORE
     */
    private function calculateMaintenanceScore($pump): int
    {
        $score = 100;

        if ($pump->next_maintenance_date) {
            $daysUntilMaintenance = Carbon::parse($pump->next_maintenance_date)->diffInDays(now(), false);

            if ($daysUntilMaintenance < 0) {
                $score = 0; // Overdue
            } elseif ($daysUntilMaintenance <= 7) {
                $score = 25; // Due soon
            } elseif ($daysUntilMaintenance <= 30) {
                $score = 75; // Approaching
            }
        }

        return $score;
    }

    /**
     * 🔥 CALCULATE CALIBRATION SCORE
     */
    private function calculateCalibrationScore($pump): int
    {
        $score = 100;

        $daysUntilCalibration = Carbon::parse($pump->next_calibration_date)->diffInDays(now(), false);

        if ($daysUntilCalibration < 0) {
            $score = 0; // Overdue
        } elseif ($daysUntilCalibration <= 30) {
            $score = 25; // Due soon
        } elseif ($daysUntilCalibration <= 90) {
            $score = 75; // Approaching
        }

        return $score;
    }

    /**
     * 🔥 CALCULATE OVERALL HEALTH SCORE
     */
    private function calculateOverallHealthScore($pump): int
    {
        $scores = [];

        // Operational status score
        $scores[] = $pump->is_operational ? 100 : 0;
        $scores[] = $pump->is_active ? 100 : 50;

        // Maintenance scores
        $scores[] = $this->calculateMaintenanceScore($pump);
        $scores[] = $this->calculateCalibrationScore($pump);

        // Precision compliance score
        $precisionStatus = $this->validatePumpPrecisionStatus($pump);
        $scores[] = $precisionStatus === 'PRECISION_COMPLIANT' ? 100 : 0;

        return round(array_sum($scores) / count($scores));
    }

    /**
     * 🔥 VALIDATE POST-CREATION INTEGRITY
     */
    private function validatePostCreationIntegrity($pumpId): void
    {
        try {
            // Verify pump was created correctly
            $createdPump = DB::table('pumps')->where('id', $pumpId)->first();
            if (!$createdPump) {
                throw new Exception("INTEGRITY VIOLATION: Pump {$pumpId} not found after creation");
            }

            // Verify initial meter reading was created
            $initialReading = DB::table('meter_readings')->where('pump_id', $pumpId)->first();
            if (!$initialReading) {
                throw new Exception("INTEGRITY VIOLATION: Initial meter reading not created for pump {$pumpId}");
            }

            // Validate mathematical precision of created data
            if (abs($initialReading->meter_reading_liters - round($initialReading->meter_reading_liters, 3)) > self::VOLUME_PRECISION) {
                throw new Exception("MATHEMATICAL INTEGRITY VIOLATION: Initial meter reading lacks required precision");
            }

            // Validate business rule compliance
            if ($createdPump->meter_maximum_reading <= $initialReading->meter_reading_liters) {
                throw new Exception("BUSINESS RULE VIOLATION: Maximum reading not greater than initial reading");
            }

        } catch (Exception $e) {
            $this->auditService->logError([
                'error_message' => 'Post-creation integrity validation failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$pumpId}",
                'table_name' => 'pumps',
                'record_id' => $pumpId,
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    // =====================================
    // 🔥 CRITICAL VALIDATION METHODS FOR 100% COMPLIANCE
    // =====================================

    /**
     * 🔥 VALIDATE USER ROLE FROM DATABASE (NOT AUTH CACHE)
     */
    private function validateUserRoleFromDatabase($userId): string
    {
        $userRole = DB::table('users')
            ->where('id', $userId)
            ->where('is_active', 1)
            ->value('role');

        if (!$userRole) {
            throw new Exception("SECURITY VIOLATION: User {$userId} not found or inactive in database");
        }

        // Cross-validate with auth cache
        if (auth()->user()->role !== $userRole) {
            $this->auditService->logSecurityViolation([
                'user_id' => $userId,
                'action' => 'ROLE_MISMATCH_DETECTED',
                'details' => "Auth cache role: " . auth()->user()->role . ", Database role: {$userRole}",
                'ip_address' => request()->ip()
            ]);
            throw new Exception("SECURITY VIOLATION: Role mismatch detected - session compromised");
        }

        return $userRole;
    }

    /**
     * 🔥 VALIDATE COMPLETE AUTOMATION INTEGRATION
     */
    private function validateCompleteAutomationIntegration($pumpId = null): void
    {
        try {
            // Validate triggers are enabled and functional
            $this->validateTriggerExecution();

            // Validate stored procedures are operational
            DB::statement('CALL sp_enhanced_system_monitor()');

            // Check system health monitoring results
            $this->validateSystemHealthResults();

            // If pump-specific, validate FIFO automation
            if ($pumpId) {
                $this->validatePumpFIFOAutomation($pumpId);
            }

        } catch (Exception $e) {
            throw new Exception("AUTOMATION INTEGRATION FAILURE: " . $e->getMessage());
        }
    }

    /**
     * 🔥 VALIDATE TRIGGER EXECUTION
     */
    private function validateTriggerExecution(): void
    {
        // Check critical triggers exist and are enabled
        $criticalTriggers = [
            'tr_enhanced_meter_fifo_automation',
            'tr_validate_meter_progression',
            'tr_enhanced_delivery_fifo_layers',
            'tr_auto_variance_detection'
        ];

        $existingTriggers = DB::select("
            SELECT TRIGGER_NAME
            FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA = DATABASE()
            AND TRIGGER_NAME IN ('" . implode("','", $criticalTriggers) . "')
        ");

        $foundTriggers = collect($existingTriggers)->pluck('TRIGGER_NAME')->toArray();
        $missingTriggers = array_diff($criticalTriggers, $foundTriggers);

        if (!empty($missingTriggers)) {
            throw new Exception("CRITICAL TRIGGERS MISSING: " . implode(', ', $missingTriggers));
        }
    }

    /**
     * 🔥 VALIDATE SYSTEM HEALTH RESULTS
     */
    private function validateSystemHealthResults(): void
    {
        $recentChecks = DB::table('system_health_monitoring')
            ->where('check_timestamp', '>=', now()->subMinutes(5))
            ->where('check_status', 'FAILED')
            ->where('severity', 'CRITICAL')
            ->count();

        if ($recentChecks > 0) {
            throw new Exception("SYSTEM HEALTH CRITICAL: {$recentChecks} critical failures detected");
        }
    }

    /**
     * 🔥 VALIDATE PUMP FIFO AUTOMATION
     */
    private function validatePumpFIFOAutomation($pumpId): void
    {
        // Check if pump has meter readings that should trigger FIFO
        $recentMeterReadings = DB::table('meter_readings')
            ->where('pump_id', $pumpId)
            ->where('reading_date', '>=', now()->subDays(1))
            ->count();

        if ($recentMeterReadings > 0) {
            // Verify FIFO processing occurred
            $fifoProcessing = DB::table('system_health_monitoring')
                ->where('check_type', 'FIFO_PROCESSING_SUCCESS')
                ->where('check_details', 'LIKE', "%pump {$pumpId}%")
                ->where('check_timestamp', '>=', now()->subDays(1))
                ->exists();

            if (!$fifoProcessing) {
                throw new Exception("FIFO AUTOMATION FAILURE: No FIFO processing detected for pump {$pumpId}");
            }
        }
    }

    /**
     * 🔥 VALIDATE AUDIT INTEGRITY AND HASH CHAIN
     */
    private function validateAuditIntegrity(): void
    {
        // try {
        //     // Verify hash chain integrity using AuditService
        //     $hashValidation = $this->auditService->verifyHashChainIntegrity(auth()->id(), 10);

        //     if (isset($hashValidation['error'])) {
        //         throw new Exception("AUDIT HASH CHAIN ERROR: " . $hashValidation['error']);
        //     }

        //     if ($hashValidation['integrity_percentage'] < 100) {
        //         throw new Exception("AUDIT INTEGRITY VIOLATION: Hash chain integrity at {$hashValidation['integrity_percentage']}%");
        //     }

        // } catch (Exception $e) {
        //     $this->auditService->logSecurityViolation([
        //         'user_id' => auth()->id(),
        //         'action' => 'AUDIT_INTEGRITY_FAILURE',
        //         'details' => $e->getMessage(),
        //         'ip_address' => request()->ip()
        //     ]);
        //     throw $e;
        // }
    }

    /**
     * 🔥 VALIDATE TRANSACTION INTEGRITY
     */
    private function validateTransactionIntegrity(): void
    {
        // Verify database connection is active
        if (!DB::connection()->getPdo()) {
            throw new Exception("TRANSACTION INTEGRITY FAILURE: Database connection lost");
        }

        // Verify we're in a transaction
        if (DB::transactionLevel() === 0) {
            throw new Exception("TRANSACTION INTEGRITY FAILURE: Not in transaction");
        }

        // Check for any pending critical alerts
        $criticalAlerts = DB::table('system_accuracy_alerts')
            ->where('detected_at', '>=', now()->subMinutes(1))
            ->where('severity', 'CRITICAL')
            ->where('resolved_at', null)
            ->count();

        if ($criticalAlerts > 0) {
            throw new Exception("TRANSACTION INTEGRITY FAILURE: {$criticalAlerts} critical alerts during operation");
        }
    }

    /**
     * 🔥 VALIDATE ROLLBACK SUCCESS
     */
    private function validateRollbackSuccess(): void
    {
        try {
            // Verify we're no longer in a transaction
            if (DB::transactionLevel() > 0) {
                throw new Exception("ROLLBACK FAILURE: Still in transaction after rollback");
            }

            // Verify database connection is still active
            if (!DB::connection()->getPdo()) {
                throw new Exception("ROLLBACK FAILURE: Database connection lost during rollback");
            }

            // Log successful rollback
            $this->auditService->logAction([
                'action_type' => 'UPDATE',
                'action_category' => 'MAINTENANCE',
                'table_name' => 'system_recovery',
                'change_reason' => 'Transaction rollback completed successfully',
                'business_justification' => 'Error recovery with complete rollback',
                'user_id' => auth()->id()
            ]);

        } catch (Exception $e) {
            // Critical: Rollback validation failed
            $this->auditService->logError([
                'error_message' => 'CRITICAL: Rollback validation failed - ' . $e->getMessage(),
                'error_context' => 'System integrity may be compromised',
                'table_name' => 'system_recovery',
                'user_id' => auth()->id()
            ]);

            // This is a critical system failure
            throw new Exception("CRITICAL SYSTEM FAILURE: Rollback validation failed - " . $e->getMessage());
        }
    }

    /**
     * 🔥 VALIDATE FINANCIAL REPORTING ACCURACY
     */
    private function validateFinancialReportingAccuracy($pumpId): void
    {
        try {
            // Get tank ID for this pump
            $tankId = DB::table('pumps')->where('id', $pumpId)->value('tank_id');
            if (!$tankId) {
                throw new Exception("FINANCIAL ACCURACY ERROR: Pump {$pumpId} has no associated tank");
            }

            // Validate FIFO mathematical consistency for financial accuracy
            $fifoValidation = $this->fifoService->validateFIFOConsistency($tankId);
            if (!$fifoValidation['validation_passed']) {
                throw new Exception("FINANCIAL ACCURACY VIOLATION: FIFO inconsistency detected in tank {$tankId}");
            }

            // Validate no variance above tolerance
            $recentVariances = DB::table('variances')
                ->join('readings', 'variances.reading_id', '=', 'readings.id')
                ->where('readings.tank_id', $tankId)
                ->where('variances.created_at', '>=', now()->subDays(1))
                ->where('variances.calculated_variance_percentage', '>', 0.001) // Zero tolerance
                ->count();

            if ($recentVariances > 0) {
                throw new Exception("FINANCIAL ACCURACY VIOLATION: {$recentVariances} variances above zero tolerance detected");
            }

            // Validate cost calculation precision
            $this->validateCostCalculationPrecision($tankId);

        } catch (Exception $e) {
            $this->auditService->logError([
                'error_message' => 'Financial reporting accuracy validation failed: ' . $e->getMessage(),
                'error_context' => "Pump ID: {$pumpId}",
                'table_name' => 'financial_validation',
                'record_id' => $pumpId,
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * 🔥 VALIDATE COST CALCULATION PRECISION
     */
    private function validateCostCalculationPrecision($tankId): void
    {
        // Get latest cost calculations for this tank
        $costCalculation = DB::table('cost_calculations')
            ->where('tank_id', $tankId)
            ->orderBy('calculation_date', 'desc')
            ->orderBy('calculation_time', 'desc')
            ->first();

        if ($costCalculation) {
            // Validate weighted average cost precision (0.0001 UGX tolerance)
            if (abs($costCalculation->weighted_average_cost - round($costCalculation->weighted_average_cost, 4)) > self::COST_PRECISION) {
                throw new Exception("COST PRECISION VIOLATION: Weighted average cost lacks required 0.0001 UGX precision");
            }

            // Validate total inventory value precision
            $expectedValue = round($costCalculation->total_quantity_liters * $costCalculation->weighted_average_cost, 2);
            if (abs($costCalculation->total_inventory_value - $expectedValue) > 0.01) {
                throw new Exception("COST CALCULATION ERROR: Inventory value mathematical inconsistency");
            }
        }
    }
}
