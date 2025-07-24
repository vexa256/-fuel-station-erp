<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * EXACT SCHEMA FIELD MAPPING - supplier_contracts table
     */
    private const CONTRACT_FIELDS = [
        'id', 'supplier_id', 'contract_number', 'product_type', 'base_price_per_liter',
        'minimum_quantity_liters', 'maximum_quantity_liters', 'effective_from', 'effective_until',
        'is_active', 'created_at', 'updated_at', 'created_by'
    ];

    /**
     * VERIFIED PRODUCT TYPES from schema - EXACT ENUM VALUES
     */
    private const PRODUCT_TYPES = [
        'PETROL_95', 'PETROL_98', 'DIESEL', 'KEROSENE', 'JET_A1', 'HEAVY_FUEL_OIL',
        'LIGHT_FUEL_OIL', 'LPG_AUTOGAS', 'ETHANOL_E10', 'ETHANOL_E85', 'BIODIESEL_B7', 'BIODIESEL_B20'
    ];

    /**
     * Active contracts list - CRITICAL FOR PO CREATION
     */
    public function index(Request $request)
    {
        // PERMISSION CHECK - CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('CONTRACT_VIEW')) {
            return redirect()->back()->with('error', 'Insufficient permissions for contract access');
        }

        // BASIC FILTERING - only active contracts
        $query = DB::table('supplier_contracts')
            ->join('suppliers', 'supplier_contracts.supplier_id', '=', 'suppliers.id')
            ->select([
                'supplier_contracts.id',
                'supplier_contracts.contract_number',
                'supplier_contracts.product_type',
                'supplier_contracts.base_price_per_liter',
                'supplier_contracts.effective_from',
                'supplier_contracts.effective_until',
                'suppliers.company_name',
                'suppliers.supplier_code'
            ])
            ->where('supplier_contracts.is_active', 1)
            ->where('supplier_contracts.effective_from', '<=', now())
            ->where('supplier_contracts.effective_until', '>=', now());

        // SIMPLE SEARCH - contract number or supplier name
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('supplier_contracts.contract_number', 'LIKE', "%{$search}%")
                  ->orWhere('suppliers.company_name', 'LIKE', "%{$search}%");
            });
        }

        // SIMPLE PRODUCT FILTER
        if ($productType = $request->get('product_type')) {
            $query->where('supplier_contracts.product_type', $productType);
        }

        $contracts = $query->orderBy('suppliers.company_name')->paginate(25);

        // BASIC STATISTICS
        $stats = [
            'active_contracts' => DB::table('supplier_contracts')
                ->where('is_active', 1)
                ->where('effective_from', '<=', now())
                ->where('effective_until', '>=', now())
                ->count(),
            'expiring_soon' => DB::table('supplier_contracts')
                ->where('is_active', 1)
                ->where('effective_until', '>=', now())
                ->where('effective_until', '<=', now()->addDays(30))
                ->count()
        ];

        // LOG ACCESS
        $this->logAction([
            'user_id' => auth()->id(),
            'action' => 'CONTRACT_LIST_ACCESSED',
            'table_name' => 'supplier_contracts',
            'record_id' => null,
            'old_values' => null,
            'new_values' => json_encode(['filters' => $request->all()]),
            'business_justification' => 'Contract directory access for procurement operations',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return view('contracts.index', compact('contracts', 'stats'));
    }

    /**
     * Contract creation form - MINIMAL REQUIRED FIELDS
     */
    public function create(Request $request)
    {
        // PERMISSION CHECK
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('CONTRACT_CREATE')) {
            return redirect()->back()->with('error', 'Insufficient permissions');
        }

        // SUPPLIERS LIST
        $suppliers = DB::table('suppliers')
            ->where('is_active', 1)
            ->select(['id', 'supplier_code', 'company_name'])
            ->orderBy('company_name')
            ->get();

        // GENERATE CONTRACT NUMBER
        $nextContractNumber = $this->generateNextContractNumber();

        // MINIMAL DEFAULTS
        $defaults = [
            'effective_from' => now()->toDateString(),
            'effective_until' => now()->addMonths(12)->toDateString(),
            'minimum_quantity_liters' => 5000.000,
            'maximum_quantity_liters' => 999999999.999,
            'is_active' => 1
        ];

        return view('contracts.create', compact('suppliers', 'nextContractNumber', 'defaults'));
    }

    /**
     * STORE contract - MINIMAL VALIDATION
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // PERMISSION CHECK
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermission('CONTRACT_CREATE')) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            // MINIMAL VALIDATION
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'contract_number' => 'required|string|max:100|unique:supplier_contracts,contract_number',
                'product_type' => ['required', Rule::in(self::PRODUCT_TYPES)],
                'base_price_per_liter' => 'required|numeric|min:0|max:999999.9999',
                'minimum_quantity_liters' => 'required|numeric|min:0|max:999999999.999',
                'maximum_quantity_liters' => 'required|numeric|min:0|max:999999999.999',
                'effective_from' => 'required|date',
                'effective_until' => 'required|date|after:effective_from',
                'is_active' => 'boolean'
            ]);

            // BASIC BUSINESS RULE
            if ($validated['maximum_quantity_liters'] < $validated['minimum_quantity_liters']) {
                return response()->json([
                    'error' => 'Maximum quantity cannot be less than minimum quantity'
                ], 422);
            }

            // PREPARE DATA - MINIMAL REQUIRED FIELDS
            $contractData = [
                'supplier_id' => $validated['supplier_id'],
                'contract_number' => strtoupper($validated['contract_number']),
                'product_type' => $validated['product_type'],
                'base_price_per_liter' => $validated['base_price_per_liter'],
                'minimum_quantity_liters' => $validated['minimum_quantity_liters'],
                'maximum_quantity_liters' => $validated['maximum_quantity_liters'],
                'volume_discount_tier_1_threshold' => 0.000,
                'volume_discount_tier_1_amount' => 0.0000,
                'volume_discount_tier_2_threshold' => 0.000,
                'volume_discount_tier_2_amount' => 0.0000,
                'volume_discount_tier_3_threshold' => 0.000,
                'volume_discount_tier_3_amount' => 0.0000,
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'],
                'is_active' => $validated['is_active'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id()
            ];

            // INSERT CONTRACT
            $contractId = DB::table('supplier_contracts')->insertGetId($contractData);

            // LOG CREATION
            $this->logAction([
                'user_id' => auth()->id(),
                'action' => 'CONTRACT_CREATED',
                'table_name' => 'supplier_contracts',
                'record_id' => $contractId,
                'old_values' => null,
                'new_values' => json_encode($contractData),
                'business_justification' => 'New supplier contract setup for fuel procurement',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contract created successfully',
                'contract' => [
                    'id' => $contractId,
                    'contract_number' => $contractData['contract_number'],
                    'product_type' => $contractData['product_type']
                ],
                'redirect' => route('contracts.show', $contractId)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Contract creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CONTRACT DETAILS - CRITICAL FOR PO CREATION
     */
    public function show($id)
    {
        // PERMISSION CHECK
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('CONTRACT_VIEW')) {
            return redirect()->back()->with('error', 'Insufficient permissions');
        }

        // GET CONTRACT
        $contract = DB::table('supplier_contracts')
            ->join('suppliers', 'supplier_contracts.supplier_id', '=', 'suppliers.id')
            ->select([
                'supplier_contracts.*',
                'suppliers.company_name',
                'suppliers.supplier_code',
                'suppliers.contact_person',
                'suppliers.email',
                'suppliers.phone',
                'suppliers.currency_code',
                'suppliers.payment_terms_days'
            ])
            ->where('supplier_contracts.id', $id)
            ->first();

        if (!$contract) {
            return redirect()->route('contracts.index')
                ->with('error', 'Contract not found');
        }

        // MINIMAL RELATED DATA - CRITICAL FOR PO CREATION
        $relatedData = [
           'recent_orders' => DB::table('purchase_orders')
    ->where('supplier_contract_id', $id)
    ->select(['id', 'po_number', 'created_at', 'ordered_quantity_liters', 'total_order_value', 'order_status'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(),

          'usage_summary' => DB::table('purchase_orders')
    ->where('supplier_contract_id', $id)
    ->selectRaw('COUNT(*) as total_orders, SUM(ordered_quantity_liters) as total_quantity')
    ->first()
        ];

        // CONTRACT STATUS
        $contractStatus = [
            'is_active' => (bool) $contract->is_active,
            'is_current' => now()->between($contract->effective_from, $contract->effective_until),
            'days_remaining' => Carbon::parse($contract->effective_until)->diffInDays(now(), false)
        ];

        // LOG VIEW
        $this->logAction([
            'user_id' => auth()->id(),
            'action' => 'CONTRACT_PROFILE_VIEWED',
            'table_name' => 'supplier_contracts',
            'record_id' => $id,
            'old_values' => null,
            'new_values' => json_encode(['contract_number' => $contract->contract_number]),
            'business_justification' => 'Contract profile access for procurement review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return view('contracts.show', compact('contract', 'relatedData', 'contractStatus'));
    }

    // ================================
    // PRIVATE HELPER METHODS - MINIMAL
    // ================================

    /**
     * Generate contract number
     */
    private function generateNextContractNumber(): string
    {
        $currentYear = date('Y');
        $prefix = 'CTR-' . $currentYear . '-';

        $lastNumber = DB::table('supplier_contracts')
            ->where('contract_number', 'LIKE', $prefix . '%')
            ->orderBy('contract_number', 'desc')
            ->value('contract_number');

        if ($lastNumber) {
            $number = (int) substr($lastNumber, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check permissions
     */
    private function hasPermission(string $permission): bool
    {
        $userPermissions = DB::table('user_stations')
            ->where('user_id', auth()->id())
            ->where('is_active', 1)
            ->pluck('access_level')
            ->toArray();

        $permissionMap = [
            'CONTRACT_VIEW' => ['FULL_ACCESS', 'SUPERVISOR', 'MANAGER'],
            'CONTRACT_CREATE' => ['FULL_ACCESS', 'MANAGER']
        ];

        $allowedLevels = $permissionMap[$permission] ?? [];
        return !empty(array_intersect($userPermissions, $allowedLevels));
    }

    /**
     * Log action for audit
     */
    private function logAction(array $actionData): void
    {
        $previousHash = DB::table('audit_logs')->orderBy('id', 'desc')->value('hash_current');
        $hashData = json_encode([
            'user_id' => $actionData['user_id'],
            'action' => $actionData['action'],
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'previous_hash' => $previousHash
        ]);
        $currentHash = hash('sha256', $hashData);

        DB::table('audit_logs')->insert([
            'user_id' => $actionData['user_id'],
            'session_id' => session()->getId(),
            'station_id' => null,
            'action_type' => $this->getActionType($actionData['action']),
            'action_category' => 'DATA_ENTRY',
            'table_name' => $actionData['table_name'],
            'record_id' => $actionData['record_id'],
            'old_value_text' => $actionData['old_values'],
            'new_value_text' => $actionData['new_values'],
            'change_reason' => $actionData['business_justification'],
            'business_justification' => $actionData['business_justification'],
            'ip_address' => $actionData['ip_address'],
            'user_agent' => $actionData['user_agent'],
            'hash_previous' => $previousHash,
            'hash_current' => $currentHash,
            'hash_data' => $hashData,
            'hash_algorithm' => 'SHA256',
            'risk_level' => 'LOW',
            'sensitivity_level' => 'INTERNAL',
            'compliance_category' => 'NONE',
            'system_generated' => 0,
            'batch_operation' => 0,
            'error_occurred' => 0,
            'timestamp' => now()
        ]);
    }

    /**
     * Get action type for audit
     */
    private function getActionType(string $action): string
    {
        $actionTypes = [
            'CONTRACT_LIST_ACCESSED' => 'READ',
            'CONTRACT_CREATED' => 'CREATE',
            'CONTRACT_PROFILE_VIEWED' => 'READ'
        ];
        return $actionTypes[$action] ?? 'CREATE';
    }
}
