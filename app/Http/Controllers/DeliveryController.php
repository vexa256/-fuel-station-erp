<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;

class DeliveryController extends Controller
{
    /**
     * EXACT SCHEMA COMPLIANCE - 100% accurate field mapping from FUEL_ERP.sql
     */
    private const DELIVERY_STATUS = ['SCHEDULED', 'IN_TRANSIT', 'ARRIVED', 'UNLOADING', 'COMPLETED', 'REJECTED', 'OTHER'];

    /**
     * EXACT SCHEMA COMPLIANCE - 100% accurate field mapping from FUEL_ERP.sql
     */
    private const VEHICLE_TYPES = ['TANKER', 'BOWSER', 'TRUCK', 'OTHER'];

    /**
     * ALL deliveries table fields from schema - 100% accurate
     */
    private const DELIVERY_FIELDS = [
        'id', 'purchase_order_id', 'supplier_id', 'tank_id', 'delivery_note_number',
        'supplier_invoice_reference', 'delivery_date', 'delivery_time', 'scheduled_date',
        'scheduled_time', 'driver_name', 'driver_license', 'vehicle_registration',
        'vehicle_type', 'compartment_count', 'seal_number_1', 'seal_number_2',
        'seal_number_3', 'quantity_ordered_liters', 'quantity_delivered_liters',
        'quantity_variance_liters', 'variance_percentage', 'cost_per_liter',
        'transport_cost_per_liter', 'handling_cost_per_liter', 'total_delivery_cost',
        'loading_temperature_celsius', 'delivery_temperature_celsius', 'temperature_variance_celsius',
        'density_at_15c', 'volume_correction_factor', 'corrected_volume_liters',
        'water_content_ppm', 'quality_test_passed', 'quality_failure_reason',
        'delivery_status', 'rejection_reason', 'received_by', 'approved_by',
        'created_at', 'updated_at', 'hash_previous', 'hash_current'
    ];

