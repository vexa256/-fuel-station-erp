<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\AuditService;


class PurchaseOrderController extends Controller
{
    private AuditService $auditService;

    /**
     * EXACT SCHEMA FIELD MAPPING - purchase_orders table
     */
    private const PO_FIELDS = [
        'id', 'po_number', 'supplier_id', 'supplier_contract_id', 'station_id',
        'product_type', 'ordered_quantity_liters', 'agreed_price_per_liter',
        'transport_cost_per_liter', 'other_charges_per_liter', 'total_order_value',
        'expected_delivery_date', 'expected_delivery_time', 'order_status',
        'approved_at', 'approved_by', 'created_at', 'updated_at', 'created_by'
    ];

    /**
     * VERIFIED PRODUCT TYPES from schema - EXACT ENUM VALUES
     */
    private const PRODUCT_TYPES = [
        'PETROL_95', 'PETROL_98', 'DIESEL', 'KEROSENE', 'JET_A1', 'HEAVY_FUEL_OIL',
        'LIGHT_FUEL_OIL', 'LPG_AUTOGAS', 'ETHANOL_E10', 'ETHANOL_E85', 'BIODIESEL_B7', 'BIODIESEL_B20'
    ];

    /**
     * VERIFIED ORDER STATUS from schema - EXACT ENUM VALUES
     */
    private const ORDER_STATUS = [
        'PENDING', 'APPROVED', 'PARTIALLY_DELIVERED', 'FULLY_DELIVERED', 'CANCELLED'
    ];

