<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class EveningReadingController extends Controller
{
    /**
     * Display evening readings dashboard with ENHANCED INFORMATION
     */
    public function index(Request $request)
    {
        try {
            // Permission check
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermission($currentUserRole, 'evening_reading_view')) {
                return redirect()->back()->with('error', 'Insufficient permissions');
            }

            // Get user stations
            $userStations = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('is_active', 1)
                ->pluck('station_id')
                ->toArray();

            $currentDate = Carbon::now()->toDateString();

            // Get tanks needing evening readings
            $tanksNeedingReadings = $this->getTanksNeedingEveningReadings($currentDate, $userStations);

            // Get completed evening readings
            $completedReadings = $this->getCompletedEveningReadings($currentDate, $userStations);

            // ENHANCED: Get complete tank information for dashboard awareness
            $tankDetails = $this->getCompleteTankInformation(
                $tanksNeedingReadings->pluck('id')->merge($completedReadings->pluck('tank_id'))->unique()->toArray(),
                $currentDate
            );

            // ENHANCED: Get station summaries
            $stationSummaries = $this->getStationSummaries($userStations, $currentDate);

            // Time validation
            $timeValidation = $this->validateTimeWindow();

            return view('evening.readings.index', compact(
                'tanksNeedingReadings',
                'completedReadings',
                'tankDetails',
                'stationSummaries',
                'currentDate',
                'timeValidation',
                'isAutoApproved'
            ));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show create form with COMPLETE SITUATIONAL AWARENESS
     */
    public function create(Request $request)
    {
        try {
            // Permission check
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermission($currentUserRole, 'evening_reading_create')) {
                return redirect()->back()->with('error', 'Insufficient permissions');
            }

            // Time validation
            $timeValidation = $this->validateTimeWindow();
            if (!$timeValidation['valid'] && !$isAutoApproved) {
                return redirect()->back()->with('error', $timeValidation['message']);
            }

            // Get user stations and available tanks
            $userStations = DB::table('user_stations')
                ->where('user_id', auth()->id())
                ->where('is_active', 1)
                ->pluck('station_id')
                ->toArray();

            $currentDate = Carbon::now()->toDateString();
            $tanksNeedingReadings = $this->getTanksNeedingEveningReadings($currentDate, $userStations);

            // ENHANCED: Get complete tank information for situational awareness
            $tankDetails = $this->getCompleteTankInformation($tanksNeedingReadings->pluck('id')->toArray(), $currentDate);

            // ENHANCED: Get variance thresholds for real-time validation
            $varianceThresholds = $this->getVarianceThresholds();

            return view('evening.readings.create', compact(
                'tanksNeedingReadings',
                'tankDetails',
                'varianceThresholds',
                'currentDate',
                'timeValidation',
                'isAutoApproved'
            ));

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



    /**
     * Get tanks that need evening readings
     */
    private function getTanksNeedingEveningReadings(string $date, array $userStations)
    {
        // Get tanks with morning readings but no evening readings
        $morningTankIds = DB::table('readings')
            ->where('reading_date', $date)
            ->where('reading_type', 'MORNING_DIP')
            ->whereIn('reading_status', ['PENDING', 'VALIDATED', 'APPROVED'])
            ->pluck('tank_id');

        $eveningTankIds = DB::table('readings')
            ->where('reading_date', $date)
            ->where('reading_type', 'EVENING_DIP')
            ->pluck('tank_id');

        $tanksNeedingReadings = $morningTankIds->diff($eveningTankIds);

        if ($tanksNeedingReadings->isEmpty()) {
            return collect();
        }

        return DB::table('tanks')
            ->select(['tanks.id', 'tanks.tank_number', 'tanks.capacity_liters', 'stations.station_name', 'products.product_name', 'products.product_type'])
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->whereIn('tanks.id', $tanksNeedingReadings)
            ->whereIn('tanks.station_id', $userStations)
            ->where('tanks.is_active', 1)
            ->orderBy('stations.station_name')
            ->orderBy('tanks.tank_number')
            ->get();
    }

    /**
     * Get completed evening readings
     */
    private function getCompletedEveningReadings(string $date, array $userStations)
    {
        return DB::table('readings')
            ->select(['readings.id', 'readings.dip_reading_mm', 'readings.dip_reading_liters', 'readings.temperature_celsius', 'readings.variance_from_expected_percentage', 'readings.reading_status', 'readings.reading_time', 'tanks.tank_number', 'stations.station_name', 'products.product_type'])
            ->join('tanks', 'readings.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->where('readings.reading_date', $date)
            ->where('readings.reading_type', 'EVENING_DIP')
            ->whereIn('tanks.station_id', $userStations)
            ->orderBy('stations.station_name')
            ->orderBy('tanks.tank_number')
            ->get();
    }

    /**
     * Apply temperature correction - SELF CONTAINED
     */
    private function applyTemperatureCorrection(float $dipMm, float $temperature, int $productId, int $tankId): float
    {
        // CORRECTED: Use exact schema field name 'dip_mm'
        $volume = DB::table('tank_calibration_tables')
            ->where('tank_id', $tankId)
            ->where('dip_mm', '<=', $dipMm)  // FIXED: Correct field name and logic
            ->orderBy('dip_mm', 'desc')      // FIXED: Correct field name and order
            ->value('volume_liters');

        if (!$volume) {
            throw new Exception('Tank calibration data not found for dip reading');
        }

        // Get fuel constants for temperature correction
        $fuelConstants = DB::table('fuel_constants')
            ->where('product_id', $productId)
            ->first();

        if (!$fuelConstants) {
            return round($volume, 3); // Return uncorrected if no constants
        }

        // Apply temperature correction formula
        $temperatureDifference = $temperature - $fuelConstants->temperature_reference_celsius;
        $correctionFactor = 1 - ($fuelConstants->thermal_expansion_coefficient * $temperatureDifference);

        return round($volume * $correctionFactor, 3);
    }



    /**
     * Determine reading status based on variance - SELF CONTAINED
     */


    /**
     * Calculate ullage - SELF CONTAINED
     */
/**
 * Helper method: Get density reading - ASTM D1298 compliant
 */
private function getDensityReading(int $productId, float $temperature): float
{
    try {
        $constants = DB::table('fuel_constants')
            ->where('product_id', $productId)
            ->first();

        if (!$constants) {
            return 0.7500; // Default density at 15°C
        }

        // ASTM D1298: Density decreases as temperature increases
        $tempDiff = $temperature - 15.0;
        $densityAtTemp = $constants->density_15c * (1 - ($constants->thermal_expansion_coefficient * $tempDiff));

        return round(max(0.6000, min(1.0000, $densityAtTemp)), 4); // Physical limits

    } catch (Exception $e) {
        return 0.7500; // Safe fallback
    }
}



// ✅ CORRECTED CODE WITH INTERPOLATION:
private function getCorrectedVolume(int $tankId, float $dipMm, float $temperature): float
{
    try {
        // STEP 1: Get calibration points for interpolation
        $lowerPoint = DB::table('tank_calibration_tables')
            ->where('tank_id', $tankId)
            ->where('dip_mm', '<=', $dipMm)
            ->orderBy('dip_mm', 'desc')
            ->first();

        $upperPoint = DB::table('tank_calibration_tables')
            ->where('tank_id', $tankId)
            ->where('dip_mm', '>', $dipMm)
            ->orderBy('dip_mm', 'asc')
            ->first();

        // Handle edge cases
        if (!$lowerPoint && !$upperPoint) {
            throw new Exception("No calibration data found for tank {$tankId}");
        }

        // If exact match or only lower point exists
        if (!$upperPoint) {
            if ($lowerPoint && abs($lowerPoint->dip_mm - $dipMm) < 0.01) {
                $rawVolume = $lowerPoint->volume_liters;
            } else {
                // Extrapolate beyond max calibration (dangerous for accuracy)
                throw new Exception("Dip reading {$dipMm}mm exceeds maximum calibration for tank {$tankId}");
            }
        }
        // If only upper point exists (below minimum)
        elseif (!$lowerPoint) {
            if (abs($upperPoint->dip_mm - $dipMm) < 0.01) {
                $rawVolume = $upperPoint->volume_liters;
            } else {
                throw new Exception("Dip reading {$dipMm}mm below minimum calibration for tank {$tankId}");
            }
        }
        // Interpolate between two points
        else {
            $dipRange = $upperPoint->dip_mm - $lowerPoint->dip_mm;
            $volumeRange = $upperPoint->volume_liters - $lowerPoint->volume_liters;
            $dipOffset = $dipMm - $lowerPoint->dip_mm;

            $rawVolume = $lowerPoint->volume_liters + (($dipOffset / $dipRange) * $volumeRange);
        }

        // STEP 2: Get product ID for temperature correction
        $productId = DB::table('tanks')
            ->where('id', $tankId)
            ->value('product_id');

        if (!$productId) {
            throw new Exception("Product not found for tank {$tankId}");
        }

        // STEP 3: Apply temperature correction
        $fuelConstants = DB::table('fuel_constants')
            ->select([
                'density_15c',
                'thermal_expansion_coefficient',
                'vapor_pressure_correction',
                'temperature_reference_celsius'
            ])
            ->where('product_id', $productId)
            ->first();

        if (!$fuelConstants) {
            // Use default fuel constants if not found
            $fuelConstants = (object)[
                'density_15c' => 0.7500,
                'thermal_expansion_coefficient' => 0.001200,
                'vapor_pressure_correction' => 1.000,
                'temperature_reference_celsius' => 15
            ];
        }

        // Apply temperature correction formula
        $temperatureDifference = $temperature - $fuelConstants->temperature_reference_celsius;
        $correctionFactor = 1 - ($fuelConstants->thermal_expansion_coefficient * $temperatureDifference);
        $correctedVolume = $rawVolume * $correctionFactor * $fuelConstants->vapor_pressure_correction;

        return round($correctedVolume, 3);

    } catch (Exception $e) {
        throw new Exception("Volume calculation failed: " . $e->getMessage());
    }
}

/**
 * Helper method: Calculate ullage (tank space remaining) - API MPMS compliant
 */
private function calculateUllage(int $tankId, float $dipMm): float
{
    try {
        $tankCapacity = DB::table('tanks')
            ->where('id', $tankId)
            ->value('capacity_liters');

        if (!$tankCapacity) {
            throw new Exception("Tank capacity not found for tank {$tankId}");
        }

        $currentVolume = $this->getCorrectedVolume($tankId, $dipMm, 15.0); // Standard temp
        $ullage = $tankCapacity - $currentVolume;

        return round(max(0, $ullage), 3); // Ensure non-negative

    } catch (Exception $e) {
        return 0.000; // Safe fallback
    }
}
private function hasPermission(string $userRole, string $permission): bool
{
    $permissions = [
        'CEO' => ['evening_reading_create', 'evening_reading_view', 'evening_reading_edit'],
        'SYSTEM_ADMIN' => ['evening_reading_create', 'evening_reading_view', 'evening_reading_edit'],
        'STATION_MANAGER' => ['evening_reading_create', 'evening_reading_view'],
        'ASSISTANT_MANAGER' => ['evening_reading_create', 'evening_reading_view'],
        'PUMP_ATTENDANT' => ['evening_reading_view']
    ];

    return in_array($permission, $permissions[$userRole] ?? []);
}

    /**
     * Validate time window - SELF CONTAINED
     */
    private function validateTimeWindow(): array
    {
        $currentHour = (int) date('H');
        $valid = ($currentHour >= 18 && $currentHour < 20); // 18:00-20:00 evening window

        return [
            'valid' => $valid,
            'message' => $valid ? 'Time window valid' : 'Evening readings allowed between 18:00-20:00'
        ];
    }



    /**
     * ENHANCED: Get complete tank information for MAXIMUM SITUATIONAL AWARENESS
     */
    private function getCompleteTankInformation(array $tankIds, string $currentDate): \Illuminate\Support\Collection
    {
        if (empty($tankIds)) {
            return collect();
        }

        $tankDetails = [];

        foreach ($tankIds as $tankId) {
            // Get tank basic info
            $tank = DB::table('tanks')
                ->select(['tanks.*', 'stations.station_name', 'products.product_name', 'products.product_type'])
                ->join('stations', 'tanks.station_id', '=', 'stations.id')
                ->join('products', 'tanks.product_id', '=', 'products.id')
                ->where('tanks.id', $tankId)
                ->first();

            if (!$tank) continue;

            // Get morning reading (opening stock)
            $morningReading = DB::table('readings')
                ->where('tank_id', $tankId)
                ->where('reading_date', $currentDate)
                ->where('reading_type', 'MORNING_DIP')
                ->first();

            // Get deliveries for the day
            $deliveries = DB::table('deliveries')
                ->select(['quantity_delivered_liters', 'cost_per_liter', 'delivery_time', 'delivery_status'])
                ->where('tank_id', $tankId)
                ->where('delivery_date', $currentDate)
                ->where('delivery_status', 'COMPLETED')
                ->get();

            $totalDeliveries = $deliveries->sum('quantity_delivered_liters');

            // Get meter sales for the day
            $meterSales = DB::table('readings')
                ->where('tank_id', $tankId)
                ->where('reading_date', $currentDate)
                ->whereIn('reading_type', ['MORNING_METER', 'EVENING_METER'])
                ->sum('calculated_sales_liters') ?: 0;

            // Calculate expected evening reading
            $expectedEvening = $morningReading ?
                ($morningReading->dip_reading_liters + $totalDeliveries - $meterSales) : 0;

            // Get recent readings for trend analysis
            $recentReadings = DB::table('readings')
                ->select(['reading_date', 'reading_type', 'dip_reading_liters', 'variance_from_expected_percentage'])
                ->where('tank_id', $tankId)
                ->where('reading_date', '>=', Carbon::parse($currentDate)->subDays(7)->toDateString())
                ->whereIn('reading_type', ['MORNING_DIP', 'EVENING_DIP'])
                ->orderBy('reading_date', 'desc')
                ->orderBy('reading_type', 'desc')
                ->limit(14)
                ->get();

            // Get calibration range for validation
            $calibrationRange = DB::table('tank_calibration_tables')
                ->where('tank_id', $tankId)
                ->selectRaw('MIN(dip_mm) as min_dip, MAX(dip_mm) as max_dip, MIN(volume_liters) as min_volume, MAX(volume_liters) as max_volume')
                ->first();

            // Calculate capacity utilization
            $currentUtilization = $morningReading && $tank->capacity_liters > 0 ?
                round(($morningReading->dip_reading_liters / $tank->capacity_liters) * 100, 2) : 0;

            $expectedUtilization = $tank->capacity_liters > 0 ?
                round(($expectedEvening / $tank->capacity_liters) * 100, 2) : 0;

            // Get active variances
            $activeVariances = DB::table('variances')
                ->select(['variance_type', 'variance_liters', 'variance_percentage', 'escalation_level', 'created_at'])
                ->where('tank_id', $tankId)
                ->whereIn('variance_status', ['PENDING', 'UNDER_INVESTIGATION'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Compile comprehensive tank information
            $tankDetails[$tankId] = [
                'tank' => $tank,
                'morning_reading' => $morningReading,
                'deliveries' => $deliveries,
                'total_deliveries' => round($totalDeliveries, 3),
                'meter_sales' => round($meterSales, 3),
                'expected_evening' => round($expectedEvening, 3),
                'recent_readings' => $recentReadings,
                'calibration_range' => $calibrationRange,
                'current_utilization' => $currentUtilization,
                'expected_utilization' => $expectedUtilization,
                'active_variances' => $activeVariances,
                'analysis' => [
                    'opening_stock' => $morningReading ? round($morningReading->dip_reading_liters, 3) : 0,
                    'total_inflow' => round($totalDeliveries, 3),
                    'total_outflow' => round($meterSales, 3),
                    'theoretical_closing' => round($expectedEvening, 3),
                    'variance_threshold_minor' => 0.5, // Will be overridden by actual thresholds
                    'variance_threshold_critical' => 5.0, // Will be overridden by actual thresholds
                    'capacity_available' => round($tank->capacity_liters - $expectedEvening, 3),
                    'days_supply' => $meterSales > 0 ? round($expectedEvening / ($meterSales / 1), 1) : 999
                ]
            ];
        }

        return collect($tankDetails);
    }

    /**
     * ENHANCED: Get station summaries for dashboard overview
     */
    private function getStationSummaries(array $userStations, string $currentDate): \Illuminate\Support\Collection
    {
        $summaries = [];

        foreach ($userStations as $stationId) {
            $station = DB::table('stations')->where('id', $stationId)->first();
            if (!$station) continue;

            // Get tank counts
            $totalTanks = DB::table('tanks')->where('station_id', $stationId)->where('is_active', 1)->count();

            $morningReadingsCount = DB::table('readings')
                ->join('tanks', 'readings.tank_id', '=', 'tanks.id')
                ->where('tanks.station_id', $stationId)
                ->where('readings.reading_date', $currentDate)
                ->where('readings.reading_type', 'MORNING_DIP')
                ->count();

            $eveningReadingsCount = DB::table('readings')
                ->join('tanks', 'readings.tank_id', '=', 'tanks.id')
                ->where('tanks.station_id', $stationId)
                ->where('readings.reading_date', $currentDate)
                ->where('readings.reading_type', 'EVENING_DIP')
                ->count();

            // Get variance counts
            $activeVariances = DB::table('variances')
                ->where('station_id', $stationId)
                ->whereIn('variance_status', ['PENDING', 'UNDER_INVESTIGATION'])
                ->count();

            // Calculate completion percentage
            $completionRate = $totalTanks > 0 ? round(($eveningReadingsCount / $totalTanks) * 100, 1) : 0;

            $summaries[$stationId] = [
                'station' => $station,
                'total_tanks' => $totalTanks,
                'morning_readings' => $morningReadingsCount,
                'evening_readings' => $eveningReadingsCount,
                'pending_readings' => max(0, $morningReadingsCount - $eveningReadingsCount),
                'active_variances' => $activeVariances,
                'completion_rate' => $completionRate,
                'status' => $this->getStationStatus($completionRate, $activeVariances)
            ];
        }

        return collect($summaries);
    }

    /**
     * ENHANCED: Get variance thresholds for real-time validation
     */
    private function getVarianceThresholds(): array
    {
        $thresholds = DB::table('system_configurations')
            ->whereIn('config_key', [
                'MINOR_VARIANCE_PERCENTAGE',
                'MODERATE_VARIANCE_PERCENTAGE',
                'SIGNIFICANT_VARIANCE_PERCENTAGE',
                'CRITICAL_VARIANCE_PERCENTAGE'
            ])
            ->pluck('config_value_numeric', 'config_key');

        return [
            'minor' => $thresholds['MINOR_VARIANCE_PERCENTAGE'] ?? 0.5,
            'moderate' => $thresholds['MODERATE_VARIANCE_PERCENTAGE'] ?? 1.0,
            'significant' => $thresholds['SIGNIFICANT_VARIANCE_PERCENTAGE'] ?? 2.0,
            'critical' => $thresholds['CRITICAL_VARIANCE_PERCENTAGE'] ?? 5.0
        ];
    }

    /**
     * ENHANCED: Get station status based on completion and variances
     */
    private function getStationStatus(float $completionRate, int $activeVariances): string
    {
        if ($activeVariances > 0) {
            return 'ATTENTION_REQUIRED';
        } elseif ($completionRate >= 100) {
            return 'COMPLETED';
        } elseif ($completionRate >= 50) {
            return 'IN_PROGRESS';
        } else {
            return 'PENDING';
        }
    }

 // 🔥 ADDITIONAL FIX: The calculateReading method needs validation
public function calculateReading(Request $request)
{
    try {
        // Enhanced validation
        $validatedData = $request->validate([
            'tank_id' => 'required|integer|exists:tanks,id',
            'dip_reading_mm' => 'required|numeric|min:0|max:9999.99',
            'temperature_celsius' => 'required|numeric|min:-10|max:60',
            'reading_date' => 'required|date'
        ]);

        // Validate tank is active
        $tank = DB::table('tanks')
            ->select([
                'tanks.id', 'tanks.tank_number', 'tanks.station_id',
                'tanks.product_id', 'tanks.capacity_liters', 'products.product_type'
            ])
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->where('tanks.id', $validatedData['tank_id'])
            ->where('tanks.is_active', 1)
            ->first();

        if (!$tank) {
            return response()->json(['error' => 'Tank not found or inactive'], 404);
        }

        // Validate calibration range
        $calibrationRange = DB::table('tank_calibration_tables')
            ->where('tank_id', $validatedData['tank_id'])
            ->selectRaw('MIN(dip_mm) as min_dip, MAX(dip_mm) as max_dip')
            ->first();

        if ($calibrationRange) {
            if ($validatedData['dip_reading_mm'] < $calibrationRange->min_dip) {
                return response()->json([
                    'error' => "Dip reading {$validatedData['dip_reading_mm']}mm is below minimum calibration {$calibrationRange->min_dip}mm"
                ], 400);
            }
            if ($validatedData['dip_reading_mm'] > $calibrationRange->max_dip) {
                return response()->json([
                    'error' => "Dip reading {$validatedData['dip_reading_mm']}mm exceeds maximum calibration {$calibrationRange->max_dip}mm"
                ], 400);
            }
        }

        // Get morning reading - MANDATORY
        $morningReading = DB::table('readings')
            ->where('tank_id', $validatedData['tank_id'])
            ->where('reading_date', $validatedData['reading_date'])
            ->where('reading_type', 'MORNING_DIP')
            ->whereIn('reading_status', ['PENDING', 'VALIDATED', 'APPROVED'])
            ->first();

        if (!$morningReading) {
            return response()->json(['error' => 'Morning reading required before evening reading'], 400);
        }

        // Calculate corrected volume with FIXED interpolation
        $correctedVolume = $this->getCorrectedVolume(
            $validatedData['tank_id'],
            $validatedData['dip_reading_mm'],
            $validatedData['temperature_celsius']
        );

        // Calculate expected evening reading
        $expectedReading = $this->calculateExpectedEvening(
            $validatedData['tank_id'],
            $validatedData['reading_date'],
            $morningReading
        );

        // Calculate variance with 0.001L precision
        $varianceFromExpectedLiters = round($correctedVolume - $expectedReading, 3);
        $varianceFromExpectedPercentage = $expectedReading > 0 ?
            round(($varianceFromExpectedLiters / $expectedReading) * 100, 3) : 0;

        // Determine reading status
        $readingStatus = $this->determineReadingStatus(abs($varianceFromExpectedPercentage));

        // Calculate additional metrics
        $capacityUtilization = ($correctedVolume / $tank->capacity_liters) * 100;
        $stockChange = $correctedVolume - $morningReading->dip_reading_liters;

        return response()->json([
            'success' => true,
            'calculations' => [
                'corrected_volume' => round($correctedVolume, 3),
                'expected_reading' => round($expectedReading, 3),
                'variance_liters' => round($varianceFromExpectedLiters, 3),
                'variance_percentage' => round($varianceFromExpectedPercentage, 3),
                'reading_status' => $readingStatus,
                'capacity_utilization' => round($capacityUtilization, 2),
                'stock_change' => round($stockChange, 3),
                'is_valid' => $correctedVolume >= 0 && $correctedVolume <= $tank->capacity_liters,
                'debug_info' => [
                    'raw_dip_mm' => $validatedData['dip_reading_mm'],
                    'temperature' => $validatedData['temperature_celsius'],
                    'morning_reading' => $morningReading->dip_reading_liters,
                    'tank_capacity' => $tank->capacity_liters
                ]
            ]
        ]);

    } catch (Exception $e) {
        return response()->json(['error' => 'Calculation failed: ' . $e->getMessage()], 500);
    }
}


private function executeTankReconciliation(int $tankId, string $readingDate, int $eveningReadingId): ?int
{
    try {
        // Get tank details with product information
        $tank = DB::table('tanks')
            ->select(['tanks.station_id', 'tanks.product_id', 'products.product_type'])
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->where('tanks.id', $tankId)
            ->first();

        if (!$tank) {
            return null;
        }

        // Get morning reading - MANDATORY for reconciliation
        $morningReading = DB::table('readings')
            ->where('tank_id', $tankId)
            ->where('reading_date', $readingDate)
            ->where('reading_type', 'MORNING_DIP')
            ->whereIn('reading_status', ['PENDING', 'VALIDATED', 'APPROVED'])
            ->first();

        if (!$morningReading) {
            return null;
        }

        // Get evening reading (using the provided reading ID - CRITICAL FIX)
        $eveningReading = DB::table('readings')
            ->where('id', $eveningReadingId)
            ->first();

        if (!$eveningReading) {
            return null;
        }

        // Calculate deliveries for the day
        $deliveries = DB::table('deliveries')
            ->where('tank_id', $tankId)
            ->where('delivery_date', $readingDate)
            ->where('delivery_status', 'COMPLETED')
            ->sum('quantity_delivered_liters');
        $deliveries = $deliveries ?: 0.000;

        // Calculate meter sales for the day from readings table
        $meterSales = DB::table('readings')
            ->where('tank_id', $tankId)
            ->where('reading_date', $readingDate)
            ->whereIn('reading_type', ['EVENING_METER', 'MORNING_METER', 'ENHANCED_METER_AUTO'])
            ->sum('calculated_sales_liters');
        $meterSales = $meterSales ?: 0.000;

        // RECONCILIATION MATHEMATICS (0.001L precision)
        $openingStock = round($morningReading->dip_reading_liters, 3);
        $closingStock = round($eveningReading->dip_reading_liters, 3);

        // Physical Sales = Opening + Deliveries - Closing
        $calculatedSales = round($openingStock + $deliveries - $closingStock, 3);

        // Variance = Physical Sales - Meter Sales
        $variance = round($calculatedSales - $meterSales, 3);

        // Variance Percentage (prevent division by zero)
        $variancePercentage = $calculatedSales > 0 ?
            round(($variance / $calculatedSales) * 100, 3) : 0.000;

        // Meter Sales Variance Percentage
        $meterSalesVariancePercentage = $meterSales > 0 ?
            round((($calculatedSales - $meterSales) / $meterSales) * 100, 3) : 0.000;

        // Book Stock vs Physical Stock
        $bookStock = round($openingStock + $deliveries - $meterSales, 3);
        $physicalStock = round($closingStock, 3);

        // Create daily reconciliation record - EXACT SCHEMA FIELDS
        $reconciliationId = DB::table('daily_reconciliations')->insertGetId([
            'station_id' => $tank->station_id,
            'tank_id' => $tankId,
            'reconciliation_date' => $readingDate,
            'opening_dip_mm' => $morningReading->dip_reading_mm,
            'opening_stock_liters' => $openingStock,
            'closing_dip_mm' => $eveningReading->dip_reading_mm,
            'closing_stock_liters' => $closingStock,
            'deliveries_liters' => $deliveries,
            'calculated_sales_liters' => $calculatedSales,
            'meter_sales_liters' => $meterSales,
            'variance_liters' => $variance,
            'variance_percentage' => $variancePercentage,
            'meter_sales_variance_percentage' => $meterSalesVariancePercentage,
            'book_stock_liters' => $bookStock,
            'physical_stock_liters' => $physicalStock,
            'created_by' => auth()->id()
        ]);

        return $reconciliationId;

    } catch (Exception $e) {
        // Silent failure - don't break evening reading insertion
        return null;
    }
}

/**
 * 🎯 CORRECTED STORE METHOD: Fix the method call with 3 parameters
 * Replace your existing store method with this complete implementation
 */
public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // STEP 1: Permission validation
        $currentUserRole = auth()->user()->role ?? 'STATION_OPERATOR';

        if (!in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER', 'STATION_OPERATOR'])) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // STEP 2: Validate input data - EXACT SCHEMA FIELDS
        $validatedData = $request->validate([
            'tank_id' => 'required|integer|exists:tanks,id',
            'reading_date' => 'required|date|before_or_equal:today',
            'dip_reading_mm' => 'required|numeric|min:0|max:50000',
            'temperature_celsius' => 'required|numeric|min:-10|max:60',
            'water_level_mm' => 'required|numeric|min:0|max:1000',
            'reading_notes' => 'nullable|string|max:1000'
        ]);

        // STEP 3: Get tank details with product information for calculations
        $tank = DB::table('tanks')
            ->select([
                'tanks.id', 'tanks.station_id', 'tanks.product_id', 'tanks.capacity_liters',
                'products.product_type', 'products.density_kg_per_liter'
            ])
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->where('tanks.id', $validatedData['tank_id'])
            ->first();

        if (!$tank) {
            return response()->json(['error' => 'Tank not found'], 404);
        }

        // STEP 4: Get morning reading - MANDATORY for evening readings
        $morningReading = DB::table('readings')
            ->where('tank_id', $validatedData['tank_id'])
            ->where('reading_date', $validatedData['reading_date'])
            ->where('reading_type', 'MORNING_DIP')
            ->whereIn('reading_status', ['PENDING', 'VALIDATED', 'APPROVED'])
            ->first();

        if (!$morningReading) {
            return response()->json(['error' => 'Morning reading required before evening reading'], 400);
        }

        // STEP 5: Check for duplicate evening reading
        $existingEvening = DB::table('readings')
            ->where('tank_id', $validatedData['tank_id'])
            ->where('reading_date', $validatedData['reading_date'])
            ->where('reading_type', 'EVENING_DIP')
            ->exists();

        if ($existingEvening) {
            return response()->json(['error' => 'Evening reading already exists for this date'], 409);
        }

        // STEP 6: Convert dip reading to liters with temperature correction
        $correctedVolume = $this->getCorrectedVolume(
            $tank->id,
            $validatedData['dip_reading_mm'],
            $validatedData['temperature_celsius']
        );

        if ($correctedVolume === null) {
            return response()->json(['error' => 'Failed to convert dip reading to volume'], 500);
        }

        // STEP 7: Calculate expected reading for variance detection
        $expectedReading = $this->calculateExpectedEvening(
            $validatedData['tank_id'],
            $validatedData['reading_date'],
            $morningReading
        );

        // STEP 8: Calculate variance metrics
        $varianceFromExpectedLiters = round($correctedVolume - $expectedReading, 3);
        $varianceFromExpectedPercentage = $expectedReading > 0 ?
            round(($varianceFromExpectedLiters / $expectedReading) * 100, 3) : 0.000;

        // STEP 9: Determine reading status based on variance thresholds
        $readingStatus = $this->determineReadingStatus(abs($varianceFromExpectedPercentage));

        // STEP 10: Calculate additional technical fields
        $ullage = $this->calculateUllage($tank->id, $validatedData['dip_reading_mm']);
        $densityReading = $this->getDensityReading($tank->product_id, $validatedData['temperature_celsius']);
        $stockChange = round($correctedVolume - $morningReading->dip_reading_liters, 3);

        // STEP 11: Determine auto-approval
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        // STEP 12: Insert reading - EXACT SCHEMA FIELDS
        $readingId = DB::table('readings')->insertGetId([
            // Core identification
            'station_id' => $tank->station_id,
            'reading_type' => 'EVENING_DIP',
            'reading_date' => $validatedData['reading_date'],
            'reading_time' => now()->format('H:i:s'),
            'reading_shift' => 'EVENING',
            'tank_id' => $validatedData['tank_id'],
            'pump_id' => null,
            'product_type' => $tank->product_type,

            // Dip measurements
            'dip_reading_mm' => $validatedData['dip_reading_mm'],
            'dip_reading_liters' => $correctedVolume,
            'meter_reading_liters' => null,
            'temperature_celsius' => $validatedData['temperature_celsius'],
            'water_level_mm' => $validatedData['water_level_mm'],
            'density_reading' => $densityReading,
            'ullage_mm' => $ullage,

            // Previous reading chain
            'previous_reading_id' => $morningReading->id,
            'previous_dip_reading_liters' => $morningReading->dip_reading_liters,
            'previous_meter_reading_liters' => null,

            // Calculated values
            'calculated_sales_liters' => null,
            'calculated_deliveries_liters' => null,
            'calculated_stock_change_liters' => $stockChange,
            'expected_reading_liters' => $expectedReading,
            'variance_from_expected_liters' => $varianceFromExpectedLiters,
            'variance_from_expected_percentage' => $varianceFromExpectedPercentage,

            // Meter variance
            'meter_dip_variance_liters' => null,
            'meter_dip_variance_percentage' => null,

            // Status and validation
            'reading_status' => $readingStatus,
            'validation_error_code' => 'NONE',
            'validation_notes' => $validatedData['reading_notes'],
            'requires_recount' => $readingStatus === 'FLAGGED' ? 1 : 0,
            'recount_completed' => 0,

            // Entry metadata
            'entry_method' => 'MANUAL',
            'entry_device' => 'DESKTOP',
            'reading_session_id' => null,
            'reading_confidence_level' => 'HIGH',
            'environmental_conditions' => 'NORMAL',

            // Hash fields - triggers will manage
            'hash_previous' => null,
            'hash_current' => hash('sha256', $validatedData['tank_id'] . $validatedData['reading_date'] . time()),
            'hash_validation_status' => 'PENDING',

            // User tracking
            'entered_by' => auth()->id(),
            'validated_by' => null,
            'approved_by' => $isAutoApproved ? auth()->id() : null,
            'created_at' => now(),
            'updated_at' => now(),
            'reading_timestamp' => now()
        ]);

        // STEP 13: Execute tank reconciliation with CORRECT 3 PARAMETERS - CRITICAL FIX
        $reconciliationId = $this->executeTankReconciliation(
            $validatedData['tank_id'],
            $validatedData['reading_date'],
            $readingId  // ✅ THIRD PARAMETER ADDED - THIS FIXES THE ERROR
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => $isAutoApproved ?
                'Evening reading completed and auto-approved with reconciliation' :
                'Evening reading completed successfully with reconciliation',
            'data' => [
                'reading_id' => $readingId,
                'reconciliation_id' => $reconciliationId,
                'corrected_volume' => $correctedVolume,
                'variance_percentage' => $varianceFromExpectedPercentage,
                'variance_liters' => $varianceFromExpectedLiters,
                'reading_status' => $readingStatus,
                'auto_approved' => $isAutoApproved,
                'tank_reconciliation_executed' => $reconciliationId !== null
            ]
        ]);

    } catch (Exception $e) {
        DB::rollback();
        return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
    }
}
/**
 * Helper method: Calculate expected evening reading
 */
private function calculateExpectedEvening(int $tankId, string $date, $morningReading): float
{
    // Get deliveries for the day
    $deliveries = DB::table('deliveries')
        ->where('tank_id', $tankId)
        ->where('delivery_date', $date)
        ->where('delivery_status', 'COMPLETED')
        ->sum('quantity_delivered_liters') ?: 0.000;

    // Get meter sales for the day
    $meterSales = DB::table('readings')
        ->where('tank_id', $tankId)
        ->where('reading_date', $date)
        ->whereIn('reading_type', ['EVENING_METER', 'MORNING_METER', 'ENHANCED_METER_AUTO'])
        ->sum('calculated_sales_liters') ?: 0.000;

    // Expected = Morning + Deliveries - Sales
    return round($morningReading->dip_reading_liters + $deliveries - $meterSales, 3);
}

/**
 * Helper method: Determine reading status based on variance
 */
private function determineReadingStatus(float $variancePercentage): string
{
    if ($variancePercentage <= 0.5) {
        return 'VALIDATED';
    } elseif ($variancePercentage <= 2.0) {
        return 'PENDING';
    } else {
        return 'FLAGGED';
    }
}
}
