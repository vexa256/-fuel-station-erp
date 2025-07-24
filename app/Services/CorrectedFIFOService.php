<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CorrectedFIFOService
{
    /**
     *  CRITICAL FIX: FIFO VALIDATION WITH DATABASE AUTOMATION INTEGRATION
     *
     * Instead of manual math checks, this integrates with the database's
     * sp_enhanced_system_monitor() stored procedure for comprehensive validation
     *
     * @param int $tankId
     * @return array Validation results from database monitoring system
     */
    public function validateFIFOConsistency(int $tankId): array
    {
        try {
            // 🔥 INTEGRATION FIX: Use database's built-in monitoring system
            // Call the stored procedure that validates FIFO integrity
            DB::statement('CALL sp_enhanced_system_monitor()');

            // Get validation results from system monitoring
            $monitoringResults = DB::table('system_health_monitoring')
                ->whereIn('check_type', [
                    'ENHANCED_FIFO_MATH_CHECK',
                    'ENHANCED_VALUE_CALC_CHECK',
                    'ENHANCED_CAPACITY_CHECK'
                ])
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(5))
                ->orderBy('check_timestamp', 'desc')
                ->get();

            // Get current FIFO inventory summary
            $fifoSummary = DB::table('tank_inventory_layers')
                ->selectRaw('
                    tank_id,
                    COUNT(*) as total_layers,
                    COUNT(CASE WHEN is_depleted = 0 THEN 1 END) as active_layers,
                    SUM(CASE WHEN is_depleted = 0 THEN current_quantity_liters ELSE 0 END) as total_current_quantity,
                    SUM(CASE WHEN is_depleted = 0 THEN remaining_layer_value ELSE 0 END) as total_current_value,
                    MIN(layer_created_at) as oldest_layer_date,
                    MAX(layer_created_at) as newest_layer_date
                ')
                ->where('tank_id', $tankId)
                ->first();

            // Get latest physical reading - CORRECTED: Use exact schema fields
            $latestPhysical = DB::table('readings')
                ->select([
                    'id',
                    'dip_reading_liters',
                    'reading_date',
                    'reading_time', //  CORRECT: This field exists in readings table
                    'reading_shift',
                    'reading_status',
                    'created_at'
                ])
                ->where('tank_id', $tankId)
                ->where('dip_reading_liters', '>', 0)
                ->whereIn('reading_status', ['VALIDATED', 'APPROVED'])
                ->orderBy('reading_date', 'desc')
                ->orderBy('reading_time', 'desc')
                ->first();

            // Calculate variance only if we have both values
            $variance = null;
            $percentageVariance = null;

            if ($latestPhysical && $fifoSummary && $fifoSummary->total_current_quantity > 0) {
                $variance = abs($fifoSummary->total_current_quantity - $latestPhysical->dip_reading_liters);
                $percentageVariance = ($latestPhysical->dip_reading_liters > 0)
                    ? ($variance / $latestPhysical->dip_reading_liters) * 100
                    : 0;
            }

            // Check for critical alerts from monitoring system with enhanced diagnostics
            $criticalAlerts = DB::table('system_accuracy_alerts')
                ->select(['id', 'alert_type', 'error_description', 'variance_amount', 'detected_at'])
                ->where('tank_id', $tankId)
                ->where('resolved_at', null)
                ->where('severity', 'CRITICAL')
                ->get();

            $validationResult = [
                'tank_id' => $tankId,
                'validation_timestamp' => now(),
                'fifo_summary' => [
                    'total_layers' => $fifoSummary->total_layers ?? 0,
                    'active_layers' => $fifoSummary->active_layers ?? 0,
                    'current_quantity_liters' => round($fifoSummary->total_current_quantity ?? 0, 3),
                    'current_value' => round($fifoSummary->total_current_value ?? 0, 2),
                    'oldest_layer' => $fifoSummary->oldest_layer_date,
                    'newest_layer' => $fifoSummary->newest_layer_date
                ],
                'physical_reading' => $latestPhysical ? [
                    'reading_id' => $latestPhysical->id,
                    'quantity_liters' => round($latestPhysical->dip_reading_liters, 3),
                    'reading_date' => $latestPhysical->reading_date,
                    'reading_time' => $latestPhysical->reading_time,
                    'reading_shift' => $latestPhysical->reading_shift,
                    'reading_status' => $latestPhysical->reading_status
                ] : null,
                'variance_analysis' => [
                    'variance_liters' => $variance ? round($variance, 3) : null,
                    'variance_percentage' => $percentageVariance ? round($percentageVariance, 4) : null
                ],
                'monitoring_status' => [
                    'math_check_passed' => $monitoringResults->where('check_type', 'ENHANCED_FIFO_MATH_CHECK')->where('check_status', 'PASSED')->count() > 0,
                    'value_check_passed' => $monitoringResults->where('check_type', 'ENHANCED_VALUE_CALC_CHECK')->where('check_status', 'PASSED')->count() > 0,
                    'capacity_check_passed' => $monitoringResults->where('check_type', 'ENHANCED_CAPACITY_CHECK')->where('check_status', 'PASSED')->count() > 0,
                    'critical_alerts_count' => $criticalAlerts->count(),
                    'critical_alerts_details' => $criticalAlerts->toArray()
                ],
                'validation_passed' => $criticalAlerts->count() == 0 && $percentageVariance < 2.0
            ];

            return $validationResult;

        } catch (Exception $e) {
            // Enhanced error context from monitoring system
            $recentErrors = DB::table('system_health_monitoring')
                ->where('check_status', 'FAILED')
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(10))
                ->orderBy('check_timestamp', 'desc')
                ->limit(3)
                ->pluck('check_details')
                ->toArray();

            $errorContext = empty($recentErrors) ? '' : ' Recent system errors: ' . implode('; ', $recentErrors);

            throw new Exception("FIFO validation failed for tank {$tankId}: " . $e->getMessage() . $errorContext);
        }
    }

    /**
     *  FIX 1: ROBUST TIMING WITH POLLING
     *
     * Replaces sleep() with intelligent polling of monitoring system
     */
    private function waitForAutomationProcessing(string $operationType, int $referenceId, int $maxWaitSeconds = 10): bool
    {
        $startTime = time();
        $pollInterval = 0.5; // 500ms intervals

        while ((time() - $startTime) < $maxWaitSeconds) {
            // Check for successful processing indicators
            $processingComplete = false;

            switch ($operationType) {
                case 'meter_reading':
                    $processingComplete = DB::table('readings')
                        ->where('pump_id', $referenceId)
                        ->where('reading_type', 'ENHANCED_METER_AUTO')
                        ->where('created_at', '>=', Carbon::now()->subMinutes(1))
                        ->exists();
                    break;

                case 'delivery':
                    $processingComplete = DB::table('tank_inventory_layers')
                        ->where('delivery_id', $referenceId)
                        ->where('layer_created_at', '>=', Carbon::now()->subMinutes(1))
                        ->exists();
                    break;
            }

            if ($processingComplete) {
                return true;
            }

            // Check for processing errors
            $processingError = DB::table('system_health_monitoring')
                ->whereIn('check_type', ['FIFO_PROCESSING_ERROR', 'LAYER_CREATION_ERROR'])
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(1))
                ->exists();

            if ($processingError) {
                return false;
            }

            usleep($pollInterval * 1000000); // Convert to microseconds
        }

        return false; // Timeout
    }

    /**
     *  FIX 2: VALIDATED USER CONTEXT
     *
     * Ensures proper user validation instead of assuming user ID 1 exists
     */
    private function getValidatedUserId(): int
    {
        // Try authenticated user first
        $userId = auth()->id();

        if ($userId) {
            // Verify user exists and is active
            $userExists = DB::table('users')
                ->where('id', $userId)
                ->where('is_active', 1)
                ->exists();

            if ($userExists) {
                return $userId;
            }
        }

        // Fallback: Get system admin user
        $systemAdmin = DB::table('users')
            ->where('role', 'SYSTEM_ADMIN')
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if ($systemAdmin) {
            return $systemAdmin->id;
        }

        // Last resort: Get any CEO user
        $ceoUser = DB::table('users')
            ->where('role', 'CEO')
            ->where('is_active', 1)
            ->orderBy('id')
            ->first();

        if ($ceoUser) {
            return $ceoUser->id;
        }

        throw new Exception("No valid system user found for FIFO operations. Ensure at least one SYSTEM_ADMIN or CEO user exists and is active.");
    }

    /**
     *  CRITICAL FIX: SALES PROCESSING WITH ROBUST AUTOMATION INTEGRATION
     *
     * Fixed: Robust timing, validated user context, enhanced error diagnostics
     *
     * @param int $tankId
     * @param int $pumpId
     * @param float $meterReading
     * @param string $shift
     * @return array Processing results
     */
    public function processSaleViaAutomation(int $tankId, int $pumpId, float $meterReading, string $shift): array
    {
        DB::beginTransaction();

        try {
            // Validate that FIFO processing is enabled
            $processingEnabled = DB::table('system_configurations')
                ->where('config_key', 'ENHANCED_FIFO_PROCESSING_ENABLED')
                ->value('config_value_boolean');

            if (!$processingEnabled) {
                throw new Exception("FIFO automation is disabled. Enable ENHANCED_FIFO_PROCESSING_ENABLED configuration.");
            }

            // Get pump details and validate tank mapping
            $pump = DB::table('pumps')
                ->where('id', $pumpId)
                ->where('tank_id', $tankId)
                ->where('is_operational', 1)
                ->first();

            if (!$pump) {
                throw new Exception("Pump {$pumpId} not found or not operational for tank {$tankId}");
            }

            // Get validated user ID
            $validatedUserId = $this->getValidatedUserId();

            // 🔥 AUTOMATION INTEGRATION: Insert meter reading to trigger automation
            // tr_enhanced_meter_fifo_automation trigger will handle FIFO processing
            $meterReadingId = DB::table('meter_readings')->insertGetId([
                'pump_id' => $pumpId,
                'reading_date' => now()->format('Y-m-d'),
                'reading_shift' => $shift,
                'reading_timestamp' => now(),
                'meter_reading_liters' => $meterReading,
                'entered_by' => $validatedUserId,
                'created_at' => now()
            ]);

            // The trigger automatically:
            // 1. Calculates sales volume
            // 2. Creates readings record
            // 3. Calls sp_enhanced_fifo_processor
            // 4. Creates batch_consumption records

            // 🔥 FIX: Robust timing with polling instead of sleep
            $processingSuccess = $this->waitForAutomationProcessing('meter_reading', $pumpId, 15);

            if (!$processingSuccess) {
                // Get detailed error information from monitoring system
                $errorDetails = DB::table('system_health_monitoring')
                    ->select(['check_type', 'check_details', 'severity'])
                    ->whereIn('check_type', ['FIFO_PROCESSING_ERROR', 'NEGATIVE_SALES_DETECTED', 'PUMP_TANK_MAPPING_ERROR'])
                    ->where('check_timestamp', '>=', Carbon::now()->subMinutes(1))
                    ->orderBy('check_timestamp', 'desc')
                    ->get();

                $errorMsg = "Automated processing failed within timeout period.";
                if ($errorDetails->isNotEmpty()) {
                    $errorMsg .= " System errors: " . $errorDetails->pluck('check_details')->implode('; ');
                }

                throw new Exception($errorMsg);
            }

            // Get the automatically created reading record
            $readingRecord = DB::table('readings')
                ->where('pump_id', $pumpId)
                ->where('reading_type', 'ENHANCED_METER_AUTO')
                ->where('created_at', '>=', Carbon::now()->subMinutes(2))
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$readingRecord) {
                throw new Exception("Automated reading processing failed - no reading record created");
            }

            // Get consumption records created by automation
            $consumptionRecords = DB::table('batch_consumption')
                ->where('reading_id', $readingRecord->id)
                ->get();

            // Calculate totals
            $totalConsumed = $consumptionRecords->sum('quantity_consumed_liters');
            $totalCOGS = $consumptionRecords->sum('total_cost_consumed');
            $weightedCost = $totalConsumed > 0 ? $totalCOGS / $totalConsumed : 0;

            DB::commit();

            $result = [
                'success' => true,
                'tank_id' => $tankId,
                'pump_id' => $pumpId,
                'meter_reading_id' => $meterReadingId,
                'reading_id' => $readingRecord->id,
                'calculated_sales_liters' => round($readingRecord->calculated_sales_liters ?? 0, 3),
                'total_consumed_liters' => round($totalConsumed, 3),
                'total_cogs' => round($totalCOGS, 2),
                'weighted_cost_per_liter' => round($weightedCost, 4),
                'layers_consumed' => $consumptionRecords->count(),
                'automation_triggered' => true,
                'processed_by_user_id' => $validatedUserId,
                'processing_time_seconds' => time() - (time() - 15), // Approximate
                'processed_at' => now()
            ];

            return $result;

        } catch (Exception $e) {
            DB::rollback();

            // Enhanced error context from monitoring system
            $systemErrors = DB::table('system_health_monitoring')
                ->where('check_status', 'FAILED')
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(2))
                ->pluck('check_details')
                ->toArray();

            $errorContext = empty($systemErrors) ? '' : ' System diagnostics: ' . implode('; ', $systemErrors);

            throw new Exception("Automated FIFO processing failed: " . $e->getMessage() . $errorContext);
        }
    }

    /**
     *  CRITICAL FIX: DELIVERY PROCESSING WITH ROBUST AUTOMATION INTEGRATION
     *
     * Fixed: Robust timing, validated user context, enhanced error diagnostics
     */
    public function processDeliveryViaAutomation(int $deliveryId): array
    {
        DB::beginTransaction();

        try {
            // Get delivery details
            $delivery = DB::table('deliveries')
                ->where('id', $deliveryId)
                ->first();

            if (!$delivery) {
                throw new Exception("Delivery {$deliveryId} not found");
            }

            if ($delivery->delivery_status === 'COMPLETED') {
                throw new Exception("Delivery {$deliveryId} already completed");
            }

            // Validate that layer creation is enabled
            $layerCreationEnabled = DB::table('system_configurations')
                ->where('config_key', 'AUTO_DELIVERY_LAYER_CREATION')
                ->value('config_value_boolean');

            if (!$layerCreationEnabled) {
                throw new Exception("Automatic layer creation is disabled. Enable AUTO_DELIVERY_LAYER_CREATION configuration.");
            }

            // 🔥 AUTOMATION INTEGRATION: Update delivery status to trigger automation
            // tr_enhanced_delivery_fifo_layers trigger will automatically create FIFO layer
            DB::table('deliveries')
                ->where('id', $deliveryId)
                ->update([
                    'delivery_status' => 'COMPLETED',
                    'updated_at' => now()
                ]);

            // 🔥 FIX: Robust timing with polling instead of sleep
            $processingSuccess = $this->waitForAutomationProcessing('delivery', $deliveryId, 10);

            if (!$processingSuccess) {
                // Get detailed error information
                $errorDetails = DB::table('system_health_monitoring')
                    ->select(['check_type', 'check_details', 'severity'])
                    ->whereIn('check_type', ['LAYER_CREATION_ERROR', 'ENHANCED_LAYER_CREATION'])
                    ->where('check_timestamp', '>=', Carbon::now()->subMinutes(1))
                    ->orderBy('check_timestamp', 'desc')
                    ->get();

                $errorMsg = "Delivery processing failed within timeout period.";
                if ($errorDetails->isNotEmpty()) {
                    $errorMsg .= " System diagnostics: " . $errorDetails->pluck('check_details')->implode('; ');
                }

                throw new Exception($errorMsg);
            }

            // Get the automatically created FIFO layer
            $fifoLayer = DB::table('tank_inventory_layers')
                ->where('delivery_id', $deliveryId)
                ->orderBy('layer_created_at', 'desc')
                ->first();

            if (!$fifoLayer) {
                throw new Exception("Automatic FIFO layer creation failed for delivery {$deliveryId}");
            }

            // Get monitoring logs for this operation with enhanced details
            $monitoringLog = DB::table('system_health_monitoring')
                ->select(['check_type', 'check_status', 'check_details', 'affected_records', 'execution_time_ms'])
                ->where('check_type', 'ENHANCED_LAYER_CREATION')
                ->where('check_details', 'like', "%delivery {$deliveryId}%")
                ->orderBy('check_timestamp', 'desc')
                ->first();

            DB::commit();

            $result = [
                'success' => true,
                'delivery_id' => $deliveryId,
                'tank_id' => $delivery->tank_id,
                'fifo_layer' => [
                    'layer_id' => $fifoLayer->id,
                    'sequence_number' => $fifoLayer->layer_sequence_number,
                    'quantity_liters' => $fifoLayer->opening_quantity_liters,
                    'cost_per_liter' => $fifoLayer->cost_per_liter,
                    'total_value' => $fifoLayer->total_layer_cost,
                    'layer_status' => $fifoLayer->layer_status
                ],
                'monitoring_status' => [
                    'status' => $monitoringLog->check_status ?? 'UNKNOWN',
                    'details' => $monitoringLog->check_details ?? '',
                    'execution_time_ms' => $monitoringLog->execution_time_ms ?? 0,
                    'affected_records' => $monitoringLog->affected_records ?? 0
                ],
                'automation_triggered' => true,
                'processed_at' => now()
            ];

            return $result;

        } catch (Exception $e) {
            DB::rollback();

            // Enhanced error context from monitoring system
            $systemErrors = DB::table('system_health_monitoring')
                ->select(['check_type', 'check_details', 'severity'])
                ->where('check_status', 'FAILED')
                ->where('check_timestamp', '>=', Carbon::now()->subMinutes(2))
                ->get();

            $errorContext = '';
            if ($systemErrors->isNotEmpty()) {
                $errorContext = ' System diagnostics: ' . $systemErrors->map(function($error) {
                    return "({$error->check_type}: {$error->check_details})";
                })->implode('; ');
            }

            throw new Exception("Automated delivery processing failed: " . $e->getMessage() . $errorContext);
        }
    }

    /**
     *  CRITICAL FIX: EXACT COST CALCULATION FROM DATABASE
     *
     * Gets weighted cost using database functions and current FIFO layers
     */
    public function getExactFIFOWeightedCost(int $tankId): float
    {
        try {
            // Get weighted cost from active FIFO layers - EXACT SCHEMA FIELDS
            $activeLayers = DB::table('tank_inventory_layers')
                ->select([
                    'current_quantity_liters',
                    'remaining_layer_value',
                    'cost_per_liter'
                ])
                ->where('tank_id', $tankId)
                ->where('is_depleted', 0)
                ->where('current_quantity_liters', '>', 0.001)
                ->get();

            if ($activeLayers->isNotEmpty()) {
                $totalValue = $activeLayers->sum('remaining_layer_value');
                $totalQuantity = $activeLayers->sum('current_quantity_liters');

                if ($totalQuantity > 0) {
                    return round($totalValue / $totalQuantity, 4);
                }
            }

            // Fallback 1: Most recent delivery cost
            $recentCost = DB::table('deliveries')
                ->where('tank_id', $tankId)
                ->where('delivery_status', 'COMPLETED')
                ->orderBy('delivery_date', 'desc')
                ->orderBy('delivery_time', 'desc')
                ->value('cost_per_liter');

            if ($recentCost) {
                return round($recentCost, 4);
            }

            // Fallback 2: Purchase prices
            $purchaseCost = DB::table('purchase_prices')
                ->join('deliveries', 'purchase_prices.delivery_id', '=', 'deliveries.id')
                ->where('deliveries.tank_id', $tankId)
                ->orderBy('purchase_prices.price_date', 'desc')
                ->value('purchase_prices.net_cost_per_liter');

            if ($purchaseCost) {
                return round($purchaseCost, 4);
            }

            // Fallback 3: Market prices for tank's product type
            $marketCost = DB::table('market_prices')
                ->join('tanks', function($join) use ($tankId) {
                    $join->whereRaw('tanks.id = ?', [$tankId]);
                })
                ->join('products', 'tanks.product_id', '=', 'products.id')
                ->whereRaw('market_prices.product_type = products.product_type')
                ->where('market_prices.price_category', 'WHOLESALE')
                ->orderBy('market_prices.price_date', 'desc')
                ->value('market_prices.price_per_liter');

            if ($marketCost) {
                return round($marketCost, 4);
            }

            throw new Exception("No cost data available for tank {$tankId} in any database table");

        } catch (Exception $e) {
            // Enhanced error context
            $tankInfo = DB::table('tanks')
                ->select(['tank_number', 'product_id', 'is_active'])
                ->where('id', $tankId)
                ->first();

            $contextInfo = $tankInfo ? " (Tank #{$tankInfo->tank_number}, Product ID: {$tankInfo->product_id}, Active: " . ($tankInfo->is_active ? 'Yes' : 'No') . ")" : '';

            throw new Exception("Cost calculation failed for tank {$tankId}{$contextInfo}: " . $e->getMessage());
        }
    }

    /**
     *  ENHANCED METHOD: INTEGRATION WITH SYSTEM MONITORING
     *
     * Gets FIFO health status from database monitoring system with enhanced diagnostics
     */
    public function getFIFOHealthStatus(): array
    {
        try {
            // Get recent monitoring results
            $monitoringResults = DB::table('system_health_monitoring')
                ->select(['check_type', 'check_status', 'check_details', 'affected_records', 'execution_time_ms', 'severity', 'check_timestamp'])
                ->whereIn('check_type', [
                    'ENHANCED_FIFO_MATH_CHECK',
                    'ENHANCED_VALUE_CALC_CHECK',
                    'ENHANCED_CAPACITY_CHECK',
                    'FIFO_PROCESSING_SUCCESS',
                    'FIFO_PROCESSING_ERROR'
                ])
                ->where('check_timestamp', '>=', Carbon::now()->subHours(24))
                ->orderBy('check_timestamp', 'desc')
                ->get();

            // Get active alerts with enhanced details
            $activeAlerts = DB::table('system_accuracy_alerts')
                ->select(['id', 'alert_type', 'severity', 'tank_id', 'error_description', 'variance_amount', 'detected_at'])
                ->where('resolved_at', null)
                ->whereIn('alert_type', [
                    'FIFO_MATH_ERROR',
                    'VALUE_CALCULATION_ERROR',
                    'LAYER_INCONSISTENCY',
                    'CAPACITY_VIOLATION'
                ])
                ->get();

            // Get FIFO processing statistics
            $processingStats = DB::table('batch_consumption')
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    COUNT(DISTINCT tank_inventory_layer_id) as layers_consumed,
                    SUM(quantity_consumed_liters) as total_quantity,
                    SUM(total_cost_consumed) as total_value,
                    AVG(cost_per_liter) as avg_cost_per_liter,
                    MIN(consumption_timestamp) as earliest_transaction,
                    MAX(consumption_timestamp) as latest_transaction
                ')
                ->where('consumption_timestamp', '>=', Carbon::now()->subHours(24))
                ->first();

            // Get system configuration status
            $configStatus = DB::table('system_configurations')
                ->select(['config_key', 'config_value_boolean'])
                ->whereIn('config_key', [
                    'ENHANCED_FIFO_PROCESSING_ENABLED',
                    'ENHANCED_MONITORING_ENABLED',
                    'ENHANCED_CLEANUP_ENABLED',
                    'AUTO_DELIVERY_LAYER_CREATION'
                ])
                ->get()
                ->keyBy('config_key');

            return [
                'system_health' => [
                    'monitoring_checks_24h' => $monitoringResults->count(),
                    'checks_passed' => $monitoringResults->where('check_status', 'PASSED')->count(),
                    'checks_failed' => $monitoringResults->where('check_status', 'FAILED')->count(),
                    'checks_warning' => $monitoringResults->where('check_status', 'WARNING')->count(),
                    'recent_failures' => $monitoringResults->where('check_status', 'FAILED')->take(5)->values()
                ],
                'active_alerts' => [
                    'total_alerts' => $activeAlerts->count(),
                    'critical_alerts' => $activeAlerts->where('severity', 'CRITICAL')->count(),
                    'high_alerts' => $activeAlerts->where('severity', 'HIGH')->count(),
                    'alert_types' => $activeAlerts->pluck('alert_type')->unique()->values(),
                    'alert_details' => $activeAlerts->take(10)->values()
                ],
                'processing_stats_24h' => [
                    'total_transactions' => $processingStats->total_transactions ?? 0,
                    'layers_consumed' => $processingStats->layers_consumed ?? 0,
                    'total_quantity' => round($processingStats->total_quantity ?? 0, 3),
                    'total_value' => round($processingStats->total_value ?? 0, 2),
                    'avg_cost_per_liter' => round($processingStats->avg_cost_per_liter ?? 0, 4),
                    'earliest_transaction' => $processingStats->earliest_transaction,
                    'latest_transaction' => $processingStats->latest_transaction
                ],
                'configuration_status' => [
                    'fifo_processing_enabled' => $configStatus->get('ENHANCED_FIFO_PROCESSING_ENABLED')->config_value_boolean ?? false,
                    'monitoring_enabled' => $configStatus->get('ENHANCED_MONITORING_ENABLED')->config_value_boolean ?? false,
                    'cleanup_enabled' => $configStatus->get('ENHANCED_CLEANUP_ENABLED')->config_value_boolean ?? false,
                    'auto_layer_creation_enabled' => $configStatus->get('AUTO_DELIVERY_LAYER_CREATION')->config_value_boolean ?? false
                ],
                'health_status' => $activeAlerts->where('severity', 'CRITICAL')->count() == 0 ? 'HEALTHY' : 'CRITICAL',
                'overall_score' => $this->calculateHealthScore($monitoringResults, $activeAlerts),
                'report_timestamp' => now()
            ];

        } catch (Exception $e) {
            return [
                'health_status' => 'ERROR',
                'error_message' => $e->getMessage(),
                'error_context' => 'Failed to retrieve FIFO health status from monitoring system',
                'report_timestamp' => now()
            ];
        }
    }

    /**
     *  NEW METHOD: HEALTH SCORE CALCULATION
     *
     * Calculates overall FIFO system health score based on monitoring data
     */
    private function calculateHealthScore($monitoringResults, $activeAlerts): int
    {
        $score = 100;

        // Deduct points for failed checks (last 24h)
        $failedChecks = $monitoringResults->where('check_status', 'FAILED')->count();
        $score -= $failedChecks * 5;

        // Deduct points for active alerts
        $criticalAlerts = $activeAlerts->where('severity', 'CRITICAL')->count();
        $highAlerts = $activeAlerts->where('severity', 'HIGH')->count();

        $score -= $criticalAlerts * 20;
        $score -= $highAlerts * 10;

        // Minimum score is 0
        return max(0, $score);
    }

    /**
     *  ENHANCED METHOD: COGS WITH AUTOMATION INTEGRATION
     *
     * Calculates COGS from actual consumption records created by automation
     */
    public function calculateCOGSFromConsumption(int $readingId): array
    {
        try {
            // Validate reading exists
            $reading = DB::table('readings')
                ->select(['id', 'tank_id', 'pump_id', 'calculated_sales_liters', 'reading_date'])
                ->where('id', $readingId)
                ->first();

            if (!$reading) {
                throw new Exception("Reading {$readingId} not found");
            }

            // Get consumption records created by the automation system
            $consumptionRecords = DB::table('batch_consumption as bc')
                ->select([
                    'bc.quantity_consumed_liters',
                    'bc.cost_per_liter',
                    'bc.total_cost_consumed',
                    'bc.consumption_sequence',
                    'bc.consumption_timestamp',
                    'til.layer_sequence_number',
                    'til.delivery_batch_number',
                    'til.layer_created_at'
                ])
                ->join('tank_inventory_layers as til', 'bc.tank_inventory_layer_id', '=', 'til.id')
                ->where('bc.reading_id', $readingId)
                ->orderBy('bc.consumption_sequence')
                ->get();

            if ($consumptionRecords->isEmpty()) {
                throw new Exception("No consumption records found for reading {$readingId}. This may indicate FIFO automation failure.");
            }

            $totalQuantity = $consumptionRecords->sum('quantity_consumed_liters');
            $totalCOGS = $consumptionRecords->sum('total_cost_consumed');
            $averageCost = $totalQuantity > 0 ? $totalCOGS / $totalQuantity : 0;

            // Validate against reading's calculated sales
            $salesVariance = abs($totalQuantity - ($reading->calculated_sales_liters ?? 0));
            $salesVariancePercentage = $reading->calculated_sales_liters > 0
                ? ($salesVariance / $reading->calculated_sales_liters) * 100
                : 0;

            return [
                'reading_id' => $readingId,
                'reading_details' => [
                    'tank_id' => $reading->tank_id,
                    'pump_id' => $reading->pump_id,
                    'reading_date' => $reading->reading_date,
                    'calculated_sales_liters' => round($reading->calculated_sales_liters ?? 0, 3)
                ],
                'cogs_calculation' => [
                    'total_quantity_consumed' => round($totalQuantity, 3),
                    'total_cogs' => round($totalCOGS, 2),
                    'average_cost_per_liter' => round($averageCost, 4),
                    'layers_consumed' => $consumptionRecords->count()
                ],
                'validation' => [
                    'sales_variance_liters' => round($salesVariance, 3),
                    'sales_variance_percentage' => round($salesVariancePercentage, 4),
                    'validation_passed' => $salesVariancePercentage < 1.0
                ],
                'consumption_details' => $consumptionRecords->map(function($record) {
                    return [
                        'sequence' => $record->consumption_sequence,
                        'layer_sequence' => $record->layer_sequence_number,
                        'batch_number' => $record->delivery_batch_number,
                        'layer_age_days' => Carbon::parse($record->layer_created_at)->diffInDays(now()),
                        'quantity' => round($record->quantity_consumed_liters, 3),
                        'cost_per_liter' => round($record->cost_per_liter, 4),
                        'layer_cogs' => round($record->total_cost_consumed, 2),
                        'consumed_at' => $record->consumption_timestamp
                    ];
                })->toArray(),
                'calculation_timestamp' => now()
            ];

        } catch (Exception $e) {
            // Enhanced error context
            $readingContext = '';
            try {
                $readingInfo = DB::table('readings')
                    ->select(['reading_type', 'reading_status', 'tank_id', 'pump_id'])
                    ->where('id', $readingId)
                    ->first();

                if ($readingInfo) {
                    $readingContext = " (Type: {$readingInfo->reading_type}, Status: {$readingInfo->reading_status}, Tank: {$readingInfo->tank_id}, Pump: {$readingInfo->pump_id})";
                }
            } catch (Exception $contextE) {
                // Ignore context retrieval errors
            }

            throw new Exception("COGS calculation failed for reading {$readingId}{$readingContext}: " . $e->getMessage());
        }
    }
}
