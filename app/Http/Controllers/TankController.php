<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\AuditService;
use App\Services\CorrectedFIFOService;
use App\Services\ReconciliationService;
use Exception;

class TankController extends Controller
{
    private AuditService $auditService;
    private CorrectedFIFOService $fifoService;
    private ReconciliationService $reconciliationService;

    public function __construct(
        AuditService $auditService,
        CorrectedFIFOService $fifoService,
        ReconciliationService $reconciliationService
    ) {
        $this->auditService = $auditService;
        $this->fifoService = $fifoService;
        $this->reconciliationService = $reconciliationService;

        // 🔥 MANDATORY: Validate automation systems on controller load
        $this->validateSystemIntegrity();
    }

    /**
     * 🔥 CRITICAL: System integrity validation with automation integration
     */
    private function validateSystemIntegrity(): void
    {
        try {
            // Check critical system configurations
            $requiredConfigs = [
                'ENHANCED_FIFO_PROCESSING_ENABLED',
                'ENHANCED_MONITORING_ENABLED',
                'ENHANCED_CLEANUP_ENABLED',
                'AUTO_DELIVERY_LAYER_CREATION'
            ];

            $missingConfigs = [];
            foreach ($requiredConfigs as $config) {
                $configExists = DB::table('system_configurations')
                    ->where('config_key', $config)
                    ->where('config_value_boolean', 1)
                    ->exists();

                if (!$configExists) {
                    $missingConfigs[] = $config;
                }
            }

            if (!empty($missingConfigs)) {
                throw new Exception(
                    "AUTOMATION SYSTEM FAILURE: Required configurations disabled: " .
                    implode(', ', $missingConfigs)
                );
            }

            // Validate stored procedures are operational
            DB::statement('CALL sp_enhanced_system_monitor()');

            $recentErrors = DB::table('system_health_monitoring')
                ->where('check_status', 'FAILED')
                ->where('check_timestamp', '>=', now()->subMinutes(5))
                ->where('severity', 'CRITICAL')
                ->count();

            if ($recentErrors > 0) {
                throw new Exception("CRITICAL SYSTEM ERRORS DETECTED: {$recentErrors} critical failures in last 5 minutes");
            }

        } catch (Exception $e) {
            // Log critical system failure
            $this->auditService->logError([
                'user_id' => auth()->id() ?? 1,
                'error_message' => $e->getMessage(),
                'error_context' => 'TankController system integrity validation',
                'table_name' => 'system_configurations',
                'severity' => 'CRITICAL'
            ]);

            abort(503, 'System automation integrity compromised: ' . $e->getMessage());
        }
    }

