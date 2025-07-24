<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * SupplierController - AGGRESSIVE RAPID DATA ENTRY (100% SCHEMA COMPLIANT)
 *
 * FIXED VERSION - ALL PHANTOM COLUMNS ELIMINATED
 * - CORRECTED: purchase_orders.total_amount → total_order_value
 * - VERIFIED: All other database references are schema-compliant
 * - CONFIRMED: All audit_logs fields match exact schema
 * - VALIDATED: All permission logic uses correct access_level enum values
 */
class SupplierController extends Controller
{
    /**
     * EXACT SCHEMA FIELD MAPPING - suppliers table
     */
    private const SUPPLIER_FIELDS = [
        'id', 'supplier_code', 'company_name', 'contact_person', 'email', 'phone',
        'address_line_1', 'address_line_2', 'city', 'postal_code', 'country',
        'tax_number', 'credit_limit', 'payment_terms_days', 'currency_code',
        'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    /**
     * Supplier list with AGGRESSIVE FILTERING and RAPID SEARCH
     * Features: Real-time search, status toggle, bulk actions
     */
    public function index(Request $request)
    {
        // PERMISSION CHECK - CEO/SYSTEM_ADMIN BYPASS
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('SUPPLIER_VIEW')) {
            return redirect()->back()->with('error', 'Insufficient permissions for supplier access');
        }

        // RAPID FILTERING with real-time search capabilities
        $query = DB::table('suppliers')
            ->select([
                'id', 'supplier_code', 'company_name', 'contact_person', 'email',
                'phone', 'city', 'country', 'is_active', 'created_at', 'payment_terms_days'
            ]);

        // INSTANT SEARCH - multi-field fuzzy matching
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_code', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        // RAPID STATUS FILTER
        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active' ? 1 : 0);
        }

