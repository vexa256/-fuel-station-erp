<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PumpController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\EveningReadingController;
use App\Http\Controllers\MorningReadingController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\ContinuousMeterController;


Route::middleware(['auth'])->group(function () {
    Route::prefix('continuous-meter')->name('continuous-meter.')->group(function () {
        Route::get('/', [ContinuousMeterController::class, 'index'])->name('index');
        Route::get('/create', [ContinuousMeterController::class, 'create'])->name('create');
        Route::post('/', [ContinuousMeterController::class, 'store'])->name('store');
        Route::get('/{id}', [ContinuousMeterController::class, 'show'])->name('show');
        Route::post('/reconciliation', [ContinuousMeterController::class, 'reconciliation'])->name('reconciliation');
    });
});


Route::middleware(['auth'])->group(function () {
   Route::prefix('evening-readings')->name('evening.readings.')->group(function () {
       Route::get('/', [EveningReadingController::class, 'index'])->name('index');
       Route::get('/create', [EveningReadingController::class, 'create'])->name('create');
       Route::post('/', [EveningReadingController::class, 'store'])->name('store');
       Route::post('/calculate', [EveningReadingController::class, 'calculateReading'])->name('calculate');



   });
});


Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index'); // Add this line

   Route::get('/layers/{tankId}', [InventoryController::class, 'layers'])->name('layers');
   Route::post('/consume-layers', [InventoryController::class, 'consumeFromLayers'])->name('consume-layers');
   Route::post('/create-layer', [InventoryController::class, 'createLayer'])->name('create-layer');
   Route::get('/movements/{tankId}', [InventoryController::class, 'movements'])->name('movements');
   Route::get('/valuation/{stationId}', [InventoryController::class, 'valuation'])->name('valuation');
   Route::get('/adjustments', [InventoryController::class, 'adjustments'])->name('adjustments');
   Route::get('/batch-consumption/{tankId}', [InventoryController::class, 'batchConsumption'])->name('batch-consumption');
});


Route::middleware(['auth'])->group(function () {
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::get('/create', [DeliveryController::class, 'create'])->name('create');
        Route::post('/', [DeliveryController::class, 'store'])->name('store');
        Route::get('/{id}', [DeliveryController::class, 'show'])->name('show');
        Route::patch('/{id}/approve', [DeliveryController::class, 'approve'])->name('approve');
        Route::get('/{id}/receipt', [DeliveryController::class, 'receipt'])->name('receipt');
    });
});


Route::middleware(['auth'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/daily-reconciliation', [ReportsController::class, 'dailyReconciliation'])->name('daily-reconciliation');
        Route::get('/variance-analysis', [ReportsController::class, 'varianceAnalysis'])->name('variance-analysis');
        Route::get('/inventory-valuation', [ReportsController::class, 'inventoryValuation'])->name('inventory-valuation');
        Route::get('/sales-meter-reconciliation', [ReportsController::class, 'salesMeterReconciliation'])->name('sales-meter-reconciliation');
        Route::get('/delivery-tracking', [ReportsController::class, 'deliveryTracking'])->name('delivery-tracking');
        Route::get('/financial-performance', [ReportsController::class, 'financialPerformance'])->name('financial-performance');
        Route::get('/compliance-audit', [ReportsController::class, 'complianceAudit'])->name('compliance-audit');
        Route::get('/operational-efficiency', [ReportsController::class, 'operationalEfficiency'])->name('operational-efficiency');
        Route::get('/exceptions-alerts', [ReportsController::class, 'exceptionsAlerts'])->name('exceptions-alerts');
        Route::get('/executive-summary', [ReportsController::class, 'executiveSummary'])->name('executive-summary');
    });
});


Route::middleware(['auth'])->group(function () {
    Route::resource('purchase-orders', PurchaseOrderController::class)->only([
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
    ]);

    Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
});;

Route::prefix('contracts')->name('contracts.')->group(function () {
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::post('/', [ContractController::class, 'store'])->name('store');
    Route::get('/{id}', [ContractController::class, 'show'])->name('show');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::post('/suppliers/generate-fake', [SupplierController::class, 'generateFakeSupplier'])->name('suppliers.generate-fake');
});


