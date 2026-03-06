<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BoardingHousePolicyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BoardingHouseApplicationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SuperDuperAdmin\BoardingHouseController as SuperDuperAdminBoardingHouseController;
use App\Http\Controllers\SuperDuperAdmin\DashboardController as SuperDuperAdminDashboardController;
use App\Http\Controllers\Map\BoardingHouseMapController;
use App\Http\Controllers\User\BoardingHouseBrowseController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\InquiryController;
use App\Http\Controllers\User\ReservationController;
use App\Models\BoardingHouse;
use App\Models\Room;
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

    // SuperDuperAdmin area
    Route::prefix('superduperadmin')->name('superduperadmin.')->middleware('superduperadmin')->group(function () {
        Route::get('/dashboard', [SuperDuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/boarding-houses', [SuperDuperAdminBoardingHouseController::class, 'store'])->name('boarding-houses.store');
        Route::get('/boarding-houses/{boardingHouse}/edit', [SuperDuperAdminBoardingHouseController::class, 'edit'])->name('boarding-houses.edit');
        Route::put('/boarding-houses/{boardingHouse}', [SuperDuperAdminBoardingHouseController::class, 'update'])->name('boarding-houses.update');
        Route::delete('/boarding-houses/{boardingHouse}', [SuperDuperAdminBoardingHouseController::class, 'destroy'])->name('boarding-houses.destroy');
        Route::post('/boarding-houses/{boardingHouse}/approve', [SuperDuperAdminBoardingHouseController::class, 'approve'])->name('boarding-houses.approve');
        Route::post('/boarding-houses/{boardingHouse}/reject', [SuperDuperAdminBoardingHouseController::class, 'reject'])->name('boarding-houses.reject');
    });

    Route::prefix('map')->name('map.')->group(function () {
        Route::get('/admin/boarding-houses', [BoardingHouseMapController::class, 'admin'])
            ->middleware('admin')
            ->name('admin.boarding-houses');
        Route::get('/user/boarding-houses', [BoardingHouseMapController::class, 'user'])
            ->name('user.boarding-houses');
    });

    // Admin-only area
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/tenant-history', [AdminController::class, 'tenantHistory'])->name('tenant-history');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::put('/users/{user}/archive', [AdminController::class, 'archiveUser'])->name('users.archive');
        Route::put('/users/{user}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::resource('/boarding-houses', \App\Http\Controllers\BoardingHouseController::class)->names('boarding-houses');
        Route::resource('/rooms', RoomController::class)->only(['store', 'update', 'destroy'])->names('rooms');
        Route::get('/boarding-house-policies', [BoardingHousePolicyController::class, 'index'])->name('boarding-house-policies.index');
        Route::post('/boarding-house-policies', [BoardingHousePolicyController::class, 'update'])->name('boarding-house-policies.update');

        Route::get('/boarding-house-applications', [BoardingHouseApplicationController::class, 'index'])->name('applications.index');
        Route::post('/boarding-house-applications/{application}/approve', [BoardingHouseApplicationController::class, 'approve'])->name('applications.approve');
        Route::post('/boarding-house-applications/{application}/reject', [BoardingHouseApplicationController::class, 'reject'])->name('applications.reject');
    });

    // Owner Dashboard (role-gated)
    Route::get('/owner/dashboard', [DashboardController::class, 'owner'])->name('owner.dashboard');
    Route::get('/owner/maintenance', [DashboardController::class, 'ownerMaintenance'])->name('owner.maintenance');
    Route::get('/owner/rooms', function () {
        $user = Auth::user();
        abort_unless($user && $user->isOwner(), 403);
        $rooms = Room::with('boardingHouse')->orderByDesc('created_at')->get();
        $boardingHouses = BoardingHouse::orderBy('name')->get();
        return view('owner.rooms', compact('rooms', 'boardingHouses'));
    })->name('owner.rooms');
    Route::get('/owner/boarding-houses', function () {
        $user = Auth::user();
        abort_unless($user && $user->isOwner(), 403);
        $houses = BoardingHouse::orderByDesc('created_at')->get();
        return view('owner.boarding-houses', compact('houses'));
    })->name('owner.boarding-houses');

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

    Route::prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/boarding-houses', [BoardingHouseBrowseController::class, 'index'])->name('boarding-houses');
        Route::get('/boarding-houses/compare', [BoardingHouseBrowseController::class, 'compare'])->name('boarding-houses.compare');
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/boarding-houses', [BoardingHouseBrowseController::class, 'index'])->name('boarding-houses.index');
        Route::get('/boarding-houses/compare', [BoardingHouseBrowseController::class, 'compare'])->name('boarding-houses.compare');
        Route::get('/boarding-houses/{boardingHouse}', [BoardingHouseBrowseController::class, 'show'])->name('boarding-houses.show');

        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{boardingHouse}', [FavoriteController::class, 'store'])->middleware('throttle:30,1')->name('favorites.store');
        Route::delete('/favorites/{boardingHouse}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::post('/boarding-houses/{boardingHouse}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');
        Route::post('/boarding-houses/{boardingHouse}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('reservations.store');
    });

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
        Route::post('/bookings/{id}/processing', [\App\Http\Controllers\CaretakerController::class, 'bookingProcess'])->name('bookings.process');
        Route::post('/bookings/{id}/confirm', [\App\Http\Controllers\CaretakerController::class, 'bookingConfirm'])->name('bookings.confirm');
        Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\CaretakerController::class, 'bookingCancel'])->name('bookings.cancel');
        Route::post('/bookings/{id}/extend', [\App\Http\Controllers\CaretakerController::class, 'bookingExtend'])->name('bookings.extend');
        Route::get('/bookings/availability', [\App\Http\Controllers\CaretakerController::class, 'bookingAvailability'])->name('bookings.availability');

        Route::get('/rooms', [\App\Http\Controllers\CaretakerController::class, 'rooms'])->name('rooms.index');
        Route::get('/rooms/availability', [\App\Http\Controllers\CaretakerController::class, 'roomsAvailability'])->name('rooms.availability');
        Route::post('/rooms/{id}/status', [\App\Http\Controllers\CaretakerController::class, 'roomStatus'])->name('rooms.status');
        Route::post('/rooms/{id}', [\App\Http\Controllers\CaretakerController::class, 'roomUpdate'])->name('rooms.update');

        Route::get('/maintenance', [\App\Http\Controllers\CaretakerController::class, 'maintenance'])->name('maintenance.index');
        Route::get('/maintenance/{id}', [\App\Http\Controllers\CaretakerController::class, 'maintenanceShow'])->name('maintenance.show');
        Route::post('/maintenance/{id}/update', [\App\Http\Controllers\CaretakerController::class, 'maintenanceUpdate'])->name('maintenance.update');
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