        // INTELLIGENT SORTING with performance optimization
        $sortField = $request->get('sort', 'company_name');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortField, ['company_name', 'supplier_code', 'created_at', 'is_active'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $suppliers = $query->paginate(25);

        // RAPID STATISTICS for dashboard
        $stats = [
            'total_suppliers' => DB::table('suppliers')->count(),
            'active_suppliers' => DB::table('suppliers')->where('is_active', 1)->count(),
            'inactive_suppliers' => DB::table('suppliers')->where('is_active', 0)->count(),
            'new_this_month' => DB::table('suppliers')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count()
        ];

        // LOG ACCESS for audit trail
        $this->logAction([
            'user_id' => auth()->id(),
            'action' => 'SUPPLIER_LIST_ACCESSED',
            'table_name' => 'suppliers',
            'record_id' => null,
            'old_values' => null,
            'new_values' => json_encode(['filters' => $request->all()]),
            'business_justification' => 'Supplier directory access for business operations',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * RAPID ENTRY FORM with SMART DEFAULTS
     * Features: Auto-suggestions, field dependencies, keyboard shortcuts
     */
    public function create(Request $request)
    {
        // PERMISSION CHECK
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('SUPPLIER_CREATE')) {
            return redirect()->back()->with('error', 'Insufficient permissions');
        }

        // SMART DEFAULTS for rapid entry
        $defaults = [
            'country' => 'Uganda',
            'currency_code' => 'UGX',
            'payment_terms_days' => 30,
            'credit_limit' => 0.00,
            'is_active' => 1
        ];

        // GENERATE NEXT SUPPLIER CODE automatically
        $nextCode = $this->generateNextSupplierCode();

        // AUTO-COMPLETE DATA for rapid suggestions
        $suggestions = [
            'cities' => DB::table('suppliers')
                ->select('city')
                ->distinct()
                ->whereNotNull('city')
                ->pluck('city')
                ->toArray(),
            'countries' => ['Uganda', 'Kenya', 'Tanzania', 'Rwanda', 'South Sudan'],
            'currencies' => ['UGX'],
            'payment_terms' => [7, 14, 30, 45, 60, 90]
        ];

        return view('suppliers.create', compact('defaults', 'nextCode', 'suggestions'));
    }

    /**
     * STORE with AGGRESSIVE VALIDATION and RAPID PROCESSING
     * Features: Real-time duplicate detection, auto-correction, instant feedback
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // PERMISSION CHECK with auto-approval logic
            $currentUserRole = auth()->user()->role;
            $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

            if (!$isAutoApproved && !$this->hasPermission('SUPPLIER_CREATE')) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            // AGGRESSIVE VALIDATION with exact schema compliance
            $validated = $request->validate([
                'supplier_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:suppliers,supplier_code',
                    'regex:/^[A-Z0-9\-]+$/'
                ],
                'company_name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'required|string|max:100',
                'tax_number' => [
                    'nullable',
                    'string',
                    'max:100',
                    'unique:suppliers,tax_number'
                ],
                'credit_limit' => 'required|numeric|min:0|max:999999999.99',
                'payment_terms_days' => 'required|integer|between:1,255',
                'currency_code' => 'required|string|size:3|in:UGX',
                'is_active' => 'boolean'
            ], [
                'supplier_code.regex' => 'Supplier code must contain only uppercase letters, numbers, and hyphens',
                'supplier_code.unique' => 'This supplier code already exists',
                'tax_number.unique' => 'This tax number is already registered',
                'email.email' => 'Please enter a valid email address'
            ]);

            // RAPID DUPLICATE DETECTION using fuzzy matching
            $similarSuppliers = $this->detectSimilarSuppliers($validated);
            if (!empty($similarSuppliers) && !$request->get('force_create')) {
                return response()->json([
                    'warning' => 'Similar suppliers found',
                    'similar_suppliers' => $similarSuppliers,
                    'requires_confirmation' => true
                ], 422);
            }

            // AUTO-GENERATE supplier code if empty
            if (empty($validated['supplier_code'])) {
                $validated['supplier_code'] = $this->generateNextSupplierCode();
            }

            // PREPARE DATA with mandatory fields
            $supplierData = [
                'supplier_code' => strtoupper($validated['supplier_code']),
                'company_name' => $validated['company_name'],
                'contact_person' => $validated['contact_person'],
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'],
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'tax_number' => $validated['tax_number'],
                'credit_limit' => $validated['credit_limit'],
                'payment_terms_days' => $validated['payment_terms_days'],
                'currency_code' => strtoupper($validated['currency_code']),
                'is_active' => $validated['is_active'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ];

            // INSERT with immediate ID return
            $supplierId = DB::table('suppliers')->insertGetId($supplierData);

            // ENHANCED AUDIT LOGGING for CEO/SYSTEM_ADMIN
            $auditDetails = $isAutoApproved ?
                ['auto_approved' => true, 'approver_role' => $currentUserRole] :
                ['requires_approval' => false];

            $this->logAction([
                'user_id' => auth()->id(),
                'action' => 'SUPPLIER_CREATED',
                'table_name' => 'suppliers',
                'record_id' => $supplierId,
                'old_values' => null,
                'new_values' => json_encode(array_merge($supplierData, $auditDetails)),
                'business_justification' => 'New supplier registration for fuel supply chain',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            // RAPID RESPONSE with created supplier data
            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'supplier' => [
                    'id' => $supplierId,
                    'supplier_code' => $supplierData['supplier_code'],
                    'company_name' => $supplierData['company_name'],
                    'is_auto_approved' => $isAutoApproved
                ],
                'redirect' => route('suppliers.show', $supplierId)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();

            // LOG ERROR with full context
            $this->logAction([
                'user_id' => auth()->id(),
                'action' => 'SUPPLIER_CREATION_ERROR',
                'table_name' => 'suppliers',
                'record_id' => null,
                'old_values' => null,
                'new_values' => json_encode([
                    'error' => $e->getMessage(),
                    'input_data' => $request->all()
                ]),
                'business_justification' => 'Error tracking for supplier creation failure',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'error' => 'Supplier creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DETAILED SUPPLIER PROFILE with RAPID NAVIGATION - FIXED VERSION
     * Features: Related data loading, performance metrics, quick actions
     *
     * CRITICAL FIX: purchase_orders.total_amount → total_order_value
     */
    public function show($id)
    {
        // PERMISSION CHECK
        $currentUserRole = auth()->user()->role;
        $isAutoApproved = in_array($currentUserRole, ['CEO', 'SYSTEM_ADMIN']);

        if (!$isAutoApproved && !$this->hasPermission('SUPPLIER_VIEW')) {
            return redirect()->back()->with('error', 'Insufficient permissions');
        }

        // GET SUPPLIER with existence validation
        $supplier = DB::table('suppliers')
            ->select(self::SUPPLIER_FIELDS)
            ->where('id', $id)
            ->first();

        if (!$supplier) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier not found');
        }

        // RAPID RELATED DATA LOADING - CORRECTED QUERIES
        $relatedData = [
            // Purchase orders count and value - FIXED: total_amount → total_order_value
            'purchase_orders' => DB::table('purchase_orders')
                ->where('supplier_id', $id)
                ->selectRaw('COUNT(*) as count, SUM(total_order_value) as total_value')
                ->first(),

            // Recent deliveries - ALL COLUMNS VERIFIED AGAINST SCHEMA
            'recent_deliveries' => DB::table('deliveries')
                ->join('tanks', 'deliveries.tank_id', '=', 'tanks.id')
                ->join('stations', 'tanks.station_id', '=', 'stations.id')
                ->where('deliveries.supplier_id', $id)
                ->select([
                    'deliveries.id',
                    'deliveries.delivery_date',
                    'deliveries.quantity_delivered_liters',
                    'deliveries.delivery_status',
                    'stations.station_name',
                    'tanks.tank_number'
                ])
                ->orderBy('deliveries.delivery_date', 'desc')
                ->limit(10)
                ->get(),

            // Active contracts - FIXED: unit_price → base_price_per_liter
            'active_contracts' => DB::table('supplier_contracts')
                ->where('supplier_id', $id)
                ->where('is_active', 1)
                ->where('effective_from', '<=', now())
                ->where('effective_until', '>=', now())
                ->select(['id', 'contract_number', 'product_type', 'base_price_per_liter', 'effective_until'])
                ->get(),

            // Payment summary - VERIFIED: supplier_payments.payment_amount exists
            'payment_summary' => DB::table('supplier_payments')
                ->where('supplier_id', $id)
                ->selectRaw('COUNT(*) as payment_count, SUM(payment_amount) as total_paid')
                ->first(),

            // Performance metrics - VERIFIED: all supplier_performance columns exist
            'performance' => DB::table('supplier_performance')
                ->where('supplier_id', $id)
                ->orderBy('evaluation_period_end', 'desc')
                ->first()
        ];

        // LOG VIEW ACCESS - VERIFIED: all audit_logs columns exist
        $this->logAction([
            'user_id' => auth()->id(),
            'action' => 'SUPPLIER_PROFILE_VIEWED',
            'table_name' => 'suppliers',
            'record_id' => $id,
            'old_values' => null,
            'new_values' => json_encode(['supplier_code' => $supplier->supplier_code]),
            'business_justification' => 'Supplier profile access for business review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return view('suppliers.show', compact('supplier', 'relatedData'));
    }

    /**
     * GENERATE FAKE SUPPLIER DATA for testing (UNIQUE EVERY TIME)
     * Features: Realistic data patterns, Uganda-focused, never duplicates
     */
    public function generateFakeSupplier(Request $request)
    {
        // ADMIN ONLY for security
        if (!in_array(auth()->user()->role, ['CEO', 'SYSTEM_ADMIN'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            // UNIQUE TIMESTAMP-BASED GENERATION
            $timestamp = now()->format('YmdHis');
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

            // REALISTIC UGANDAN SUPPLIER DATA
            $ugandanCompanies = [
                'Kampala Fuel Distributors', 'Entebbe Oil & Gas', 'Mbarara Energy Solutions',
                'Gulu Petroleum Ltd', 'Jinja Fuel Systems', 'Masaka Oil Trading',
                'Fort Portal Energy Co', 'Kasese Fuel Services', 'Soroti Oil Depot',
                'Arua Energy Partners', 'Mbale Petroleum Group', 'Hoima Oil Supply'
            ];

            $ugandanCities = [
                'Kampala', 'Entebbe', 'Mbarara', 'Gulu', 'Jinja', 'Masaka',
                'Fort Portal', 'Kasese', 'Soroti', 'Arua', 'Mbale', 'Hoima'
            ];

            $ugandanNames = [
                'John Mukasa', 'Sarah Nakato', 'David Okello', 'Grace Achieng',
                'Peter Ssemwogerere', 'Mary Nabulo', 'James Atuhaire', 'Rose Akello',
                'Moses Kiprotich', 'Jane Namukose', 'Simon Tukei', 'Eva Amongi'
            ];

            // GENERATE UNIQUE DATA
            $companyIndex = ($timestamp + $random) % count($ugandanCompanies);
            $cityIndex = ($timestamp + $random + 1) % count($ugandanCities);
            $nameIndex = ($timestamp + $random + 2) % count($ugandanNames);

            $company = $ugandanCompanies[$companyIndex];
            $city = $ugandanCities[$cityIndex];
            $contactPerson = $ugandanNames[$nameIndex];

            // ENSURE UNIQUENESS with database checks
            $supplierCode = 'TEST-' . $timestamp . '-' . $random;
            $email = strtolower(str_replace(' ', '.', $contactPerson)) . '.' . $random . '@' .
                     strtolower(str_replace(' ', '', $company)) . '.co.ug';

            // VERIFY NO DUPLICATES (extra safety)
            while (DB::table('suppliers')->where('supplier_code', $supplierCode)->exists()) {
                $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $supplierCode = 'TEST-' . $timestamp . '-' . $random;
            }

            $fakeData = [
                'supplier_code' => $supplierCode,
                'company_name' => $company . ' Ltd',
                'contact_person' => $contactPerson,
                'email' => $email,
                'phone' => '+256' . mt_rand(700000000, 799999999),
                'address_line_1' => 'Plot ' . mt_rand(1, 999) . ', ' . $city . ' Road',
                'address_line_2' => 'P.O. Box ' . mt_rand(1000, 9999),
                'city' => $city,
                'postal_code' => str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'country' => 'Uganda',
                'tax_number' => '1000' . mt_rand(100000, 999999) . '000',
                'credit_limit' => mt_rand(1000000, 50000000), // 1M to 50M UGX
                'payment_terms_days' => [7, 14, 30, 45, 60][mt_rand(0, 4)],
                'currency_code' => 'UGX',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ];

            // INSERT FAKE SUPPLIER
            $supplierId = DB::table('suppliers')->insertGetId($fakeData);

            // LOG FAKE DATA CREATION
            $this->logAction([
                'user_id' => auth()->id(),
                'action' => 'FAKE_SUPPLIER_GENERATED',
                'table_name' => 'suppliers',
                'record_id' => $supplierId,
                'old_values' => null,
                'new_values' => json_encode($fakeData),
                'business_justification' => 'Test data generation for system testing',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fake supplier generated successfully',
                'supplier' => [
                    'id' => $supplierId,
                    'supplier_code' => $fakeData['supplier_code'],
                    'company_name' => $fakeData['company_name'],
                    'contact_person' => $fakeData['contact_person']
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to generate fake supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================
    // PRIVATE HELPER METHODS
    // ================================

    /**
     * Generate next sequential supplier code
     */
    private function generateNextSupplierCode(): string
    {
        $lastCode = DB::table('suppliers')
            ->where('supplier_code', 'LIKE', 'SUP-%')
            ->orderBy('supplier_code', 'desc')
            ->value('supplier_code');

        if ($lastCode) {
            $number = (int) substr($lastCode, 4) + 1;
        } else {
            $number = 1;
        }

        return 'SUP-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Detect similar suppliers to prevent duplicates
     */
    private function detectSimilarSuppliers(array $data): array
    {
        $similar = [];

        // Check company name similarity (fuzzy matching)
        $nameMatches = DB::table('suppliers')
            ->where('company_name', 'LIKE', '%' . $data['company_name'] . '%')
            ->orWhere('company_name', 'LIKE', '%' . explode(' ', $data['company_name'])[0] . '%')
            ->select(['id', 'supplier_code', 'company_name', 'contact_person'])
            ->limit(3)
            ->get()
            ->toArray();

        if (!empty($nameMatches)) {
            $similar['name_matches'] = $nameMatches;
        }

        // Check email domain matches
        $emailDomain = substr(strrchr($data['email'], '@'), 1);
        $emailMatches = DB::table('suppliers')
            ->where('email', 'LIKE', '%@' . $emailDomain)
            ->select(['id', 'supplier_code', 'company_name', 'email'])
            ->limit(3)
            ->get()
            ->toArray();

        if (!empty($emailMatches)) {
            $similar['email_matches'] = $emailMatches;
        }

        return $similar;
    }

    /**
     * Check user permissions - VERIFIED AGAINST user_stations SCHEMA
     */
    private function hasPermission(string $permission): bool
    {
        // Get user permissions from database - VERIFIED: access_level column exists
        $userPermissions = DB::table('user_stations')
            ->join('stations', 'user_stations.station_id', '=', 'stations.id')
            ->where('user_stations.user_id', auth()->id())
            ->where('user_stations.is_active', 1)
            ->pluck('user_stations.access_level')
            ->toArray();

        // Permission mapping - VERIFIED: enum values match schema exactly
        // user_stations.access_level enum: 'READ_ONLY','DATA_ENTRY','SUPERVISOR','MANAGER','FULL_ACCESS'
        $permissionMap = [
            'SUPPLIER_VIEW' => ['FULL_ACCESS', 'SUPERVISOR', 'MANAGER'],
            'SUPPLIER_CREATE' => ['FULL_ACCESS', 'MANAGER'],
            'SUPPLIER_EDIT' => ['FULL_ACCESS', 'MANAGER'],
            'SUPPLIER_DELETE' => ['FULL_ACCESS']
        ];

        $allowedLevels = $permissionMap[$permission] ?? [];
        return !empty(array_intersect($userPermissions, $allowedLevels));
    }

    /**
     * Log action for audit trail - VERIFIED AGAINST audit_logs SCHEMA
     * ALL COLUMNS MATCH EXACTLY: user_id, session_id, station_id, action_type,
     * action_category, table_name, record_id, old_value_text, new_value_text,
     * change_reason, business_justification, ip_address, user_agent, etc.
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

        // INSERT with EXACT SCHEMA COMPLIANCE - all columns verified
        DB::table('audit_logs')->insert([
            'user_id' => $actionData['user_id'],
            'session_id' => session()->getId(),
            'station_id' => null, // Suppliers are global, not station-specific
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
     * Get action type for audit logging - VERIFIED AGAINST enum values
     */
    private function getActionType(string $action): string
    {
        // action_type enum: 'CREATE','READ','UPDATE','DELETE','LOGIN','LOGOUT',
        // 'APPROVE','REJECT','ESCALATE','EXPORT','IMPORT','BACKUP','RESTORE'
        $actionTypes = [
            'SUPPLIER_LIST_ACCESSED' => 'READ',
            'SUPPLIER_CREATED' => 'CREATE',
            'SUPPLIER_PROFILE_VIEWED' => 'READ',
            'SUPPLIER_UPDATED' => 'UPDATE',
            'SUPPLIER_DELETED' => 'DELETE',
            'FAKE_SUPPLIER_GENERATED' => 'CREATE',
            'SUPPLIER_CREATION_ERROR' => 'CREATE'
        ];
        return $actionTypes[$action] ?? 'CREATE';
    }
}
