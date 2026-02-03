<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BoardingHousePolicyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BoardingHouseApplicationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->get('/auth', function () {
    // Send guests straight to the standard Breeze login screen to match
    // the framework's default authentication flow.
    return redirect()->route('login');
})->name('auth.choice');

// Standard Breeze Auth Routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard: Redirects to the Admin Dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();

        $routeName = $user?->dashboardRouteName() ?? 'admin.dashboard';

        return redirect()->route($routeName);
    })->name('dashboard');

    // Admin-only area
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/tenant-history', [AdminController::class, 'tenantHistory'])->name('tenant-history');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::resource('/boarding-houses', \App\Http\Controllers\BoardingHouseController::class)->names('boarding-houses');
        Route::get('/boarding-house-policies', [BoardingHousePolicyController::class, 'index'])->name('boarding-house-policies.index');
        Route::post('/boarding-house-policies', [BoardingHousePolicyController::class, 'update'])->name('boarding-house-policies.update');

        Route::get('/boarding-house-applications', [BoardingHouseApplicationController::class, 'index'])->name('applications.index');
        Route::post('/boarding-house-applications/{application}/approve', [BoardingHouseApplicationController::class, 'approve'])->name('applications.approve');
        Route::post('/boarding-house-applications/{application}/reject', [BoardingHouseApplicationController::class, 'reject'])->name('applications.reject');
    });

    // Owner Dashboard (role-gated)
    Route::get('/owner/dashboard', function () {
        $user = Auth::user();
        abort_unless($user && $user->isOwner(), 403);
        return view('owner.dashboard');
    })->name('owner.dashboard');

    // Tenant Dashboard (role-gated)
    Route::get('/tenant/dashboard', function () {
        $user = Auth::user();
        abort_unless($user && $user->isTenant(), 403);
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    Route::get('/tenant/bh-policies', function () {
        $user = Auth::user();
        abort_unless($user && $user->isTenant(), 403);

        $policyCategories = Lang::get('boarding_house_policies.categories', []);

        return view('tenant.bh-policies', compact('policyCategories'));
    })->name('tenant.bh-policies');

    Route::get('/tenant/boarding-houses', function () {
        $user = Auth::user();
        abort_unless($user && $user->isTenant(), 403);
        $availableHouses = \App\Models\BoardingHouse::withCount('tenants')->get()->filter(fn($h) => $h->tenants_count < $h->capacity);
        return view('tenant.boarding-houses', compact('availableHouses'));
    })->name('tenant.boarding-houses');

    Route::post('/boarding-houses/{boarding_house}/apply', [BoardingHouseApplicationController::class, 'store'])->name('tenant.boarding-houses.apply');
    Route::post('/boarding-houses/apply', [BoardingHouseApplicationController::class, 'storeFromSelect'])->name('tenant.boarding-houses.apply.select');

    // Caretaker Dashboard (role-gated)
    Route::prefix('caretaker')->name('caretaker.')->middleware('caretaker')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\CaretakerController::class, 'dashboard'])->name('dashboard');
        Route::get('/tenants', [\App\Http\Controllers\CaretakerController::class, 'tenants'])->name('tenants.index');
        Route::get('/tenants/{id}', [\App\Http\Controllers\CaretakerController::class, 'tenantShow'])->name('tenants.show');
        Route::post('/tenants/{id}/checkin', [\App\Http\Controllers\CaretakerController::class, 'tenantCheckin'])->name('tenants.checkin');
        Route::post('/tenants/{id}/checkout', [\App\Http\Controllers\CaretakerController::class, 'tenantCheckout'])->name('tenants.checkout');
        Route::post('/tenants/{id}', [\App\Http\Controllers\CaretakerController::class, 'tenantUpdate'])->name('tenants.update');

        Route::get('/bookings', [\App\Http\Controllers\CaretakerController::class, 'bookings'])->name('bookings.index');
        Route::get('/bookings/{id}', [\App\Http\Controllers\CaretakerController::class, 'bookingShow'])->name('bookings.show');
        Route::post('/bookings/{id}/confirm', [\App\Http\Controllers\CaretakerController::class, 'bookingConfirm'])->name('bookings.confirm');
        Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\CaretakerController::class, 'bookingCancel'])->name('bookings.cancel');
        Route::post('/bookings/{id}/extend', [\App\Http\Controllers\CaretakerController::class, 'bookingExtend'])->name('bookings.extend');

        Route::get('/rooms', [\App\Http\Controllers\CaretakerController::class, 'rooms'])->name('rooms.index');
        Route::post('/rooms/{id}/status', [\App\Http\Controllers\CaretakerController::class, 'roomStatus'])->name('rooms.status');
        Route::post('/rooms/{id}', [\App\Http\Controllers\CaretakerController::class, 'roomUpdate'])->name('rooms.update');

        Route::get('/maintenance', [\App\Http\Controllers\CaretakerController::class, 'maintenance'])->name('maintenance.index');
        Route::post('/maintenance/{id}/priority', [\App\Http\Controllers\CaretakerController::class, 'maintenancePriority'])->name('maintenance.priority');
        Route::post('/maintenance/{id}/resolve', [\App\Http\Controllers\CaretakerController::class, 'maintenanceResolve'])->name('maintenance.resolve');

        Route::get('/incidents', [\App\Http\Controllers\CaretakerController::class, 'incidents'])->name('incidents.index');
        Route::get('/incidents/{id}', [\App\Http\Controllers\CaretakerController::class, 'incidentShow'])->name('incidents.show');
        Route::post('/incidents/{id}/update', [\App\Http\Controllers\CaretakerController::class, 'incidentUpdate'])->name('incidents.update');
        Route::post('/incidents/{id}/resolve', [\App\Http\Controllers\CaretakerController::class, 'incidentResolve'])->name('incidents.resolve');

        Route::get('/notices', [\App\Http\Controllers\CaretakerController::class, 'notices'])->name('notices.index');
        Route::post('/notices', [\App\Http\Controllers\CaretakerController::class, 'noticesStore'])->name('notices.store');

        Route::get('/reports', [\App\Http\Controllers\CaretakerController::class, 'reports'])->name('reports.index');
        Route::post('/reports/generate', [\App\Http\Controllers\CaretakerController::class, 'reportsGenerate'])->name('reports.generate');

        Route::get('/settings', [\App\Http\Controllers\CaretakerController::class, 'settings'])->name('settings');
    });

    // OSAS Dashboard (role-gated)
    Route::prefix('osas')->name('osas.')->middleware('osas')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\OsasController::class, 'dashboard'])->name('dashboard');
        Route::get('/validators', [\App\Http\Controllers\OsasController::class, 'validators'])->name('validators.index');
        Route::post('/validators/{id}/toggle', [\App\Http\Controllers\OsasController::class, 'validatorToggle'])->name('validators.toggle');
        Route::get('/validators/{id}', [\App\Http\Controllers\OsasController::class, 'validatorShow'])->name('validators.show');
        Route::post('/validators/{id}/assign-task', [\App\Http\Controllers\OsasController::class, 'assignTask'])->name('validators.assign');

        Route::get('/workbench', [\App\Http\Controllers\OsasController::class, 'workbench'])->name('workbench');
        Route::get('/validations/{id}', [\App\Http\Controllers\OsasController::class, 'validationShow'])->name('validations.show');
        Route::post('/validations/{id}/start', [\App\Http\Controllers\OsasController::class, 'validationStart'])->name('validations.start');
        Route::post('/validations/{id}/save', [\App\Http\Controllers\OsasController::class, 'validationSave'])->name('validations.save');
        Route::post('/validations/{id}/submit', [\App\Http\Controllers\OsasController::class, 'validationSubmit'])->name('validations.submit');
        Route::post('/validations/{id}/evidence', [\App\Http\Controllers\OsasController::class, 'validationEvidence'])->name('validations.evidence');
        Route::post('/validations/{id}/finding', [\App\Http\Controllers\OsasController::class, 'validationFinding'])->name('validations.finding');

        Route::get('/accreditation', [\App\Http\Controllers\OsasController::class, 'accreditation'])->name('accreditation');
        Route::post('/accreditation/{id}/approve', [\App\Http\Controllers\OsasController::class, 'accreditApprove'])->name('accreditation.approve');
        Route::post('/accreditation/{id}/suspend', [\App\Http\Controllers\OsasController::class, 'accreditSuspend'])->name('accreditation.suspend');
        Route::post('/accreditation/{id}/reinstate', [\App\Http\Controllers\OsasController::class, 'accreditReinstate'])->name('accreditation.reinstate');

        Route::get('/reports', [\App\Http\Controllers\OsasController::class, 'reports'])->name('reports');
        Route::post('/reports/export', [\App\Http\Controllers\OsasController::class, 'reportsExport'])->name('reports.export');

        Route::get('/settings', [\App\Http\Controllers\OsasController::class, 'settings'])->name('settings');
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin CRUD Resource (Single definition)
    Route::resource('admins', AdminController::class);
});

require __DIR__.'/auth.php';