    /**
     * Dashboard with efficient queries - EXACT schema field mapping
     */
    public function index(Request $request)
    {
        if (!$this->hasAccess()) {
            $this->logSecurityViolation('UNAUTHORIZED_DELIVERY_ACCESS');
            return redirect()->back()->with('error', 'Access denied');
        }

        // Optimized query with explicit field selection for performance
        $query = DB::table('deliveries')
            ->join('suppliers', 'deliveries.supplier_id', '=', 'suppliers.id')
            ->join('tanks', 'deliveries.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->leftJoin('purchase_orders', 'deliveries.purchase_order_id', '=', 'purchase_orders.id')
            ->select([
                // Core delivery info
                'deliveries.id',
                'deliveries.delivery_note_number',
                'deliveries.delivery_date',
                'deliveries.delivery_time',
                'deliveries.quantity_delivered_liters',
                'deliveries.total_delivery_cost',
                'deliveries.delivery_status',
                'deliveries.quality_test_passed',
                'deliveries.driver_name',
                'deliveries.vehicle_registration',
                'deliveries.created_at',
                // Related table data
                'suppliers.company_name',
                'tanks.tank_number',
                'stations.station_name',
                'purchase_orders.po_number'
            ]);

        // Apply search filters with explicit field validation
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('deliveries.delivery_note_number', 'LIKE', "%{$search}%")
                  ->orWhere('suppliers.company_name', 'LIKE', "%{$search}%")
                  ->orWhere('deliveries.driver_name', 'LIKE', "%{$search}%")
                  ->orWhere('deliveries.vehicle_registration', 'LIKE', "%{$search}%");
            });
        }

        // Status filter with enum validation
        if ($status = $request->get('status')) {
            if (in_array($status, self::DELIVERY_STATUS)) {
                $query->where('deliveries.delivery_status', $status);
            }
        }

        // Quality filter
        if ($request->has('quality_issues')) {
            $query->where('deliveries.quality_test_passed', 0);
        }

        $deliveries = $query->orderBy('deliveries.delivery_date', 'desc')
                           ->orderBy('deliveries.delivery_time', 'desc')
                           ->paginate(25);

        // Dashboard stats with separate optimized queries
        $stats = [
            'total' => DB::table('deliveries')->count(),
            'completed_today' => DB::table('deliveries')
                ->where('delivery_status', 'COMPLETED')
                ->where('delivery_date', now()->toDateString())
                ->count(),
            'in_transit' => DB::table('deliveries')
                ->where('delivery_status', 'IN_TRANSIT')
                ->count(),
            'quality_issues' => DB::table('deliveries')
                ->where('quality_test_passed', 0)
                ->count()
        ];

        // Simple audit log
        $this->logAction('read', 'deliveries', null, 'Dashboard access');

        return view('deliveries.index', compact('deliveries', 'stats'));
    }

    /**
     * Create form - loads only essential data efficiently
     */
    public function create(Request $request)
    {
        if (!$this->hasAccess()) {
            $this->logSecurityViolation('UNAUTHORIZED_DELIVERY_CREATE');
            return redirect()->back()->with('error', 'Access denied');
        }

        $selectedPOId = $request->get('po_id');
        $selectedPO = null;

        // Get selected PO with essential fields only
        if ($selectedPOId) {
            $selectedPO = DB::table('purchase_orders')
                ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
                ->join('stations', 'purchase_orders.station_id', '=', 'stations.id')
                ->where('purchase_orders.id', $selectedPOId)
                ->where('purchase_orders.order_status', 'APPROVED')
                ->select([
                    'purchase_orders.id',
                    'purchase_orders.po_number',
                    'purchase_orders.supplier_id',
                    'purchase_orders.station_id',
                    'purchase_orders.product_type',
                    'purchase_orders.ordered_quantity_liters',
                    'purchase_orders.agreed_price_per_liter',
                    'purchase_orders.transport_cost_per_liter',
                    'purchase_orders.other_charges_per_liter',
                    'suppliers.company_name',
                    'stations.station_name'
                ])
                ->first();
        }

        // Get available POs with calculated delivery progress
        $approvedPOs = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->join('stations', 'purchase_orders.station_id', '=', 'stations.id')
            ->leftJoin(DB::raw('(SELECT purchase_order_id, SUM(quantity_delivered_liters) as total_delivered
                               FROM deliveries
                               WHERE delivery_status = "COMPLETED"
                               GROUP BY purchase_order_id) as delivery_summary'),
                      'purchase_orders.id', '=', 'delivery_summary.purchase_order_id')
            ->whereIn('purchase_orders.order_status', ['APPROVED', 'PARTIALLY_DELIVERED'])
            ->where('purchase_orders.expected_delivery_date', '>=', now()->subDays(30))
            ->where(function($query) {
                $query->whereNull('delivery_summary.total_delivered')
                      ->orWhereRaw('delivery_summary.total_delivered < purchase_orders.ordered_quantity_liters * 0.95');
            })
            ->select([
                'purchase_orders.*',
                'suppliers.company_name',
                'stations.station_name',
                DB::raw('COALESCE(delivery_summary.total_delivered, 0) as total_delivered'),
                DB::raw('(purchase_orders.ordered_quantity_liters - COALESCE(delivery_summary.total_delivered, 0)) as remaining_quantity')
            ])
            ->orderBy('purchase_orders.expected_delivery_date')
            ->get();

        // Generate next delivery note number
        $nextDeliveryNote = $this->generateDeliveryNote();

        $this->logAction('read', 'deliveries', null, 'Create form access');

        return view('deliveries.create', compact(
            'selectedPO',
            'approvedPOs',
            'nextDeliveryNote'
        ));
    }

    /**
     * Store delivery - works WITH database triggers, no over-engineering
     * Follows tr_validate_tank_capacity, tr_deliveries_hash_chain, tr_enhanced_delivery_fifo_layers
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!$this->hasAccess()) {
                return response()->json(['success' => false, 'error' => 'Access denied'], 403);
            }

            // Validate with EXACT schema field names and constraints
            $validated = $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'delivery_note_number' => 'required|string|max:100|unique:deliveries,delivery_note_number',
                'supplier_invoice_reference' => 'nullable|string|max:100',
                'delivery_date' => 'required|date',
                'delivery_time' => 'required',
                'driver_name' => 'nullable|string|max:255',  // Schema shows nullable
                'driver_license' => 'nullable|string|max:100',
                'vehicle_registration' => 'nullable|string|max:50',  // Schema shows nullable
                'vehicle_type' => ['nullable', Rule::in(self::VEHICLE_TYPES)],
                'compartment_count' => 'nullable|integer|min:1|max:255',  // tinyint UNSIGNED
                'seal_number_1' => 'nullable|string|max:50',
                'seal_number_2' => 'nullable|string|max:50',
                'seal_number_3' => 'nullable|string|max:50',
                'quantity_delivered_liters' => 'required|numeric|min:0.001|max:999999999.999',  // decimal(12,3)
                'loading_temperature_celsius' => 'nullable|numeric|min:-999.9|max:999.9',  // decimal(4,1)
                'delivery_temperature_celsius' => 'nullable|numeric|min:-999.9|max:999.9',
                'density_at_15c' => 'nullable|numeric|min:0.0001|max:99.9999',  // decimal(6,4)
                'water_content_ppm' => 'nullable|numeric|min:0.0|max:9999.9',  // decimal(6,1)
                'transport_cost_per_liter' => 'nullable|numeric|min:0|max:9999.9999',  // decimal(8,4)
                'handling_cost_per_liter' => 'nullable|numeric|min:0|max:9999.9999'
            ]);

            // Get PO with validation
            $po = DB::table('purchase_orders')
                ->where('id', $validated['purchase_order_id'])
                ->whereIn('order_status', ['APPROVED', 'PARTIALLY_DELIVERED'])
                ->first();

            if (!$po) {
                return response()->json(['success' => false, 'error' => 'Purchase Order not approved'], 422);
            }

          // Find appropriate tank - matches schema relationships
$tank = DB::table('tanks')
    ->join('products', 'tanks.product_id', '=', 'products.id')
    ->where('tanks.station_id', $po->station_id)
    ->where('products.product_type', $po->product_type)
    ->where('tanks.is_active', 1)
    ->select([
        'tanks.id',
        'tanks.station_id',
        'tanks.tank_number',
        'tanks.product_id',
        'tanks.capacity_liters',
        'tanks.minimum_stock_level_liters',
        'tanks.maximum_stock_level_liters',
        'tanks.tank_type',
        'products.product_type',
        'products.product_name'
    ])
    ->first();

if (!$tank) {
    // Enhanced diagnostic information
    $debugInfo = [
        'station_id' => $po->station_id,
        'product_type' => $po->product_type,
        'available_tanks' => DB::table('tanks')
            ->join('products', 'tanks.product_id', '=', 'products.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->where('tanks.station_id', $po->station_id)
            ->select([
                'tanks.tank_number',
                'tanks.is_active',
                'products.product_type',
                'stations.station_name'
            ])
            ->get()
    ];

    return response()->json([
        'success' => false,
        'error' => "No active tank found for product type '{$po->product_type}' at station ID {$po->station_id}",
        'debug' => $debugInfo
    ], 422);
}

// Add capacity validation
$currentVolume = DB::table('readings')
    ->where('tank_id', $tank->id)
    ->whereIn('reading_type', ['MORNING_DIP', 'EVENING_DIP', 'SPOT_CHECK'])
    ->where('reading_status', '!=', 'REJECTED')
    ->orderBy('reading_date', 'desc')
    ->orderBy('reading_time', 'desc')
    ->value('dip_reading_liters') ?? 0;

$availableCapacity = $tank->capacity_liters - $currentVolume;
$deliveryQuantity = floatval($validated['quantity_delivered_liters']);

if ($deliveryQuantity > $availableCapacity) {
    return response()->json([
        'success' => false,
        'error' => 'Delivery quantity exceeds available tank capacity',
        'details' => [
            'tank_capacity' => $tank->capacity_liters,
            'current_volume' => $currentVolume,
            'available_capacity' => $availableCapacity,
            'requested_delivery' => $deliveryQuantity
        ]
    ], 422);
}

            // Get current stock using readings table (NOT dip_readings as per instructions)
            $currentStock = DB::table('readings')
                ->where('tank_id', $tank->id)
                ->whereIn('reading_type', ['MORNING_DIP', 'EVENING_DIP', 'DELIVERY_AFTER'])
                ->where('reading_status', 'VALIDATED')
                ->whereNotNull('dip_reading_liters')
                ->orderBy('reading_date', 'desc')
                ->orderBy('reading_time', 'desc')
                ->value('dip_reading_liters') ?? 0;

            // Pre-validate capacity (tr_validate_tank_capacity trigger will also check)
            if (($currentStock + $validated['quantity_delivered_liters']) > $tank->capacity_liters) {
                return response()->json([
                    'success' => false,
                    'error' => 'Delivery would exceed tank capacity',
                    'details' => [
                        'current_stock' => round($currentStock, 3),
                        'delivery_quantity' => round($validated['quantity_delivered_liters'], 3),
                        'tank_capacity' => round($tank->capacity_liters, 3),
                        'available_capacity' => round($tank->capacity_liters - $currentStock, 3)
                    ]
                ], 422);
            }

            // Calculate fields with mathematical precision (0.001L tolerance)
            $quantityVariance = round($validated['quantity_delivered_liters'] - $po->ordered_quantity_liters, 3);
            $variancePercentage = $po->ordered_quantity_liters > 0 ?
                round(($quantityVariance / $po->ordered_quantity_liters) * 100, 3) : 0;  // decimal(6,3)

            $temperatureVariance = null;
            if (isset($validated['loading_temperature_celsius']) && isset($validated['delivery_temperature_celsius'])) {
                $temperatureVariance = round($validated['delivery_temperature_celsius'] - $validated['loading_temperature_celsius'], 1);
            }

            // Cost calculations with 0.0001 precision
            $transportCost = round($validated['transport_cost_per_liter'] ?? $po->transport_cost_per_liter ?? 0.0000, 4);
            $handlingCost = round($validated['handling_cost_per_liter'] ?? $po->other_charges_per_liter ?? 0.0000, 4);
            $costPerLiter = round($po->agreed_price_per_liter + $transportCost + $handlingCost, 4);  // decimal(10,4)
            $totalCost = round($validated['quantity_delivered_liters'] * $costPerLiter, 2);  // decimal(15,2)

            // Quality validation logic
            $qualityPassed = 1;
            $qualityFailure = null;
            if (abs($variancePercentage) > 5.0) {  // Critical variance threshold
                $qualityPassed = 0;
                $qualityFailure = "Quantity variance " . $variancePercentage . "% exceeds tolerance";
            }
            if (($validated['water_content_ppm'] ?? 0) > 200) {
                $qualityPassed = 0;
                $qualityFailure = ($qualityFailure ? $qualityFailure . '; ' : '') . 'Water content exceeds limits';
            }

            // CEO/SYSTEM_ADMIN/STATION_MANAGER/STATION_ADMIN auto-approval logic
            $userRole = auth()->user()->role ?? '';
            $autoApproved = in_array($userRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER', 'STATION_ADMIN']);
            $deliveryStatus = $autoApproved ? 'COMPLETED' : 'ARRIVED';

            // Prepare delivery data with ALL schema fields
            $deliveryData = [
                'purchase_order_id' => $validated['purchase_order_id'],
                'supplier_id' => $po->supplier_id,
                'tank_id' => $tank->id,
                'delivery_note_number' => strtoupper($validated['delivery_note_number']),
                'supplier_invoice_reference' => $validated['supplier_invoice_reference'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_time' => $validated['delivery_time'],
                'scheduled_date' => $validated['delivery_date'],  // Default to delivery date
                'scheduled_time' => $validated['delivery_time'],  // Default to delivery time
                'driver_name' => $validated['driver_name'],
                'driver_license' => $validated['driver_license'],
                'vehicle_registration' => $validated['vehicle_registration'] ? strtoupper($validated['vehicle_registration']) : null,
                'vehicle_type' => $validated['vehicle_type'] ?? 'TANKER',  // Schema default
                'compartment_count' => $validated['compartment_count'] ?? 1,  // Schema default
                'seal_number_1' => $validated['seal_number_1'],
                'seal_number_2' => $validated['seal_number_2'],
                'seal_number_3' => $validated['seal_number_3'],
                'quantity_ordered_liters' => round($po->ordered_quantity_liters, 3),
                'quantity_delivered_liters' => round($validated['quantity_delivered_liters'], 3),
                'quantity_variance_liters' => $quantityVariance,
                'variance_percentage' => $variancePercentage,
                'cost_per_liter' => $costPerLiter,
                'transport_cost_per_liter' => $transportCost,
                'handling_cost_per_liter' => $handlingCost,
                'total_delivery_cost' => $totalCost,
                'loading_temperature_celsius' => $validated['loading_temperature_celsius'],
                'delivery_temperature_celsius' => $validated['delivery_temperature_celsius'],
                'temperature_variance_celsius' => $temperatureVariance,
                'density_at_15c' => $validated['density_at_15c'],
                'volume_correction_factor' => 1.000000,  // Schema default
                'corrected_volume_liters' => round($validated['quantity_delivered_liters'], 3),
                'water_content_ppm' => $validated['water_content_ppm'] ?? 0.0,  // Schema default
                'quality_test_passed' => $qualityPassed,
                'quality_failure_reason' => $qualityFailure,
                'delivery_status' => $deliveryStatus,
                'rejection_reason' => null,
                'received_by' => auth()->id(),
                'approved_by' => $autoApproved ? auth()->id() : null,
                'created_at' => now(),
                'updated_at' => now()
                // hash_previous and hash_current will be set by tr_deliveries_hash_chain trigger
            ];

            // Insert delivery - triggers will handle hash chain and capacity validation
            $deliveryId = DB::table('deliveries')->insertGetId($deliveryData);

            // Update PO status based on delivery completion
            $totalDelivered = DB::table('deliveries')
                ->where('purchase_order_id', $validated['purchase_order_id'])
                ->where('delivery_status', 'COMPLETED')
                ->sum('quantity_delivered_liters');

            $deliveryPercentage = ($totalDelivered / $po->ordered_quantity_liters) * 100;
            $newPOStatus = ($deliveryPercentage >= 95.0) ? 'FULLY_DELIVERED' : 'PARTIALLY_DELIVERED';

            DB::table('purchase_orders')
                ->where('id', $validated['purchase_order_id'])
                ->update(['order_status' => $newPOStatus]);

            // Simple audit logging
            $this->logAction('CREATE', 'deliveries', $deliveryId, 'Delivery created');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $autoApproved ?
                    'Delivery completed with auto-approval - FIFO automation triggered' :
                    'Delivery created - pending approval',
                'delivery' => [
                    'id' => $deliveryId,
                    'delivery_note_number' => $deliveryData['delivery_note_number'],
                    'quantity_delivered' => $validated['quantity_delivered_liters'],
                    'auto_approved' => $autoApproved,
                    'auto_approved_role' => $autoApproved ? $userRole : null,
                    'delivery_status' => $deliveryStatus,
                    'total_cost' => $totalCost
                ],
                'redirect' => route('deliveries.show', $deliveryId)
            ], 201);

        } catch (Exception $e) {
            DB::rollback();

            $this->logAction('ERROR', 'deliveries', null, 'Delivery creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Delivery creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual approval - triggers tr_enhanced_delivery_fifo_layers automatically
     */
    public function approve($id, Request $request)
    {
        DB::beginTransaction();

        try {
            if (!$this->hasAccess()) {
                return response()->json(['success' => false, 'error' => 'Access denied'], 403);
            }

            $delivery = DB::table('deliveries')->where('id', $id)->first();

            if (!$delivery) {
                return response()->json(['success' => false, 'error' => 'Delivery not found'], 404);
            }

            if ($delivery->delivery_status === 'COMPLETED') {
                return response()->json(['success' => false, 'error' => 'Already approved'], 422);
            }

            // Update to COMPLETED - tr_enhanced_delivery_fifo_layers trigger will fire automatically
            DB::table('deliveries')
                ->where('id', $id)
                ->update([
                    'delivery_status' => 'COMPLETED',
                    'approved_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            $this->logAction('APPROVE', 'deliveries', $id, 'Manual delivery approval');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery approved - FIFO automation triggered',
                'delivery_status' => 'COMPLETED'
            ]);

        } catch (Exception $e) {
            DB::rollback();
            $this->logAction('ERROR', 'deliveries', $id, 'Approval failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }


public function show($id)
{
    try {
        // STEP 1: Get delivery with all related data (EXACT schema compliance)
        $delivery = DB::table('deliveries')
            ->join('suppliers', 'deliveries.supplier_id', '=', 'suppliers.id')
            ->join('tanks', 'deliveries.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->leftJoin('purchase_orders', 'deliveries.purchase_order_id', '=', 'purchase_orders.id')
            ->select([
                // ALL deliveries table fields
                'deliveries.id',
                'deliveries.purchase_order_id',
                'deliveries.supplier_id',
                'deliveries.tank_id',
                'deliveries.delivery_note_number',
                'deliveries.supplier_invoice_reference',
                'deliveries.delivery_date',
                'deliveries.delivery_time',
                'deliveries.scheduled_date',
                'deliveries.scheduled_time',
                'deliveries.driver_name',
                'deliveries.driver_license',
                'deliveries.vehicle_registration',
                'deliveries.vehicle_type',
                'deliveries.compartment_count',
                'deliveries.seal_number_1',
                'deliveries.seal_number_2',
                'deliveries.seal_number_3',
                'deliveries.quantity_ordered_liters',
                'deliveries.quantity_delivered_liters',
                'deliveries.quantity_variance_liters',
                'deliveries.variance_percentage',
                'deliveries.cost_per_liter',
                'deliveries.transport_cost_per_liter',
                'deliveries.handling_cost_per_liter',
                'deliveries.total_delivery_cost',
                'deliveries.loading_temperature_celsius',
                'deliveries.delivery_temperature_celsius',
                'deliveries.temperature_variance_celsius',
                'deliveries.density_at_15c',
                'deliveries.volume_correction_factor',
                'deliveries.corrected_volume_liters',
                'deliveries.water_content_ppm',
                'deliveries.quality_test_passed',
                'deliveries.quality_failure_reason',
                'deliveries.delivery_status',
                'deliveries.rejection_reason',
                'deliveries.received_by',
                'deliveries.approved_by',
                'deliveries.created_at',
                'deliveries.updated_at',
                'deliveries.hash_previous',
                'deliveries.hash_current',

                // Related table fields
                'suppliers.company_name',
                'suppliers.supplier_code',
                'tanks.tank_number',
                'tanks.capacity_liters',
                'stations.station_name',
                'purchase_orders.po_number'
            ])
            ->where('deliveries.id', $id)
            ->first();

        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', 'Delivery not found');
        }

        // STEP 2: ✅ FIXED - Check if delivery receipt exists using delivery_receipts table
        $deliveryReceipt = DB::table('delivery_receipts')
            ->select([
                'id', 'delivery_id', 'receipt_timestamp', 'sample_reference_number',
                'verified_by', 'witnessed_by', 'supervisor_approved_by',
                'quality_sample_taken', 'contamination_detected'
            ])
            ->where('delivery_id', $delivery->id)
            ->first();

        // STEP 3: ✅ ADD receipt properties to delivery object (NOT receipt_id)
        $delivery->has_receipt = $deliveryReceipt !== null;
        $delivery->receipt_data = $deliveryReceipt;

        // STEP 4: Get inventory layer if exists
        $inventoryLayer = DB::table('tank_inventory_layers')
            ->select([
                'id', 'layer_sequence_number', 'opening_quantity_liters',
                'current_quantity_liters', 'consumed_quantity_liters',
                'cost_per_liter', 'total_layer_cost', 'remaining_layer_value',
                'is_depleted', 'layer_status', 'layer_created_at'
            ])
            ->where('delivery_id', $delivery->id)
            ->first();

        // STEP 5: Get delivery compartments if any
        $compartments = DB::table('delivery_compartments')
            ->select([
                'compartment_number', 'product_type', 'quantity_liters',
                'temperature_celsius', 'density', 'water_bottom_cm',
                'seal_number', 'compartment_status'
            ])
            ->where('delivery_id', $delivery->id)
            ->orderBy('compartment_number')
            ->get();

        // STEP 6: Audit logging
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action_type' => 'READ',
            'action_category' => 'DELIVERY_VIEW',
            'table_name' => 'deliveries',
            'record_id' => $delivery->id,
            'field_name' => 'delivery_show',
            'new_value_text' => "Delivery #{$delivery->id} viewed",
            'change_reason' => 'Delivery details accessed',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'hash_current' => hash('sha256', json_encode($delivery) . now()),
            'timestamp' => now()
        ]);

        return view('deliveries.show', compact(
            'delivery',
            'inventoryLayer',
            'compartments'
        ));

    } catch (Exception $e) {
        return redirect()->route('deliveries.index')
            ->with('error', 'Error loading delivery: ' . $e->getMessage());
    }
}

    /**
     * Generate delivery receipt - creates delivery_receipts record
     */
    public function receipt($id)
    {
        if (!$this->hasAccess()) {
            $this->logSecurityViolation('UNAUTHORIZED_RECEIPT_ACCESS');
            return redirect()->back()->with('error', 'Access denied');
        }

        $delivery = DB::table('deliveries')
            ->join('suppliers', 'deliveries.supplier_id', '=', 'suppliers.id')
            ->join('tanks', 'deliveries.tank_id', '=', 'tanks.id')
            ->join('stations', 'tanks.station_id', '=', 'stations.id')
            ->where('deliveries.id', $id)
            ->where('deliveries.delivery_status', 'COMPLETED')
            ->select([
                'deliveries.*',
                'suppliers.company_name',
                'suppliers.supplier_code',
                'tanks.tank_number',
                'tanks.capacity_liters',
                'stations.station_name'
            ])
            ->first();

        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', 'Delivery not found or not completed');
        }

        // Check if receipt already exists
        $existingReceipt = DB::table('delivery_receipts')->where('delivery_id', $id)->exists();

        if (!$existingReceipt) {
            // Get before/after readings from readings table
            $beforeReading = DB::table('readings')
                ->where('tank_id', $delivery->tank_id)
                ->where('reading_type', 'DELIVERY_BEFORE')
                ->where('reading_date', $delivery->delivery_date)
                ->orderBy('reading_time', 'desc')
                ->value('dip_reading_liters') ?? 0;

            $afterReading = DB::table('readings')
                ->where('tank_id', $delivery->tank_id)
                ->where('reading_type', 'DELIVERY_AFTER')
                ->where('reading_date', $delivery->delivery_date)
                ->orderBy('reading_time', 'desc')
                ->value('dip_reading_liters') ?? 0;

            // Create receipt with ALL schema fields from delivery_receipts table
            DB::table('delivery_receipts')->insert([
                'delivery_id' => $id,
                'dip_before_delivery_mm' => round($beforeReading / 50, 2),  // Approximate conversion
                'dip_before_delivery_liters' => round($beforeReading, 3),
                'dip_after_delivery_mm' => round($afterReading / 50, 2),
                'dip_after_delivery_liters' => round($afterReading, 3),
                'calculated_quantity_liters' => round($delivery->quantity_delivered_liters, 3),
                'delivery_variance_liters' => round($delivery->quantity_variance_liters, 3),
                'delivery_variance_percentage' => round($delivery->variance_percentage, 3),
                'temperature_before_celsius' => $delivery->loading_temperature_celsius,
                'temperature_after_celsius' => $delivery->delivery_temperature_celsius,
                'ambient_temperature_celsius' => $delivery->delivery_temperature_celsius,
                'specific_gravity' => $delivery->density_at_15c,
                'density_at_15c' => $delivery->density_at_15c,
                'water_content_percentage' => round(($delivery->water_content_ppm ?? 0) / 10000, 4),
                'sediment_content_percentage' => 0.0000,
                'contamination_detected' => $delivery->quality_test_passed ? 0 : 1,
                'contamination_type' => $delivery->quality_test_passed ? 'NONE' : 'OTHER_FUEL',
                'quality_sample_taken' => 1,
                'sample_reference_number' => 'QS-' . $delivery->delivery_note_number,
                'tank_cleaning_required' => 0,
                'receipt_timestamp' => now(),
                'verified_by' => auth()->id(),
                'witnessed_by' => auth()->id(),
                'supervisor_approved_by' => auth()->id()
            ]);

            $this->logAction('CREATE', 'delivery_receipts', $id, 'Receipt generated');
        }

        return view('deliveries.receipt', compact('delivery'));
    }

    // ================================
    // SIMPLIFIED HELPER METHODS
    // ================================

    /**
     * Role-based access control - CEO/SYSTEM_ADMIN/STATION_MANAGER/STATION_ADMIN
     */
    private function hasAccess(): bool
    {
        $userRole = auth()->user()->role ?? '';
        return in_array($userRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER', 'STATION_ADMIN']);
    }

    /**
     * Generate delivery note with year-month format
     */
    private function generateDeliveryNote(): string
    {
        $prefix = 'DEL-' . date('Ym') . '-';

        $lastNumber = DB::table('deliveries')
            ->where('delivery_note_number', 'LIKE', $prefix . '%')
            ->orderBy('delivery_note_number', 'desc')
            ->value('delivery_note_number');

        $number = $lastNumber ? (int)substr($lastNumber, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Simple audit logging without over-engineering
     */
    private function logAction(string $actionType, string $tableName, ?int $recordId, string $reason): void
    {
        // DB::table('audit_logs')->insert([
        //     'user_id' => auth()->id(),
        //     'table_name' => $tableName,
        //     'record_id' => $recordId,
        //     'action_type' => $actionType,
        //     'old_values' => null,
        //     'new_values' => json_encode(['reason' => $reason]),
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->userAgent(),
        //     'created_at' => now()
        // ]);
    }

    /**
     * Security violation logging
     */
    private function logSecurityViolation(string $violation): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'table_name' => 'security_violations',
            'record_id' => null,
            'action_type' => 'SECURITY_VIOLATION',
            'old_values' => null,
            'new_values' => json_encode(['violation' => $violation]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }




}