// Web Routes
Route::middleware(['auth'])->group(function () {


// Daily Reconciliation Routes
Route::get('/reconciliation/daily/{stationId?}/{date?}', [ReconciliationController::class, 'daily'])
   ->name('reconciliation.daily');

Route::post('/reconciliation/execute', [ReconciliationController::class, 'execute'])
   ->name('reconciliation.execute');

// Reconciliation Management Routes
Route::get('/reconciliation', [ReconciliationController::class, 'index'])
   ->name('reconciliation.index');

Route::get('/reconciliation/{id}', [ReconciliationController::class, 'show'])
   ->name('reconciliation.show');

Route::post('/reconciliation/{id}/approve', [ReconciliationController::class, 'approve'])
   ->name('reconciliation.approve');

// Variance Investigation Routes
Route::get('/reconciliation/variance/{id}/investigate', [ReconciliationController::class, 'investigate'])
   ->name('reconciliation.investigate');




    // Route::get('/api/tank-historical-data/{tankId}', [MorningReadingController::class, 'getHistoricalData']);

//    Route::get('/continuous-meter', [ContinuousMeterController::class, 'index'])->name('continuous-meter.index');
//    Route::get('/continuous-meter/create', [ContinuousMeterController::class, 'create'])->name('continuous-meter.create');
//    Route::post('/continuous-meter', [ContinuousMeterController::class, 'store'])->name('continuous-meter.store');
//    Route::get('/continuous-meter/{id}', [ContinuousMeterController::class, 'show'])->name('continuous-meter.show');
//    Route::get('/continuous-meter/reconciliation', [ContinuousMeterController::class, 'reconciliation'])->name('continuous-meter.reconciliation');
});





Route::middleware(['auth'])->group(function () {


    // Add to existing morning readings routes:
Route::get('/api/tank-calibration', [MorningReadingController::class, 'getTankCalibration'])
    ->name('api.tank.calibration');

Route::get('/api/validate-reading', [MorningReadingController::class, 'validateReading'])
    ->name('api.validate.reading');

    // MORNING READINGS DASHBOARD - 6:00 AM - 9:00 AM
    Route::get('/morning/readings', [MorningReadingController::class, 'index'])
        ->name('morning.readings.index')
     ;

    // MORNING READING CREATE FORM - 6:00 AM - 9:00 AM
    Route::get('/morning/readings/create', [MorningReadingController::class, 'create'])
        ->name('morning.readings.create')
     ;

    // MORNING READING STORE - 6:00 AM - 9:00 AM
    Route::post('/morning/readings', [MorningReadingController::class, 'store'])
        ->name('morning.readings.store')
     ;

    // MORNING READING EDIT FORM - CEO/SYSTEM_ADMIN ONLY
    Route::get('/morning/readings/{id}/edit', [MorningReadingController::class, 'edit'])
        ->name('morning.readings.edit')
    ;

    // MORNING READING UPDATE - CEO/SYSTEM_ADMIN ONLY
    Route::put('/morning/readings/{id}', [MorningReadingController::class, 'update'])
        ->name('morning.readings.update')
      ;

    // TIME WINDOW VALIDATION ENDPOINT
    Route::post('/morning/validate-time', [MorningReadingController::class, 'validateTimeWindow'])
        ->name('morning.readings.validate-time');

});






// Pump Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/pumps', [PumpController::class, 'selectStation'])->name('pumps.select');
    Route::get('/pumps/station/{station}', [PumpController::class, 'index'])->name('pumps.index');
    Route::get('/pumps/station/{station}/create', [PumpController::class, 'create'])->name('pumps.create');
    Route::post('/pumps/station/{station}', [PumpController::class, 'store'])->name('pumps.store');
    Route::get('/pumps/{id}/edit', [PumpController::class, 'edit'])->name('pumps.edit');
    Route::post('/pumps/{id}', [PumpController::class, 'update'])->name('pumps.update');
    Route::get('/pumps/{id}/maintenance', [PumpController::class, 'maintenance'])->name('pumps.maintenance');
});