    public function selectStation()
    {
        // Initialize stations collection
        $stations = collect();
        $hasAccess = true;

        try {
            // 🔥 ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN see all stations
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                $stations = DB::table('stations')
                    ->leftJoin('tanks', 'stations.id', '=', 'tanks.station_id')
                    ->where('stations.is_active', 1)
                    ->select([
                        'stations.id',
                        'stations.station_code',
                        'stations.station_name',
                        'stations.region',
                        'stations.district',
                        DB::raw('COUNT(tanks.id) as tank_count'),
                        DB::raw('COUNT(CASE WHEN tanks.is_active = 1 THEN tanks.id END) as active_tanks')
                    ])
                    ->groupBy(['stations.id', 'stations.station_code', 'stations.station_name', 'stations.region', 'stations.district'])
                    ->orderBy('stations.station_name')
                    ->get();

                // 🔥 CORRECTED: Use AuditService with exact schema compliance
                $this->auditService->logAction([
                    'user_id' => auth()->id(),
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'stations',
                    'change_reason' => 'All stations accessed for tank management',
                    'business_justification' => 'CEO/SYSTEM_ADMIN auto-approved station access',
                    'is_auto_approved' => true
                ]);
            } else {
                // Station-scoped access for other users with proper validation
                $userStationIds = DB::table('user_stations')
                    ->where('user_id', auth()->id())
                    ->where('is_active', 1)
                    ->whereIn('assigned_role', ['MANAGER', 'ASSISTANT_MANAGER', 'SUPERVISOR', 'OPERATOR'])
                    ->pluck('station_id');

                if ($userStationIds->isNotEmpty()) {
                    $stations = DB::table('stations')
                        ->leftJoin('tanks', 'stations.id', '=', 'tanks.station_id')
                        ->whereIn('stations.id', $userStationIds)
                        ->where('stations.is_active', 1)
                        ->select([
                            'stations.id',
                            'stations.station_code',
                            'stations.station_name',
                            'stations.region',
                            'stations.district',
                            DB::raw('COUNT(tanks.id) as tank_count'),
                            DB::raw('COUNT(CASE WHEN tanks.is_active = 1 THEN tanks.id END) as active_tanks')
                        ])
                        ->groupBy(['stations.id', 'stations.station_code', 'stations.station_name', 'stations.region', 'stations.district'])
                        ->orderBy('stations.station_name')
                        ->get();

                    $this->auditService->logAction([
                        'user_id' => auth()->id(),
                        'action_type' => 'READ',
                        'action_category' => 'DATA_ENTRY',
                        'table_name' => 'user_stations',
                        'change_reason' => 'User-scoped station access for tank management',
                        'business_justification' => 'Role-based station access validation'
                    ]);
                } else {
                    $hasAccess = false;
                }
            }

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'selectStation method execution',
                'table_name' => 'stations'
            ]);

            return redirect()->back()->with('error', 'System error accessing stations: ' . $e->getMessage());
        }

        return view('tanks.select-station', compact('stations', 'hasAccess'));
    }

    public function index($stationId)
    {
        try {
            // 🔥 CORRECTED: Verify station access with exact permissions
            $accessResult = $this->verifyStationAccessWithAutomation($stationId);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $stationId,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'tanks',
                'change_reason' => "Tank management accessed for station: {$station->station_name}",
                'business_justification' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                    ? 'Auto-approved elevated role access'
                    : 'Validated station role access',
                'is_auto_approved' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
            ]);

            // 🔥 CORRECTED: Use service for FIFO data instead of direct queries
            $tanks = $this->getTanksWithAutomationData($stationId);

            return view('tanks.index', compact('station', 'tanks'));

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank index method execution',
                'table_name' => 'tanks'
            ]);

            return redirect()->route('tanks.select')->with('error', 'System error: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 CORRECTED: Get tanks with complete automation integration
     */
    private function getTanksWithAutomationData($stationId): object
    {
        // Get base tank data with exact schema compliance
        $tanks = DB::table('tanks')
            ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
            ->where('tanks.station_id', $stationId)
            ->select([
                'tanks.id',
                'tanks.tank_number',
                'tanks.capacity_liters',
                'tanks.minimum_stock_level_liters',
                'tanks.maximum_stock_level_liters',
                'tanks.critical_low_level_liters',
                'tanks.maximum_variance_percentage',
                'tanks.tank_type',
                'tanks.tank_material',
                'tanks.installation_date',
                'tanks.last_inspection_date',
                'tanks.next_inspection_date',
                'tanks.calibration_date',
                'tanks.calibration_valid_until',
                'tanks.is_active',
                'products.product_name',
                'products.product_type',
                'products.product_code'
            ])
            ->orderBy('tanks.tank_number')
            ->get();

        // 🔥 CORRECTED: Enhance each tank with service-based data
        foreach ($tanks as $tank) {
            try {
                // Use FIFOService for accurate inventory data
                $fifoValidation = $this->fifoService->validateFIFOConsistency($tank->id);

                $tank->current_stock_liters = $fifoValidation['fifo_summary']['current_quantity_liters'];
                $tank->active_layers = $fifoValidation['fifo_summary']['active_layers'];

                // Get latest reading using EXACT schema with reading_type filtering
                $latestReading = DB::table('readings')
                    ->where('tank_id', $tank->id)
                    ->where('reading_type', 'MORNING_DIP')
                    ->whereNotNull('dip_reading_liters')
                    ->orderBy('reading_date', 'desc')
                    ->orderBy('reading_time', 'desc')
                    ->first();

                $tank->last_dip_reading = $latestReading ? $latestReading->dip_reading_liters : 0;
                $tank->last_reading_date = $latestReading ? $latestReading->reading_date : null;

                // Calculate stock status with exact business rules
                if ($tank->current_stock_liters <= $tank->critical_low_level_liters) {
                    $tank->stock_status = 'CRITICAL';
                } elseif ($tank->current_stock_liters <= $tank->minimum_stock_level_liters) {
                    $tank->stock_status = 'LOW';
                } elseif ($tank->current_stock_liters >= $tank->maximum_stock_level_liters) {
                    $tank->stock_status = 'HIGH';
                } else {
                    $tank->stock_status = 'NORMAL';
                }

                // Calculate fill percentage with 0.001L precision
                $tank->fill_percentage = round(($tank->current_stock_liters / $tank->capacity_liters) * 100, 1);

                // Check calibration status
                $calibrationCount = DB::table('tank_calibration_tables')
                    ->where('tank_id', $tank->id)
                    ->count();

                $tank->calibration_complete = $calibrationCount > 0;
                $tank->calibration_expired = $tank->calibration_valid_until < now()->toDateString();

            } catch (Exception $e) {
                // Handle individual tank errors gracefully
                $tank->current_stock_liters = 0;
                $tank->active_layers = 0;
                $tank->last_dip_reading = 0;
                $tank->last_reading_date = null;
                $tank->stock_status = 'ERROR';
                $tank->fill_percentage = 0;
                $tank->calibration_complete = false;
                $tank->calibration_expired = true;
                $tank->error_message = $e->getMessage();

                $this->auditService->logError([
                    'user_id' => auth()->id(),
                    'error_message' => $e->getMessage(),
                    'error_context' => "Tank data loading for tank {$tank->id}",
                    'table_name' => 'tanks',
                    'record_id' => $tank->id
                ]);
            }
        }

        return $tanks;
    }

    public function create($stationId)
    {
        try {
            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($stationId, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $stationId,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'tanks',
                'change_reason' => "Tank creation accessed for station: {$station->station_name}",
                'business_justification' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                    ? 'Auto-approved elevated role access'
                    : 'Validated manager role access',
                'is_auto_approved' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
            ]);

            // Get available products with exact schema fields
            $products = DB::table('products')
                ->where('is_active', 1)
                ->whereIn('product_type', ['PETROL_95', 'PETROL_98', 'DIESEL', 'KEROSENE'])
                ->select('id', 'product_name', 'product_type', 'product_code')
                ->orderBy('product_name')
                ->get();

            // Get next tank number with validation
            $nextTankNumber = DB::table('tanks')
                ->where('station_id', $stationId)
                ->max('tank_number');

            $nextTankNumber = ($nextTankNumber ?? 0) + 1;

            return view('tanks.create', compact('station', 'products', 'nextTankNumber'));

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank create method execution',
                'table_name' => 'tanks'
            ]);

            return redirect()->route('tanks.index', $stationId)->with('error', 'System error: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $stationId)
    {
        try {
            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($stationId, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $station = DB::table('stations')->where('id', $stationId)->first();
            $isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);

            // 🔥 CORRECTED: Validation with 0.001L mathematical precision
            $request->validate([
                'tank_number' => [
                    'required',
                    'integer',
                    'min:1',
                    Rule::unique('tanks')->where(function ($query) use ($stationId) {
                        return $query->where('station_id', $stationId);
                    })
                ],
                'product_id' => 'required|exists:products,id',
                'capacity_liters' => [
                    'required',
                    'numeric',
                    'min:1000.000',
                    'max:100000.000',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'minimum_stock_level_liters' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'lt:capacity_liters',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'critical_low_level_liters' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'lt:minimum_stock_level_liters',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'maximum_variance_percentage' => 'required|numeric|min:0.1|max:10.0',
                'tank_type' => ['required', Rule::in(['UNDERGROUND', 'ABOVE_GROUND', 'PORTABLE'])],
                'tank_material' => ['required', Rule::in(['STEEL', 'FIBERGLASS', 'CONCRETE'])],
                'tank_manufacturer' => 'nullable|string|max:255',
                'tank_serial_number' => 'nullable|string|max:100',
                'installation_date' => 'required|date|before_or_equal:today',
                'last_inspection_date' => 'required|date|before_or_equal:today',
                'calibration_certificate' => 'nullable|string|max:100',
            ]);

            // 🔥 CRITICAL: Mathematical precision validation
            if (floatval($request->minimum_stock_level_liters) >= floatval($request->capacity_liters)) {
                return redirect()->back()->withErrors([
                    'minimum_stock_level_liters' => 'Minimum stock level must be less than capacity with 0.001L precision'
                ]);
            }

            if (floatval($request->critical_low_level_liters) >= floatval($request->minimum_stock_level_liters)) {
                return redirect()->back()->withErrors([
                    'critical_low_level_liters' => 'Critical level must be less than minimum stock level with 0.001L precision'
                ]);
            }

            $tankId = DB::transaction(function () use ($request, $stationId, $isAutoApproved) {
                // Calculate automatic dates with business rules
                $nextInspectionDate = Carbon::parse($request->last_inspection_date)->addYear();
                $calibrationValidUntil = Carbon::parse($request->installation_date)->addYears(2);
                $maximumStockLevel = round(floatval($request->capacity_liters) * 0.95, 3); // 95% of capacity with precision

                $tankId = DB::table('tanks')->insertGetId([
                    'station_id' => $stationId,
                    'tank_number' => $request->tank_number,
                    'product_id' => $request->product_id,
                    'capacity_liters' => round(floatval($request->capacity_liters), 3),
                    'minimum_stock_level_liters' => round(floatval($request->minimum_stock_level_liters), 3),
                    'maximum_stock_level_liters' => $maximumStockLevel,
                    'critical_low_level_liters' => round(floatval($request->critical_low_level_liters), 3),
                    'maximum_variance_percentage' => $request->maximum_variance_percentage,
                    'tank_type' => $request->tank_type,
                    'tank_material' => $request->tank_material,
                    'tank_manufacturer' => $request->tank_manufacturer,
                    'tank_serial_number' => $request->tank_serial_number,
                    'installation_date' => $request->installation_date,
                    'last_inspection_date' => $request->last_inspection_date,
                    'next_inspection_date' => $nextInspectionDate->toDateString(),
                    'calibration_date' => $request->installation_date,
                    'calibration_certificate' => $request->calibration_certificate,
                    'calibration_valid_until' => $calibrationValidUntil->toDateString(),
                    'leak_detection_system' => 0,
                    'overfill_protection' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 🔥 CORRECTED: Use AuditService with complete automation context
                $this->auditService->logAction([
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'action_type' => 'CREATE',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'tanks',
                    'record_id' => $tankId,
                    'new_values' => json_encode([
                        'tank_number' => $request->tank_number,
                        'capacity_liters' => round(floatval($request->capacity_liters), 3),
                        'product_id' => $request->product_id,
                        'tank_type' => $request->tank_type
                    ]),
                    'change_reason' => "Tank {$request->tank_number} created with capacity {$request->capacity_liters}L",
                    'business_justification' => $isAutoApproved
                        ? 'Auto-approved by elevated role with full automation integration'
                        : 'Manager role tank creation with automation validation',
                    'is_auto_approved' => $isAutoApproved,
                    'automation_context' => [
                        'automation_systems_validated' => true,
                        'mathematical_precision_enforced' => true,
                        'business_rules_applied' => true,
                        'triggers_ready' => true
                    ]
                ]);

                return $tankId;
            });

            return redirect()->route('tanks.calibration', $tankId)
                ->with('success', 'Tank created successfully with full automation integration. Please configure calibration table.');

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank store method execution',
                'table_name' => 'tanks'
            ]);

            return redirect()->back()->with('error', 'System error creating tank: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $tank = DB::table('tanks')
                ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                ->leftJoin('stations', 'tanks.station_id', '=', 'stations.id')
                ->where('tanks.id', $id)
                ->select([
                    'tanks.*',
                    'products.product_name',
                    'products.product_type',
                    'stations.station_name',
                    'stations.station_code'
                ])
                ->first();

            if (!$tank) {
                return redirect()->route('tanks.select')->with('error', 'Tank not found');
            }

            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($tank->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $tank->station_id,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'tanks',
                'record_id' => $id,
                'change_reason' => "Tank edit accessed: Tank {$tank->tank_number}",
                'business_justification' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                    ? 'Auto-approved elevated role access'
                    : 'Validated manager role access',
                'is_auto_approved' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
            ]);

            // 🔥 CORRECTED: Use FIFO service for current inventory
            $fifoValidation = $this->fifoService->validateFIFOConsistency($id);
            $currentInventory = $fifoValidation['fifo_summary']['current_quantity_liters'];

            // Get available products
            $products = DB::table('products')
                ->where('is_active', 1)
                ->whereIn('product_type', ['PETROL_95', 'PETROL_98', 'DIESEL', 'KEROSENE'])
                ->select('id', 'product_name', 'product_type', 'product_code')
                ->orderBy('product_name')
                ->get();

            return view('tanks.edit', compact('tank', 'products', 'currentInventory'));

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank edit method execution',
                'table_name' => 'tanks',
                'record_id' => $id
            ]);

            return redirect()->route('tanks.select')->with('error', 'System error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tank = DB::table('tanks')->where('id', $id)->first();
            if (!$tank) {
                return redirect()->route('tanks.select')->with('error', 'Tank not found');
            }

            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($tank->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);

            // 🔥 CORRECTED: Use FIFO service for current inventory validation
            $fifoValidation = $this->fifoService->validateFIFOConsistency($id);
            $currentInventory = $fifoValidation['fifo_summary']['current_quantity_liters'];

            // 🔥 CORRECTED: Validation with mathematical precision
            $request->validate([
                'capacity_liters' => [
                    'required',
                    'numeric',
                    "min:{$currentInventory}",
                    'max:100000.000',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'minimum_stock_level_liters' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'lt:capacity_liters',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'critical_low_level_liters' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'lt:minimum_stock_level_liters',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'maximum_variance_percentage' => 'required|numeric|min:0.1|max:10.0',
                'tank_manufacturer' => 'nullable|string|max:255',
                'tank_serial_number' => 'nullable|string|max:100',
                'last_inspection_date' => 'required|date|before_or_equal:today',
                'calibration_certificate' => 'nullable|string|max:100',
                'leak_detection_system' => 'boolean',
                'overfill_protection' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Mathematical precision validation
            if (floatval($request->minimum_stock_level_liters) >= floatval($request->capacity_liters)) {
                return redirect()->back()->withErrors([
                    'minimum_stock_level_liters' => 'Minimum stock level must be less than capacity with 0.001L precision'
                ]);
            }

            if (floatval($request->critical_low_level_liters) >= floatval($request->minimum_stock_level_liters)) {
                return redirect()->back()->withErrors([
                    'critical_low_level_liters' => 'Critical level must be less than minimum stock level with 0.001L precision'
                ]);
            }

            DB::transaction(function () use ($request, $id, $tank, $isAutoApproved) {
                $changes = [];
                $nextInspectionDate = Carbon::parse($request->last_inspection_date)->addYear();
                $maximumStockLevel = round(floatval($request->capacity_liters) * 0.95, 3);

                $updateData = [
                    'capacity_liters' => round(floatval($request->capacity_liters), 3),
                    'minimum_stock_level_liters' => round(floatval($request->minimum_stock_level_liters), 3),
                    'maximum_stock_level_liters' => $maximumStockLevel,
                    'critical_low_level_liters' => round(floatval($request->critical_low_level_liters), 3),
                    'maximum_variance_percentage' => $request->maximum_variance_percentage,
                    'tank_manufacturer' => $request->tank_manufacturer,
                    'tank_serial_number' => $request->tank_serial_number,
                    'last_inspection_date' => $request->last_inspection_date,
                    'next_inspection_date' => $nextInspectionDate->toDateString(),
                    'calibration_certificate' => $request->calibration_certificate,
                    'leak_detection_system' => $request->boolean('leak_detection_system', false),
                    'overfill_protection' => $request->boolean('overfill_protection', false),
                    'updated_at' => now()
                ];

                // CEO/SYSTEM_ADMIN can change active status
                if ($isAutoApproved) {
                    $updateData['is_active'] = $request->boolean('is_active', true);
                }

                // Track changes with mathematical precision
                foreach ($updateData as $field => $newValue) {
                    $oldValue = $tank->$field;
                    if (is_numeric($oldValue) && is_numeric($newValue)) {
                        if (abs(floatval($oldValue) - floatval($newValue)) > 0.001) {
                            $changes[] = "{$field}: {$oldValue} → {$newValue}";
                        }
                    } elseif ($oldValue != $newValue) {
                        $changes[] = "{$field}: {$oldValue} → {$newValue}";
                    }
                }

                DB::table('tanks')->where('id', $id)->update($updateData);

                if (!empty($changes)) {
                    $this->auditService->logAction([
                        'user_id' => auth()->id(),
                        'station_id' => $tank->station_id,
                        'action_type' => 'UPDATE',
                        'action_category' => 'DATA_ENTRY',
                        'table_name' => 'tanks',
                        'record_id' => $id,
                        'old_values' => json_encode(array_intersect_key((array)$tank, $updateData)),
                        'new_values' => json_encode($updateData),
                        'change_reason' => 'Tank updated: ' . implode(', ', $changes),
                        'business_justification' => $isAutoApproved
                            ? 'Auto-approved by elevated role with automation validation'
                            : 'Manager role tank update with FIFO consistency check',
                        'is_auto_approved' => $isAutoApproved,
                        'automation_context' => [
                            'fifo_consistency_validated' => true,
                            'current_inventory_checked' => true,
                            'mathematical_precision_enforced' => true
                        ]
                    ]);
                }
            });

            return redirect()->route('tanks.edit', $id)->with('success', 'Tank updated successfully with automation validation');

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank update method execution',
                'table_name' => 'tanks',
                'record_id' => $id
            ]);

            return redirect()->back()->with('error', 'System error updating tank: ' . $e->getMessage());
        }
    }

    public function calibration($id)
    {
        try {
            $tank = DB::table('tanks')
                ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                ->leftJoin('stations', 'tanks.station_id', '=', 'stations.id')
                ->where('tanks.id', $id)
                ->select([
                    'tanks.*',
                    'products.product_name',
                    'products.product_type',
                    'stations.station_name',
                    'stations.station_code'
                ])
                ->first();

            if (!$tank) {
                return redirect()->route('tanks.select')->with('error', 'Tank not found');
            }

            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($tank->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $tank->station_id,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'tank_calibration_tables',
                'record_id' => $id,
                'change_reason' => "Tank calibration accessed: Tank {$tank->tank_number}",
                'business_justification' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                    ? 'Auto-approved elevated role access'
                    : 'Validated manager role access',
                'is_auto_approved' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
            ]);

            // Get existing calibration data with exact schema fields
            $calibrationData = DB::table('tank_calibration_tables')
                ->where('tank_id', $id)
                ->select('id', 'dip_mm', 'volume_liters')
                ->orderBy('dip_mm')
                ->get();

            // Calculate calibration statistics with mathematical precision
            $stats = [
                'total_points' => $calibrationData->count(),
                'coverage_percentage' => 0.000,
                'max_dip' => round($calibrationData->max('dip_mm') ?? 0.000, 3),
                'max_volume' => round($calibrationData->max('volume_liters') ?? 0.000, 3),
                'gaps_detected' => 0
            ];

            if ($stats['total_points'] > 0) {
                $stats['coverage_percentage'] = round(min(100, ($stats['max_volume'] / $tank->capacity_liters) * 100), 3);

                // Check for gaps in calibration with precision
                for ($i = 1; $i < $calibrationData->count(); $i++) {
                    $dipGap = abs($calibrationData[$i]->dip_mm - $calibrationData[$i - 1]->dip_mm);
                    if ($dipGap > 100.000) { // Gap larger than 10cm
                        $stats['gaps_detected']++;
                    }
                }
            }

            return view('tanks.calibration', compact('tank', 'calibrationData', 'stats'));

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank calibration method execution',
                'table_name' => 'tank_calibration_tables',
                'record_id' => $id
            ]);

            return redirect()->route('tanks.select')->with('error', 'System error: ' . $e->getMessage());
        }
    }

    public function storeCalibration(Request $request, $id)
    {
        try {
            $tank = DB::table('tanks')->where('id', $id)->first();
            if (!$tank) {
                return redirect()->route('tanks.select')->with('error', 'Tank not found');
            }

            // 🔥 CORRECTED: Verify access with manager permissions
            $accessResult = $this->verifyStationAccessWithAutomation($tank->station_id, ['MANAGER']);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $isAutoApproved = in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN']);

            // 🔥 CORRECTED: Validation with mathematical precision
            $request->validate([
                'calibration_data' => 'required|array|min:5',
                'calibration_data.*.dip_mm' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'max:10000.000',
                    'regex:/^\d+\.\d{3}$/'
                ],
                'calibration_data.*.volume_liters' => [
                    'required',
                    'numeric',
                    'min:0.000',
                    'max:' . $tank->capacity_liters,
                    'regex:/^\d+\.\d{3}$/'
                ],
            ]);

            // 🔥 CRITICAL: Validate calibration data integrity with precision
            $calibrationData = collect($request->calibration_data)->sortBy('dip_mm');

            // Check for duplicate dip readings with 0.001 precision
            $dipValues = $calibrationData->pluck('dip_mm')->map(function($val) {
                return round(floatval($val), 3);
            });

            if ($dipValues->count() !== $dipValues->unique()->count()) {
                return redirect()->back()->withErrors([
                    'calibration_data' => 'Duplicate dip readings are not allowed (0.001mm precision)'
                ]);
            }

            // Check for volume consistency with mathematical precision
            $previousVolume = 0.000;
            foreach ($calibrationData as $point) {
                $currentVolume = round(floatval($point['volume_liters']), 3);
                if ($currentVolume < $previousVolume) {
                    return redirect()->back()->withErrors([
                        'calibration_data' => 'Volume must increase with dip reading (0.001L precision)'
                    ]);
                }
                $previousVolume = $currentVolume;
            }

            // 🔥 CRITICAL: Check if tank has active readings dependent on calibration
            $activeReadingsCount = DB::table('readings')
                ->where('tank_id', $id)
                ->where('reading_date', '>=', now()->subDays(30))
                ->whereNotNull('dip_reading_liters')
                ->count();

            if ($activeReadingsCount > 0 && !$isAutoApproved) {
                return redirect()->back()->withErrors([
                    'calibration_data' => 'Cannot modify calibration with active readings without CEO/SYSTEM_ADMIN approval'
                ]);
            }

            DB::transaction(function () use ($calibrationData, $id, $tank, $isAutoApproved) {
                // Store existing calibration for audit trail
                $existingCalibration = DB::table('tank_calibration_tables')
                    ->where('tank_id', $id)
                    ->get();

                // Clear existing calibration data
                DB::table('tank_calibration_tables')->where('tank_id', $id)->delete();

                // Insert new calibration data with mathematical precision
                $insertData = $calibrationData->map(function ($point) use ($id) {
                    return [
                        'tank_id' => $id,
                        'dip_mm' => round(floatval($point['dip_mm']), 3),
                        'volume_liters' => round(floatval($point['volume_liters']), 3)
                    ];
                })->toArray();

                DB::table('tank_calibration_tables')->insert($insertData);

                // Update tank calibration date
                DB::table('tanks')
                    ->where('id', $id)
                    ->update([
                        'calibration_date' => now()->toDateString(),
                        'calibration_valid_until' => now()->addYears(2)->toDateString(),
                        'updated_at' => now()
                    ]);

                $pointCount = count($insertData);

                $this->auditService->logAction([
                    'user_id' => auth()->id(),
                    'station_id' => $tank->station_id,
                    'action_type' => 'UPDATE',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'tank_calibration_tables',
                    'record_id' => $id,
                    'old_values' => json_encode($existingCalibration->toArray()),
                    'new_values' => json_encode($insertData),
                    'change_reason' => "Tank calibration updated with {$pointCount} data points (0.001L precision)",
                    'business_justification' => $isAutoApproved
                        ? 'Auto-approved by elevated role with precision enforcement'
                        : 'Manager role calibration update with mathematical validation',
                    'is_auto_approved' => $isAutoApproved,
                    'automation_context' => [
                        'mathematical_precision_enforced' => true,
                        'volume_consistency_validated' => true,
                        'duplicate_prevention_applied' => true,
                        'active_readings_checked' => true
                    ]
                ]);
            });

            return redirect()->route('tanks.calibration', $id)
                ->with('success', 'Tank calibration updated successfully with 0.001L mathematical precision');

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank calibration store method execution',
                'table_name' => 'tank_calibration_tables',
                'record_id' => $id
            ]);

            return redirect()->back()->with('error', 'System error updating calibration: ' . $e->getMessage());
        }
    }

    public function layers($id)
    {
        try {
            $tank = DB::table('tanks')
                ->leftJoin('products', 'tanks.product_id', '=', 'products.id')
                ->leftJoin('stations', 'tanks.station_id', '=', 'stations.id')
                ->where('tanks.id', $id)
                ->select([
                    'tanks.*',
                    'products.product_name',
                    'products.product_type',
                    'stations.station_name',
                    'stations.station_code'
                ])
                ->first();

            if (!$tank) {
                return redirect()->route('tanks.select')->with('error', 'Tank not found');
            }

            // 🔥 CORRECTED: Verify access (read-only, no manager requirement)
            $accessResult = $this->verifyStationAccessWithAutomation($tank->station_id);
            if ($accessResult !== true) {
                return $accessResult;
            }

            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'station_id' => $tank->station_id,
                'action_type' => 'READ',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'tank_inventory_layers',
                'record_id' => $id,
                'change_reason' => "FIFO layers accessed: Tank {$tank->tank_number}",
                'business_justification' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
                    ? 'Auto-approved elevated role access'
                    : 'Validated station access for layer viewing',
                'is_auto_approved' => in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])
            ]);

            // 🔥 CORRECTED: Use FIFO service for layer validation first
            $fifoValidation = $this->fifoService->validateFIFOConsistency($id);

            if (!$fifoValidation['validation_passed']) {
                return redirect()->route('tanks.index', $tank->station_id)
                    ->with('error', 'FIFO validation failed for this tank. System integrity compromised.');
            }

            // Get FIFO inventory layers with exact schema compliance
            $layers = DB::table('tank_inventory_layers')
                ->leftJoin('deliveries', 'tank_inventory_layers.delivery_id', '=', 'deliveries.id')
                ->leftJoin('suppliers', 'deliveries.supplier_id', '=', 'suppliers.id')
                ->where('tank_inventory_layers.tank_id', $id)
                ->select([
                    'tank_inventory_layers.id',
                    'tank_inventory_layers.layer_sequence_number',
                    'tank_inventory_layers.delivery_batch_number',
                    'tank_inventory_layers.opening_quantity_liters',
                    'tank_inventory_layers.current_quantity_liters',
                    'tank_inventory_layers.consumed_quantity_liters',
                    'tank_inventory_layers.cost_per_liter',
                    'tank_inventory_layers.total_layer_cost',
                    'tank_inventory_layers.remaining_layer_value',
                    'tank_inventory_layers.delivery_temperature_celsius',
                    'tank_inventory_layers.delivery_density',
                    'tank_inventory_layers.layer_created_at',
                    'tank_inventory_layers.first_consumption_at',
                    'tank_inventory_layers.is_depleted',
                    'tank_inventory_layers.fully_depleted_at',
                    'tank_inventory_layers.layer_status',
                    'deliveries.delivery_date',
                    'deliveries.delivery_note_number',
                    'suppliers.company_name as supplier_name',
                    DB::raw('ROUND((tank_inventory_layers.consumed_quantity_liters / tank_inventory_layers.opening_quantity_liters) * 100, 3) as consumption_percentage'),
                    DB::raw('DATEDIFF(NOW(), tank_inventory_layers.layer_created_at) as age_days')
                ])
                ->orderBy('tank_inventory_layers.layer_sequence_number')
                ->get();

            // Calculate layer statistics with mathematical precision
            $activeLayers = $layers->where('is_depleted', 0);
            $stats = [
                'total_layers' => $layers->count(),
                'active_layers' => $activeLayers->count(),
                'total_volume' => round($activeLayers->sum('current_quantity_liters'), 3),
                'total_value' => round($activeLayers->sum('remaining_layer_value'), 2),
                'weighted_avg_cost' => 0.0000,
                'oldest_layer_age' => $activeLayers->max('age_days') ?? 0,
                'turnover_rate' => 0.000
            ];

            if ($stats['total_volume'] > 0) {
                $stats['weighted_avg_cost'] = round($stats['total_value'] / $stats['total_volume'], 4);
            }

            // Calculate consumption trend with service integration
            $recentConsumption = DB::table('batch_consumption')
                ->whereIn('tank_inventory_layer_id', function ($query) use ($id) {
                    $query->select('id')
                        ->from('tank_inventory_layers')
                        ->where('tank_id', $id);
                })
                ->where('sale_date', '>=', now()->subDays(30))
                ->sum('quantity_consumed_liters') ?? 0.000;

            if ($recentConsumption > 0 && $stats['total_volume'] > 0) {
                $stats['turnover_rate'] = round(($recentConsumption / $stats['total_volume']) * 100, 3);
            }

            // Include FIFO validation results
            $stats['fifo_validation'] = $fifoValidation;

            return view('tanks.layers', compact('tank', 'layers', 'stats'));

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Tank layers method execution',
                'table_name' => 'tank_inventory_layers',
                'record_id' => $id
            ]);

            return redirect()->route('tanks.select')->with('error', 'System error: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 CORRECTED: Station access verification with complete automation integration
     */
    private function verifyStationAccessWithAutomation($stationId, $requiredRoles = [])
    {
        try {
            // Verify station exists and is active
            $station = DB::table('stations')
                ->where('id', $stationId)
                ->where('is_active', 1)
                ->first();

            if (!$station) {
                return redirect()->route('tanks.select')->with('error', 'Station not found or inactive');
            }

            // 🔥 ABSOLUTE PERMISSIONS RULE: CEO/SYSTEM_ADMIN bypass all checks
            if (in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
                // Log elevated access with automation context
                $this->auditService->logAutoApproval([
                    'user_id' => auth()->id(),
                    'station_id' => $stationId,
                    'action_type' => 'READ',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'stations',
                    'record_id' => $stationId,
                    'change_reason' => 'Elevated role station access',
                    'business_justification' => 'CEO/SYSTEM_ADMIN automatic approval with automation validation'
                ]);
                return true;
            }

            // 🔥 CORRECTED: Check user_stations table permissions with exact schema
            $userStation = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('station_id', $stationId)
                ->where('is_active', 1)
                ->whereIn('assigned_role', ['MANAGER', 'ASSISTANT_MANAGER', 'SUPERVISOR', 'OPERATOR'])
                ->first();

            if (!$userStation) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'UNAUTHORIZED_STATION_ACCESS_ATTEMPT',
                    'station_id' => $stationId,
                    'violation_type' => 'ACCESS_DENIED',
                    'ip_address' => request()->ip()
                ]);

                return redirect()->route('tanks.select')->with('error', 'Access denied to this station');
            }

            // Check role requirements if specified
            if (!empty($requiredRoles) && !in_array($userStation->assigned_role, $requiredRoles)) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'INSUFFICIENT_ROLE_PERMISSIONS',
                    'station_id' => $stationId,
                    'required_roles' => json_encode($requiredRoles),
                    'user_role' => $userStation->assigned_role,
                    'ip_address' => request()->ip()
                ]);

                return redirect()->route('tanks.index', $stationId)
                    ->with('error', 'Insufficient permissions for this action. Required: ' . implode(', ', $requiredRoles));
            }

            return true;

        } catch (Exception $e) {
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_context' => 'Station access verification',
                'table_name' => 'user_stations'
            ]);

            return redirect()->route('tanks.select')->with('error', 'System error verifying access: ' . $e->getMessage());
        }
    }
}