    /**
     * MANDATORY SERVICE INJECTION for cryptographic audit compliance
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * PO management dashboard - CRITICAL FOR PROCUREMENT OPERATIONS
     */
    public function index(Request $request)
    {
        // STRICT PERMISSION CHECK - ONLY CEO/SYSTEM_ADMIN/STATION_MANAGER
        if (!$this->hasAccess()) {
            $this->auditService->logSecurityViolation([
                'user_id' => auth()->id(),
                'action' => 'PO_ACCESS_DENIED',
                'ip_address' => request()->ip(),
                'user_role' => auth()->user()->role ?? 'UNKNOWN',
                'attempted_resource' => 'purchase_orders.index'
            ]);
            return redirect()->back()->with('error', 'Access denied - PO management restricted to authorized personnel only');
        }

        // VALIDATE DATABASE AUTOMATION READINESS
        $this->validateAutomationReadiness();

        // RAPID PO FILTERING with dashboard metrics
        $query = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->join('stations', 'purchase_orders.station_id', '=', 'stations.id')
            ->join('supplier_contracts', 'purchase_orders.supplier_contract_id', '=', 'supplier_contracts.id')
            ->select([
                'purchase_orders.id',
                'purchase_orders.po_number',
                'purchase_orders.product_type',
                'purchase_orders.ordered_quantity_liters',
                'purchase_orders.total_order_value',
                'purchase_orders.expected_delivery_date',
                'purchase_orders.order_status',
                'purchase_orders.created_at',
                'suppliers.company_name',
                'suppliers.supplier_code',
                'stations.station_name',
                'supplier_contracts.contract_number'
            ]);

        // SEARCH - PO number, supplier, or station
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('purchase_orders.po_number', 'LIKE', "%{$search}%")
                  ->orWhere('suppliers.company_name', 'LIKE', "%{$search}%")
                  ->orWhere('stations.station_name', 'LIKE', "%{$search}%");
            });
        }

        // STATUS FILTER
        if ($status = $request->get('status')) {
            $query->where('purchase_orders.order_status', $status);
        }

        // PRODUCT FILTER
        if ($productType = $request->get('product_type')) {
            $query->where('purchase_orders.product_type', $productType);
        }

        $purchaseOrders = $query->orderBy('purchase_orders.created_at', 'desc')->paginate(25);

        // DASHBOARD STATISTICS WITH PRECISION (DECIMAL(15,2) compliance)
        $stats = [
            'total_pos' => DB::table('purchase_orders')->count(),
            'pending_pos' => DB::table('purchase_orders')->where('order_status', 'PENDING')->count(),
            'approved_pos' => DB::table('purchase_orders')->where('order_status', 'APPROVED')->count(),
            'overdue_deliveries' => DB::table('purchase_orders')
                ->where('order_status', 'APPROVED')
                ->where('expected_delivery_date', '<', now()->toDateString())
                ->count(),
            'total_value_pending' => round(DB::table('purchase_orders')
                ->where('order_status', 'PENDING')
                ->sum('total_order_value'), 2) // FIXED: 2 decimal precision for currency
        ];

        // ENHANCED AUDIT LOGGING
        $this->auditService->logAction([
            'user_id' => auth()->id(),
            'action_type' => 'READ',
            'action_category' => 'REPORTING',
            'table_name' => 'purchase_orders',
            'business_justification' => 'PO dashboard access by authorized role',
            'station_id' => $this->getCurrentStationId(),
            'change_reason' => 'Purchase order dashboard viewing',
            'automation_context' => [
                'trigger_awareness' => 'dashboard_access',
                'affects_deliveries' => false,
                'fifo_impact' => 'none',
                'automation_checks' => 'completed'
            ]
        ]);

        return view('purchase-orders.index', compact('purchaseOrders', 'stats'));
    }

    /**
     * PO creation form - CONTRACT-LINKED CREATION
     */
    public function create(Request $request)
    {
        // STRICT PERMISSION CHECK
        if (!$this->hasAccess()) {
            $this->auditService->logSecurityViolation([
                'user_id' => auth()->id(),
                'action' => 'PO_CREATE_ACCESS_DENIED',
                'ip_address' => request()->ip(),
                'user_role' => auth()->user()->role ?? 'UNKNOWN'
            ]);
            return redirect()->back()->with('error', 'Access denied');
        }

        // VALIDATE DATABASE AUTOMATION READINESS
        $this->validateAutomationReadiness();

        // PRE-SELECTED CONTRACT (from contract selection)
        $selectedContractId = $request->get('contract_id');
        $selectedContract = null;

        if ($selectedContractId) {
            $selectedContract = DB::table('supplier_contracts')
                ->join('suppliers', 'supplier_contracts.supplier_id', '=', 'suppliers.id')
                ->where('supplier_contracts.id', $selectedContractId)
                ->where('supplier_contracts.is_active', 1)
                ->where('supplier_contracts.effective_from', '<=', now())
                ->where('supplier_contracts.effective_until', '>=', now())
                ->select([
                    'supplier_contracts.*',
                    'suppliers.company_name',
                    'suppliers.supplier_code',
                    'suppliers.payment_terms_days'
                ])
                ->first();
        }

        // ACTIVE CONTRACTS for selection
        $activeContracts = DB::table('supplier_contracts')
            ->join('suppliers', 'supplier_contracts.supplier_id', '=', 'suppliers.id')
            ->where('supplier_contracts.is_active', 1)
            ->where('supplier_contracts.effective_from', '<=', now())
            ->where('supplier_contracts.effective_until', '>=', now())
            ->select([
                'supplier_contracts.id',
                'supplier_contracts.contract_number',
                'supplier_contracts.product_type',
                'supplier_contracts.base_price_per_liter',
                'supplier_contracts.minimum_quantity_liters',
                'supplier_contracts.maximum_quantity_liters',
                'suppliers.company_name',
                'suppliers.supplier_code'
            ])
            ->orderBy('suppliers.company_name')
            ->get();

        // STATIONS for selection
        $stations = DB::table('stations')
            ->where('is_active', 1)
            ->select(['id', 'station_name', 'station_code'])
            ->orderBy('station_name')
            ->get();

        // GENERATE PO NUMBER
        $nextPONumber = $this->generateNextPONumber();

        // SMART DEFAULTS
        $defaults = [
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
            'expected_delivery_time' => '10:00:00',
            'transport_cost_per_liter' => 0.0000,
            'other_charges_per_liter' => 0.0000
        ];

        // AUDIT LOGGING
        $this->auditService->logAction([
            'user_id' => auth()->id(),
            'action_type' => 'READ',
            'action_category' => 'DATA_ENTRY',
            'table_name' => 'purchase_orders',
            'business_justification' => 'PO creation form access by authorized role',
            'station_id' => $this->getCurrentStationId(),
            'change_reason' => 'Purchase order creation form accessed',
            'automation_context' => [
                'trigger_awareness' => 'form_access',
                'affects_deliveries' => true,
                'fifo_impact' => 'potential_delivery_layer_creation',
                'automation_checks' => 'completed'
            ]
        ]);

        return view('purchase-orders.create', compact(
            'selectedContract', 'activeContracts', 'stations', 'nextPONumber', 'defaults'
        ));
    }

    /**
     * Show PO details
     */
    public function show($id)
    {
        // STRICT PERMISSION CHECK
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // GET PO with all related data
        $po = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->join('stations', 'purchase_orders.station_id', '=', 'stations.id')
            ->join('supplier_contracts', 'purchase_orders.supplier_contract_id', '=', 'supplier_contracts.id')
            ->where('purchase_orders.id', $id)
            ->select([
                'purchase_orders.*',
                'suppliers.company_name',
                'suppliers.supplier_code',
                'suppliers.contact_person',
                'suppliers.email',
                'suppliers.phone',
                'stations.station_name',
                'stations.station_code',
                'supplier_contracts.contract_number'
            ])
            ->first();

        if (!$po) {
            return redirect()->route('purchase-orders.index')->with('error', 'Purchase order not found');
        }

        // GET DELIVERY STATUS with automation validation
        $deliveryStatus = DB::table('deliveries')
            ->where('purchase_order_id', $id)
            ->selectRaw('COUNT(*) as delivery_count, SUM(quantity_delivered_liters) as total_delivered')
            ->first();

        // VALIDATE DELIVERY AUTOMATION INTEGRITY
        $this->validateDeliveryAutomationIntegrity($id);

        // AUDIT LOGGING
        $this->auditService->logAction([
            'user_id' => auth()->id(),
            'action_type' => 'READ',
            'action_category' => 'REPORTING',
            'table_name' => 'purchase_orders',
            'record_id' => $id,
            'business_justification' => 'PO details viewing by authorized role',
            'station_id' => $po->station_id,
            'change_reason' => 'Purchase order details accessed',
            'automation_context' => [
                'trigger_awareness' => 'detail_access',
                'affects_deliveries' => false,
                'fifo_impact' => 'none',
                'delivery_automation_validated' => true
            ]
        ]);

        return view('purchase-orders.show', compact('po', 'deliveryStatus'));
    }

    /**
     * Edit PO form
     */
    public function edit($id)
    {
        // STRICT PERMISSION CHECK
        if (!$this->hasAccess()) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // GET PO
        $po = DB::table('purchase_orders')
            ->join('supplier_contracts', 'purchase_orders.supplier_contract_id', '=', 'supplier_contracts.id')
            ->join('suppliers', 'supplier_contracts.supplier_id', '=', 'suppliers.id')
            ->where('purchase_orders.id', $id)
            ->select([
                'purchase_orders.*',
                'supplier_contracts.contract_number',
                'supplier_contracts.base_price_per_liter',
                'supplier_contracts.minimum_quantity_liters',
                'supplier_contracts.maximum_quantity_liters',
                'suppliers.company_name'
            ])
            ->first();

        if (!$po) {
            return redirect()->route('purchase-orders.index')->with('error', 'Purchase order not found');
        }

        // ONLY PENDING POs can be edited
        if ($po->order_status !== 'PENDING') {
            return redirect()->route('purchase-orders.show', $id)->with('error', 'Only pending POs can be edited');
        }

        // STATIONS for selection
        $stations = DB::table('stations')
            ->where('is_active', 1)
            ->select(['id', 'station_name', 'station_code'])
            ->orderBy('station_name')
            ->get();

        // AUDIT LOGGING
        $this->auditService->logAction([
            'user_id' => auth()->id(),
            'action_type' => 'READ',
            'action_category' => 'DATA_ENTRY',
            'table_name' => 'purchase_orders',
            'record_id' => $id,
            'business_justification' => 'PO edit form access by authorized role',
            'station_id' => $po->station_id,
            'change_reason' => 'Purchase order edit form accessed',
            'automation_context' => [
                'trigger_awareness' => 'edit_form_access',
                'affects_deliveries' => true,
                'fifo_impact' => 'potential_modification',
                'current_status' => $po->order_status
            ]
        ]);

        return view('purchase-orders.edit', compact('po', 'stations'));
    }

    /**
     * PO generation with PRECISE PRICING CALCULATION - CONTRACT-BASED
     * FIXED: Mathematical precision compliance with schema DECIMAL(15,2)
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // STRICT PERMISSION CHECK WITH EXPLICIT ROLE VALIDATION
            if (!$this->hasAccess()) {
                $this->auditService->logSecurityViolation([
                    'user_id' => auth()->id(),
                    'action' => 'PO_CREATE_ACCESS_DENIED',
                    'ip_address' => request()->ip(),
                    'user_role' => auth()->user()->role ?? 'UNKNOWN'
                ]);
                return response()->json(['error' => 'Access denied'], 403);
            }

            // VALIDATE AUTOMATION READINESS BEFORE PROCESSING
            $this->validateAutomationReadiness();

            // ENHANCED VALIDATION WITH PRECISION
            $validated = $request->validate([
                'supplier_contract_id' => 'required|exists:supplier_contracts,id',
                'station_id' => 'required|exists:stations,id',
                'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
                'ordered_quantity_liters' => 'required|numeric|min:0.001|max:999999999.999',
                'expected_delivery_date' => 'required|date|after_or_equal:today',
                'expected_delivery_time' => 'nullable|date_format:H:i',
                'transport_cost_per_liter' => 'nullable|numeric|min:0|max:99999.9999',
                'other_charges_per_liter' => 'nullable|numeric|min:0|max:99999.9999'
            ]);

            // GET CONTRACT DETAILS WITH EXPIRY VALIDATION
            $contract = DB::table('supplier_contracts')
                ->where('id', $validated['supplier_contract_id'])
                ->where('is_active', 1)
                ->where('effective_from', '<=', now())
                ->where('effective_until', '>=', now())
                ->first();

            if (!$contract) {
                return response()->json(['error' => 'Contract not found or expired'], 422);
            }

            // CRITICAL CONTRACT EXPIRY VALIDATION
            if ($contract->effective_until < now()) {
                return response()->json(['error' => 'Contract expired on ' . $contract->effective_until], 422);
            }

            // ENHANCED QUANTITY VALIDATION AGAINST CONTRACT
            $quantity = round($validated['ordered_quantity_liters'], 3);
            if ($quantity < $contract->minimum_quantity_liters || $quantity > $contract->maximum_quantity_liters) {
                return response()->json([
                    'error' => "Quantity must be between {$contract->minimum_quantity_liters}L and {$contract->maximum_quantity_liters}L per contract terms"
                ], 422);
            }

            // PRECISE PRICING CALCULATION with volume discounts (0.0001 precision)
            $basePrice = round($contract->base_price_per_liter, 4);
            $volumeDiscount = $this->calculateVolumeDiscountWithValidation($contract, $quantity);
            $agreedPrice = round($basePrice - $volumeDiscount, 4);

            // PRECISE COST COMPONENTS
            $transportCost = round($validated['transport_cost_per_liter'] ?? 0.0000, 4);
            $otherCharges = round($validated['other_charges_per_liter'] ?? 0.0000, 4);

            // MATHEMATICALLY PRECISE TOTAL ORDER VALUE - FIXED: DECIMAL(15,2) compliance
            $totalOrderValue = round($quantity * ($agreedPrice + $transportCost + $otherCharges), 2);

            // EXPLICIT ROLE-BASED AUTO-APPROVAL VALIDATION
            $currentUserRole = auth()->user()->role ?? '';
            if (!in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER'])) {
                return response()->json(['error' => 'Unauthorized role for auto-approval'], 403);
            }

            // AUTO-APPROVAL FOR AUTHORIZED ROLES WITH ENHANCED AUDIT
            $poData = [
                'po_number' => strtoupper($validated['po_number']),
                'supplier_id' => $contract->supplier_id,
                'supplier_contract_id' => $validated['supplier_contract_id'],
                'station_id' => $validated['station_id'],
                'product_type' => $contract->product_type,
                'ordered_quantity_liters' => $quantity,
                'agreed_price_per_liter' => $agreedPrice,
                'transport_cost_per_liter' => $transportCost,
                'other_charges_per_liter' => $otherCharges,
                'total_order_value' => $totalOrderValue, // FIXED: 2 decimal precision
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'expected_delivery_time' => $validated['expected_delivery_time'],
                'order_status' => 'APPROVED', // AUTO-APPROVED per business rules
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id()
            ];

            // INSERT PO
            $poId = DB::table('purchase_orders')->insertGetId($poData);

            // ENHANCED AUDIT LOGGING WITH CRYPTOGRAPHIC INTEGRITY AND AUTOMATION CONTEXT
            $this->auditService->logAutoApproval([
                'user_id' => auth()->id(),
                'action_type' => 'CREATE',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'purchase_orders',
                'record_id' => $poId,
                'new_values' => json_encode($poData),
                'business_justification' => 'Auto-approved PO creation by authorized role: ' . $currentUserRole,
                'station_id' => $validated['station_id'],
                'change_reason' => 'Purchase order created and auto-approved',
                'is_auto_approved' => true,
                'automation_context' => [
                    'trigger_awareness' => 'purchase_order_creation',
                    'affects_deliveries' => true,
                    'fifo_impact' => 'delivery_layer_creation_pending',
                    'automation_config_validated' => true,
                    'role_based_approval' => $currentUserRole,
                    'mathematical_precision_applied' => 'DECIMAL_15_2_compliance'
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PO created and auto-approved successfully',
                'po' => [
                    'id' => $poId,
                    'po_number' => $poData['po_number'],
                    'total_value' => $totalOrderValue,
                    'status' => 'APPROVED'
                ],
                'redirect' => route('purchase-orders.show', $poId)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollback();

            // ENHANCED ERROR LOGGING WITH AUTOMATION CONTEXT
            $this->auditService->logError([
                'user_id' => auth()->id(),
                'action_type' => 'CREATE',
                'table_name' => 'purchase_orders',
                'error_message' => $e->getMessage(),
                'error_context' => 'PO creation failed with automation awareness',
                'automation_context' => [
                    'automation_validated' => false,
                    'failure_point' => 'po_creation_process'
                ]
            ]);

            return response()->json(['error' => 'PO creation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update PO with enhanced validation and precision - FIXED
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // STRICT PERMISSION CHECK
            if (!$this->hasAccess()) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // VALIDATE AUTOMATION READINESS
            $this->validateAutomationReadiness();

            // GET PO
            $po = DB::table('purchase_orders')->where('id', $id)->first();
            if (!$po) {
                return response()->json(['error' => 'PO not found'], 404);
            }

            // ONLY PENDING POs can be updated
            if ($po->order_status !== 'PENDING') {
                return response()->json(['error' => 'Only pending POs can be updated'], 422);
            }

            // ENHANCED VALIDATION
            $validated = $request->validate([
                'station_id' => 'required|exists:stations,id',
                'ordered_quantity_liters' => 'required|numeric|min:0.001|max:999999999.999',
                'expected_delivery_date' => 'required|date|after_or_equal:today',
                'expected_delivery_time' => 'nullable|date_format:H:i',
                'transport_cost_per_liter' => 'nullable|numeric|min:0|max:99999.9999',
                'other_charges_per_liter' => 'nullable|numeric|min:0|max:99999.9999'
            ]);

            // GET CONTRACT for recalculation with expiry check
            $contract = DB::table('supplier_contracts')
                ->where('id', $po->supplier_contract_id)
                ->where('is_active', 1)
                ->first();

            if (!$contract || $contract->effective_until < now()) {
                return response()->json(['error' => 'Contract not found or expired'], 422);
            }

            // PRECISE RECALCULATION WITH VALIDATION
            $quantity = round($validated['ordered_quantity_liters'], 3);

            // QUANTITY LIMITS VALIDATION
            if ($quantity < $contract->minimum_quantity_liters || $quantity > $contract->maximum_quantity_liters) {
                return response()->json([
                    'error' => "Quantity must be between {$contract->minimum_quantity_liters}L and {$contract->maximum_quantity_liters}L"
                ], 422);
            }

            $basePrice = round($contract->base_price_per_liter, 4);
            $volumeDiscount = $this->calculateVolumeDiscountWithValidation($contract, $quantity);
            $agreedPrice = round($basePrice - $volumeDiscount, 4);
            $transportCost = round($validated['transport_cost_per_liter'] ?? 0.0000, 4);
            $otherCharges = round($validated['other_charges_per_liter'] ?? 0.0000, 4);

            // FIXED: DECIMAL(15,2) compliance for total_order_value
            $totalOrderValue = round($quantity * ($agreedPrice + $transportCost + $otherCharges), 2);

            // UPDATE PO with precise values
            $updateData = [
                'station_id' => $validated['station_id'],
                'ordered_quantity_liters' => $quantity,
                'agreed_price_per_liter' => $agreedPrice,
                'transport_cost_per_liter' => $transportCost,
                'other_charges_per_liter' => $otherCharges,
                'total_order_value' => $totalOrderValue, // FIXED: 2 decimal precision
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'expected_delivery_time' => $validated['expected_delivery_time'],
                'updated_at' => now()
            ];

            DB::table('purchase_orders')->where('id', $id)->update($updateData);

            // ENHANCED AUDIT LOGGING WITH AUTOMATION CONTEXT
            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'purchase_orders',
                'record_id' => $id,
                'old_values' => json_encode($po),
                'new_values' => json_encode($updateData),
                'business_justification' => 'PO update by authorized user',
                'station_id' => $validated['station_id'],
                'change_reason' => 'Purchase order modification',
                'automation_context' => [
                    'trigger_awareness' => 'purchase_order_update',
                    'affects_deliveries' => true,
                    'fifo_impact' => 'potential_delivery_changes',
                    'mathematical_precision_applied' => 'DECIMAL_15_2_compliance'
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PO updated successfully',
                'redirect' => route('purchase-orders.show', $id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE',
                'table_name' => 'purchase_orders',
                'record_id' => $id,
                'error_message' => $e->getMessage(),
                'error_context' => 'PO update failed',
                'automation_context' => [
                    'automation_validated' => false,
                    'failure_point' => 'po_update_process'
                ]
            ]);

            return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete/Cancel PO with enhanced audit
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // STRICT PERMISSION CHECK
            if (!$this->hasAccess()) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // GET PO
            $po = DB::table('purchase_orders')->where('id', $id)->first();
            if (!$po) {
                return response()->json(['error' => 'PO not found'], 404);
            }

            // ONLY PENDING POs can be deleted/cancelled
            if ($po->order_status !== 'PENDING') {
                return response()->json(['error' => 'Only pending POs can be cancelled'], 422);
            }

            // UPDATE STATUS TO CANCELLED (preserve audit trail)
            DB::table('purchase_orders')->where('id', $id)->update([
                'order_status' => 'CANCELLED',
                'updated_at' => now()
            ]);

            // ENHANCED AUDIT LOGGING WITH AUTOMATION CONTEXT
            $this->auditService->logAction([
                'user_id' => auth()->id(),
                'action_type' => 'DELETE',
                'action_category' => 'DATA_ENTRY',
                'table_name' => 'purchase_orders',
                'record_id' => $id,
                'old_values' => json_encode($po),
                'new_values' => 'CANCELLED',
                'business_justification' => 'PO cancellation by authorized user',
                'station_id' => $po->station_id,
                'change_reason' => 'Purchase order cancelled',
                'automation_context' => [
                    'trigger_awareness' => 'purchase_order_cancellation',
                    'affects_deliveries' => false,
                    'fifo_impact' => 'none',
                    'status_change' => 'PENDING_to_CANCELLED'
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PO cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'action_type' => 'DELETE',
                'table_name' => 'purchase_orders',
                'record_id' => $id,
                'error_message' => $e->getMessage(),
                'error_context' => 'PO cancellation failed',
                'automation_context' => [
                    'automation_validated' => false,
                    'failure_point' => 'po_cancellation_process'
                ]
            ]);

            return response()->json(['error' => 'Cancellation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Link completed deliveries to POs with automation awareness - ENHANCED
     */
    public function receive($id, Request $request)
    {
        DB::beginTransaction();

        try {
            // STRICT PERMISSION CHECK
            if (!$this->hasAccess()) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // VALIDATE AUTOMATION READINESS
            $this->validateAutomationReadiness();

            // GET PO
            $po = DB::table('purchase_orders')->where('id', $id)->first();
            if (!$po) {
                return response()->json(['error' => 'PO not found'], 404);
            }

            // GET DELIVERY TOTALS linked to this PO
            $deliveredQuantity = round(DB::table('deliveries')
                ->where('purchase_order_id', $id)
                ->where('delivery_status', 'COMPLETED')
                ->sum('quantity_delivered_liters'), 3);

            // DETERMINE NEW STATUS WITH PRECISION
            $newStatus = 'APPROVED';
            if ($deliveredQuantity >= round($po->ordered_quantity_liters, 3)) {
                $newStatus = 'FULLY_DELIVERED';
            } elseif ($deliveredQuantity > 0.001) {
                $newStatus = 'PARTIALLY_DELIVERED';
            }

            // UPDATE STATUS if changed
            if ($newStatus !== $po->order_status) {
                DB::table('purchase_orders')->where('id', $id)->update([
                    'order_status' => $newStatus,
                    'updated_at' => now()
                ]);

                // ENHANCED AUDIT LOGGING WITH AUTOMATION CONTEXT
                $this->auditService->logAction([
                    'user_id' => auth()->id(),
                    'action_type' => 'UPDATE',
                    'action_category' => 'DATA_ENTRY',
                    'table_name' => 'purchase_orders',
                    'record_id' => $id,
                    'old_values' => $po->order_status,
                    'new_values' => $newStatus,
                    'business_justification' => 'PO status update based on delivery completion',
                    'station_id' => $po->station_id,
                    'change_reason' => 'Automatic status update from delivery processing',
                    'automation_context' => [
                        'trigger_awareness' => 'delivery_completion_status_update',
                        'affects_deliveries' => true,
                        'fifo_impact' => 'layer_creation_validated',
                        'delivery_automation_validated' => true,
                        'status_transition' => "{$po->order_status}_to_{$newStatus}"
                    ]
                ]);

                // CHECK FOR DATABASE AUTOMATION INTEGRATION
                $this->validateDeliveryAutomationIntegrity($id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PO receipt status updated',
                'po_number' => $po->po_number,
                'new_status' => $newStatus,
                'delivered_quantity' => $deliveredQuantity
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE',
                'table_name' => 'purchase_orders',
                'record_id' => $id,
                'error_message' => $e->getMessage(),
                'error_context' => 'PO receipt update failed',
                'automation_context' => [
                    'automation_validated' => false,
                    'failure_point' => 'po_receipt_update_process'
                ]
            ]);

            return response()->json(['error' => 'Receipt update failed: ' . $e->getMessage()], 500);
        }
    }

    // ================================
    // PRIVATE HELPER METHODS - ENHANCED AND FIXED
    // ================================

    /**
     * Check if user has access - ONLY CEO/SYSTEM_ADMIN/STATION_MANAGER
     */
    private function hasAccess(): bool
    {
        $userRole = auth()->user()->role ?? '';
        return in_array($userRole, ['CEO', 'SYSTEM_ADMIN', 'STATION_MANAGER']);
    }

    /**
     * Generate next PO number with enhanced formatting
     */
    private function generateNextPONumber(): string
    {
        $currentYear = date('Y');
        $prefix = 'PO-' . $currentYear . '-';

        $lastNumber = DB::table('purchase_orders')
            ->where('po_number', 'LIKE', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->value('po_number');

        if ($lastNumber) {
            $number = (int) substr($lastNumber, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate volume discount with enhanced validation and precision
     */
    private function calculateVolumeDiscountWithValidation($contract, float $quantity): float
    {
        // VALIDATE QUANTITY WITHIN CONTRACT LIMITS
        if ($quantity < $contract->minimum_quantity_liters || $quantity > $contract->maximum_quantity_liters) {
            throw new \Exception("Quantity {$quantity}L outside contract limits ({$contract->minimum_quantity_liters}L - {$contract->maximum_quantity_liters}L)");
        }

        $discount = 0.0000;

        // TIER 3 (highest) - PRECISE CALCULATIONS
        if ($quantity >= $contract->volume_discount_tier_3_threshold && $contract->volume_discount_tier_3_threshold > 0) {
            $discount = round($contract->volume_discount_tier_3_amount, 4);
        }
        // TIER 2
        elseif ($quantity >= $contract->volume_discount_tier_2_threshold && $contract->volume_discount_tier_2_threshold > 0) {
            $discount = round($contract->volume_discount_tier_2_amount, 4);
        }
        // TIER 1
        elseif ($quantity >= $contract->volume_discount_tier_1_threshold && $contract->volume_discount_tier_1_threshold > 0) {
            $discount = round($contract->volume_discount_tier_1_amount, 4);
        }

        return $discount;
    }

    /**
     * NEW: Validate database automation readiness - CRITICAL FOR INTEGRATION
     */
    private function validateAutomationReadiness(): void
    {
        // CHECK CRITICAL AUTOMATION CONFIGURATIONS
        $requiredConfigs = [
            'AUTO_DELIVERY_LAYER_CREATION',
            'ENHANCED_FIFO_PROCESSING_ENABLED',
            'ENHANCED_MONITORING_ENABLED'
        ];

        $missingConfigs = [];
        $disabledConfigs = [];

        foreach ($requiredConfigs as $configKey) {
            $config = DB::table('system_configurations')
                ->where('config_key', $configKey)
                ->where('is_system_critical', 1)
                ->first();

            if (!$config) {
                $missingConfigs[] = $configKey;
            } elseif (!$config->config_value_boolean) {
                $disabledConfigs[] = $configKey;
            }
        }

        if (!empty($missingConfigs) || !empty($disabledConfigs)) {
            $errorMessage = 'Automation readiness check failed. ';
            if (!empty($missingConfigs)) {
                $errorMessage .= 'Missing configs: ' . implode(', ', $missingConfigs) . '. ';
            }
            if (!empty($disabledConfigs)) {
                $errorMessage .= 'Disabled configs: ' . implode(', ', $disabledConfigs) . '. ';
            }

            $this->auditService->logError([
                'user_id' => auth()->id(),
                'action_type' => 'READ',
                'table_name' => 'system_configurations',
                'error_message' => $errorMessage,
                'error_context' => 'Automation readiness validation failed'
            ]);

            throw new \Exception($errorMessage);
        }
    }

    /**
     * Validate delivery automation integrity - ENHANCED
     */
    private function validateDeliveryAutomationIntegrity(int $poId): void
    {
        // CHECK FOR FIFO LAYER CREATION (tr_enhanced_delivery_fifo_layers)
        $deliveries = DB::table('deliveries')
            ->where('purchase_order_id', $poId)
            ->where('delivery_status', 'COMPLETED')
            ->pluck('id');

        foreach ($deliveries as $deliveryId) {
            $layerExists = DB::table('tank_inventory_layers')
                ->where('delivery_id', $deliveryId)
                ->exists();

            if (!$layerExists) {
                // LOG AUTOMATION FAILURE WITH ENHANCED CONTEXT
                $this->auditService->logError([
                    'user_id' => auth()->id(),
                    'action_type' => 'READ',
                    'table_name' => 'tank_inventory_layers',
                    'error_message' => "tr_enhanced_delivery_fifo_layers trigger failed for delivery {$deliveryId}",
                    'error_context' => 'Database automation integrity check failed',
                    'automation_context' => [
                        'trigger_name' => 'tr_enhanced_delivery_fifo_layers',
                        'delivery_id' => $deliveryId,
                        'po_id' => $poId,
                        'integrity_check' => 'FAILED'
                    ]
                ]);

                // CHECK IF AUTOMATION IS DISABLED
                $automationEnabled = DB::table('system_configurations')
                    ->where('config_key', 'AUTO_DELIVERY_LAYER_CREATION')
                    ->value('config_value_boolean');

                if (!$automationEnabled) {
                    throw new \Exception("Delivery automation disabled - FIFO layer not created for delivery {$deliveryId}");
                } else {
                    throw new \Exception("Critical automation failure - FIFO layer not created for delivery {$deliveryId}");
                }
            }
        }
    }

    /**
     * Get current station context for audit logging - FIXED: Now properly used
     */
    private function getCurrentStationId(): ?int
    {
        // Get from user's station assignment or request context
        $userStations = DB::table('user_stations')
            ->where('user_id', auth()->id())
            ->where('is_active', 1)
            ->orderBy('assignment_start_date', 'desc')
            ->first();

        return $userStations->station_id ?? null;
    }
}