Route::middleware('auth')->group(function () {
    Route::get('/tanks', [TankController::class, 'selectStation'])->name('tanks.select');
    Route::get('/stations/{station}/tanks', [TankController::class, 'index'])->name('tanks.index');
    Route::get('/stations/{station}/tanks/create', [TankController::class, 'create'])->name('tanks.create');
    Route::post('/stations/{station}/tanks', [TankController::class, 'store'])->name('tanks.store');
    Route::get('/tanks/{id}/edit', [TankController::class, 'edit'])->name('tanks.edit');
    Route::put('/tanks/{id}', [TankController::class, 'update'])->name('tanks.update');
    Route::get('/tanks/{id}/calibration', [TankController::class, 'calibration'])->name('tanks.calibration');
    Route::post('/tanks/{id}/calibration', [TankController::class, 'storeCalibration'])->name('tanks.calibration.store');
    Route::get('/tanks/{id}/layers', [TankController::class, 'layers'])->name('tanks.layers');
});



Route::middleware(['auth'])->group(function () {

    // Station Listing & Overview
    Route::get('/stations', [StationController::class, 'index'])->name('stations.index');


    Route::get('/stations/create', [StationController::class, 'create'])->name('stations.create');
    Route::post('/stations', [StationController::class, 'store'])->name('stations.store');


    // Station Operations (Station-scoped access)
    Route::get('/stations/{id}', [StationController::class, 'dashboard'])->name('stations.dashboard');
    Route::get('/stations/{id}/edit', [StationController::class, 'edit'])->name('stations.edit');
    Route::put('/stations/{id}', [StationController::class, 'update'])->name('stations.update');

    // API Endpoints
    Route::get('/api/stations/{id}/status', [StationController::class, 'status'])->name('stations.status');
});

// // User Management Routes
// Route::middleware(['auth'])->group(function () {

//     // User listing - all authenticated users can view (scoped by permissions)
//     Route::get('/users', [UsersController::class, 'index'])->name('users.index');

//     // User creation - CEO/SYSTEM_ADMIN only (enforced in controller)
//     Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
//     Route::post('/users', [UsersController::class, 'store'])->name('users.store');

//     // User editing - CEO/SYSTEM_ADMIN or self-edit (enforced in controller)
//     Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
//     Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');

//     // Station assignment - CEO/SYSTEM_ADMIN only (enforced in controller)
//     Route::get('/users/{id}/stations', [UsersController::class, 'edit'])->name('users.stations');
//     Route::post('/users/{id}/stations', [UsersController::class, 'assignStations'])->name('users.assign-stations');

//     // User permissions management - CEO/SYSTEM_ADMIN only (enforced in controller)
//     Route::get('/users/{id}/permissions', [UsersController::class, 'permissions'])->name('users.permissions');

//     // User activity audit trail - CEO/SYSTEM_ADMIN or self-view (enforced in controller)
//     Route::get('/users/{id}/activity', [UsersController::class, 'activity'])->name('users.activity');
// });




// User Management Routes
Route::middleware(['auth'])->group(function () {

    // User listing - all authenticated users can view (scoped by permissions)
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/dashboard', [UsersController::class, 'index'])->name('dashboard');
    Route::get('/', [UsersController::class, 'index'])->name('homenow');

    // User creation - CEO/SYSTEM_ADMIN only (enforced in controller)
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');

    // User editing - CEO/SYSTEM_ADMIN or self-edit (enforced in controller)
    Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');

    // Station assignment - CEO/SYSTEM_ADMIN only (enforced in controller)
    Route::get('/users/{id}/stations', [UsersController::class, 'stations'])->name('users.stations');
    Route::post('/users/{id}/stations', [UsersController::class, 'assignStations'])->name('users.assignStations');

    // User permissions management - CEO/SYSTEM_ADMIN only (enforced in controller)
    Route::get('/users/{id}/permissions', [UsersController::class, 'permissions'])->name('users.permissions');

    // User activity audit trail - CEO/SYSTEM_ADMIN or self-view (enforced in controller)
    Route::get('/users/{id}/activity', [UsersController::class, 'activity'])->name('users.activity');
});





















// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('users.index');
    });

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

//  Now use standard Laravel auth middleware!
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    // Role-based routes using Auth::user()
    // Route::middleware('can:manage-stations')->group(function () {
    //     Route::get('/readings', function () {
    //         return view('readings.index');
    //     })->name('readings.index');
    // });
});


// Add this to your authenticated routes section
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //  Add missing profile routes
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');

    Route::patch('/profile', function () {
        return redirect()->route('profile.edit')->with('status', 'Profile updated!');
    })->name('profile.update');

    Route::delete('/profile', function () {
        return redirect()->route('login')->with('status', 'Account deleted!');
    })->name('profile.destroy');
});
