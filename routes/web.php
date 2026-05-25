<?php

use App\Http\Controllers\Admin\BoardingHousePolicyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BoardingHouseApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Map\BoardingHouseMapController;
use App\Http\Controllers\Owner\OwnerBoardingHouseController;
use App\Http\Controllers\Owner\OwnerBookingController;
use App\Http\Controllers\Owner\OwnerComplianceController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerFeedbackController;
use App\Http\Controllers\Owner\OwnerInquiryController;
use App\Http\Controllers\Owner\OwnerRoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperDuperAdmin\BoardingHouseController as SuperDuperAdminBoardingHouseController;
use App\Http\Controllers\SuperDuperAdmin\DashboardController as SuperDuperAdminDashboardController;
use App\Http\Controllers\Tenant\TenantPageController;
use App\Http\Controllers\User\BoardingHouseBrowseController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\InquiryController;
use App\Http\Controllers\User\ReservationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        Route::get('/map', [SuperDuperAdminDashboardController::class, 'map'])->name('map');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/rooms', [SuperDuperAdminDashboardController::class, 'rooms'])->name('rooms');
        Route::get('/inquiries', [SuperDuperAdminDashboardController::class, 'inquiries'])->name('inquiries');
        Route::get('/messages', [SuperDuperAdminDashboardController::class, 'messages'])->name('messages');
        Route::get('/compliance', [SuperDuperAdminDashboardController::class, 'compliance'])->name('compliance');
        Route::get('/reviews', [SuperDuperAdminDashboardController::class, 'reviews'])->name('reviews');
        Route::get('/reports', [SuperDuperAdminDashboardController::class, 'reports'])->name('reports');
        Route::get('/settings', [SuperDuperAdminDashboardController::class, 'settings'])->name('settings');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::put('/users/{user}/archive', [AdminController::class, 'archiveUser'])->name('users.archive');
        Route::put('/users/{user}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/boarding-houses', [SuperDuperAdminDashboardController::class, 'table'])->name('boarding-houses.index');
        Route::get('/boarding-houses/create', [SuperDuperAdminDashboardController::class, 'create'])->name('boarding-houses.create');
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

    // Admin area (Owner accounts are now presented as Admin accounts)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/listings', [OwnerBoardingHouseController::class, 'index'])->name('listings');
        Route::get('/listings/create', [OwnerBoardingHouseController::class, 'create'])->name('listings.create');
        Route::post('/listings', [OwnerBoardingHouseController::class, 'store'])->name('listings.store');
        Route::get('/listings/{boardingHouse}/edit', [OwnerBoardingHouseController::class, 'edit'])->name('listings.edit');
        Route::put('/listings/{boardingHouse}', [OwnerBoardingHouseController::class, 'update'])->name('listings.update');
        Route::post('/listings/{boardingHouse}/submit', [OwnerBoardingHouseController::class, 'submit'])->name('listings.submit');
        Route::delete('/listings/{boardingHouse}', [OwnerBoardingHouseController::class, 'destroy'])->name('listings.destroy');

        Route::get('/boarding-houses', [OwnerBoardingHouseController::class, 'index'])->name('boarding-houses.index');
        Route::get('/boarding-houses/create', [OwnerBoardingHouseController::class, 'create'])->name('boarding-houses.create');
        Route::post('/boarding-houses', [OwnerBoardingHouseController::class, 'store'])->name('boarding-houses.store');
        Route::get('/boarding-houses/{boardingHouse}/edit', [OwnerBoardingHouseController::class, 'edit'])->name('boarding-houses.edit');
        Route::put('/boarding-houses/{boardingHouse}', [OwnerBoardingHouseController::class, 'update'])->name('boarding-houses.update');
        Route::post('/boarding-houses/{boardingHouse}/submit', [OwnerBoardingHouseController::class, 'submit'])->name('boarding-houses.submit');
        Route::delete('/boarding-houses/{boardingHouse}', [OwnerBoardingHouseController::class, 'destroy'])->name('boarding-houses.destroy');

        Route::get('/rooms', [OwnerRoomController::class, 'index'])->name('rooms');
        Route::post('/rooms', [OwnerRoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [OwnerRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [OwnerRoomController::class, 'update'])->name('rooms.update');
        Route::patch('/rooms/{room}/assign', [OwnerRoomController::class, 'assignTenant'])->name('rooms.assign');
        Route::delete('/rooms/{room}', [OwnerRoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/inquiries', [OwnerInquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [OwnerInquiryController::class, 'update'])->name('inquiries.update');
        Route::post('/inquiries/{inquiry}/reservation', [OwnerInquiryController::class, 'storeReservation'])->name('inquiries.reservation');
        Route::delete('/inquiries/{inquiry}', [OwnerInquiryController::class, 'destroy'])->name('inquiries.destroy');
        Route::get('/messages', [OwnerDashboardController::class, 'messages'])->name('messages');

        Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/reservations/{reservation}', [OwnerBookingController::class, 'updateReservation'])->name('reservations.update');
        Route::patch('/bookings/{booking}', [OwnerBookingController::class, 'updateBooking'])->name('bookings.update');

        Route::get('/reviews', [OwnerFeedbackController::class, 'index'])->name('reviews');
        Route::get('/feedback', [OwnerFeedbackController::class, 'index'])->name('feedback.index');
        Route::patch('/feedback/reviews/{review}', [OwnerFeedbackController::class, 'updateReview'])->name('feedback.reviews.update');
        Route::patch('/feedback/incidents/{incident}', [OwnerFeedbackController::class, 'updateIncident'])->name('feedback.incidents.update');
        Route::get('/compliance', [OwnerComplianceController::class, 'index'])->name('compliance.index');
        Route::post('/compliance/documents', [OwnerComplianceController::class, 'store'])->name('compliance.documents.store');
        Route::put('/compliance/documents/{requirement}', [OwnerComplianceController::class, 'update'])->name('compliance.documents.update');
        Route::delete('/compliance/documents/{requirement}', [OwnerComplianceController::class, 'destroy'])->name('compliance.documents.destroy');
        Route::get('/compliance/documents/{requirement}/download', [OwnerComplianceController::class, 'download'])->name('compliance.documents.download');
        Route::post('/compliance/submit', [OwnerComplianceController::class, 'submitAll'])->name('compliance.submit');
        Route::get('/reports', [OwnerDashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [OwnerDashboardController::class, 'exportReports'])->name('reports.export');
        Route::get('/settings', [OwnerDashboardController::class, 'settings'])->name('settings');
        Route::patch('/settings', [OwnerDashboardController::class, 'updateSettings'])->name('settings.update');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/tenant-history', [AdminController::class, 'tenantHistory'])->name('tenant-history');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::put('/users/{user}/archive', [AdminController::class, 'archiveUser'])->name('users.archive');
        Route::put('/users/{user}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/boarding-house-policies', [BoardingHousePolicyController::class, 'index'])->name('boarding-house-policies.index');
        Route::post('/boarding-house-policies', [BoardingHousePolicyController::class, 'update'])->name('boarding-house-policies.update');

        Route::get('/boarding-house-applications', [BoardingHouseApplicationController::class, 'index'])->name('applications.index');
        Route::post('/boarding-house-applications/{application}/approve', [BoardingHouseApplicationController::class, 'approve'])->name('applications.approve');
        Route::post('/boarding-house-applications/{application}/reject', [BoardingHouseApplicationController::class, 'reject'])->name('applications.reject');
    });

    // Owner area
    Route::prefix('owner')->name('owner.')->middleware('owner')->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/maintenance', [DashboardController::class, 'ownerMaintenance'])->name('maintenance');
        Route::patch('/maintenance/{maintenanceRequest}', [DashboardController::class, 'updateOwnerMaintenance'])->name('maintenance.update');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/archive', [AdminController::class, 'archiveUser'])->name('users.archive');
        Route::put('/users/{user}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/tenant-history', [AdminController::class, 'tenantHistory'])->name('tenant-history');

        Route::get('/boarding-houses', [OwnerBoardingHouseController::class, 'index'])->name('boarding-houses');
        Route::get('/boarding-houses/create', [OwnerBoardingHouseController::class, 'create'])->name('boarding-houses.create');
        Route::post('/boarding-houses', [OwnerBoardingHouseController::class, 'store'])->name('boarding-houses.store');
        Route::get('/boarding-houses/{boardingHouse}/edit', [OwnerBoardingHouseController::class, 'edit'])->name('boarding-houses.edit');
        Route::put('/boarding-houses/{boardingHouse}', [OwnerBoardingHouseController::class, 'update'])->name('boarding-houses.update');
        Route::post('/boarding-houses/{boardingHouse}/submit', [OwnerBoardingHouseController::class, 'submit'])->name('boarding-houses.submit');
        Route::delete('/boarding-houses/{boardingHouse}', [OwnerBoardingHouseController::class, 'destroy'])->name('boarding-houses.destroy');

        Route::get('/rooms', [OwnerRoomController::class, 'index'])->name('rooms');
        Route::post('/rooms', [OwnerRoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [OwnerRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [OwnerRoomController::class, 'update'])->name('rooms.update');
        Route::patch('/rooms/{room}/assign', [OwnerRoomController::class, 'assignTenant'])->name('rooms.assign');
        Route::delete('/rooms/{room}', [OwnerRoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/inquiries', [OwnerInquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [OwnerInquiryController::class, 'update'])->name('inquiries.update');
        Route::post('/inquiries/{inquiry}/reservation', [OwnerInquiryController::class, 'storeReservation'])->name('inquiries.reservation');
        Route::delete('/inquiries/{inquiry}', [OwnerInquiryController::class, 'destroy'])->name('inquiries.destroy');
        Route::get('/messages', [OwnerDashboardController::class, 'messages'])->name('messages');

        Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/reservations/{reservation}', [OwnerBookingController::class, 'updateReservation'])->name('reservations.update');
        Route::patch('/bookings/{booking}', [OwnerBookingController::class, 'updateBooking'])->name('bookings.update');

        Route::get('/feedback', [OwnerFeedbackController::class, 'index'])->name('feedback.index');
        Route::patch('/feedback/reviews/{review}', [OwnerFeedbackController::class, 'updateReview'])->name('feedback.reviews.update');
        Route::patch('/feedback/incidents/{incident}', [OwnerFeedbackController::class, 'updateIncident'])->name('feedback.incidents.update');
        Route::get('/compliance', [OwnerComplianceController::class, 'index'])->name('compliance.index');
        Route::post('/compliance/documents', [OwnerComplianceController::class, 'store'])->name('compliance.documents.store');
        Route::put('/compliance/documents/{requirement}', [OwnerComplianceController::class, 'update'])->name('compliance.documents.update');
        Route::delete('/compliance/documents/{requirement}', [OwnerComplianceController::class, 'destroy'])->name('compliance.documents.destroy');
        Route::get('/compliance/documents/{requirement}/download', [OwnerComplianceController::class, 'download'])->name('compliance.documents.download');
        Route::post('/compliance/submit', [OwnerComplianceController::class, 'submitAll'])->name('compliance.submit');
        Route::get('/reports', [OwnerDashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [OwnerDashboardController::class, 'exportReports'])->name('reports.export');
        Route::get('/settings', [OwnerDashboardController::class, 'settings'])->name('settings');
        Route::patch('/settings', [OwnerDashboardController::class, 'updateSettings'])->name('settings.update');
    });

    // User Dashboard (legacy tenant routes remain as compatibility aliases)
    Route::get('/user/dashboard', [DashboardController::class, 'tenant'])->name('user.dashboard');

    // Tenant Dashboard (legacy role-gated URL)
    Route::get('/tenant/dashboard', [DashboardController::class, 'tenant'])->name('tenant.dashboard');

    Route::get('/tenant/bh-policies', [TenantPageController::class, 'bhPolicies'])->name('tenant.bh-policies');

    Route::prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/boarding-houses', [BoardingHouseBrowseController::class, 'index'])->name('boarding-houses');
        Route::get('/boarding-houses/compare', [BoardingHouseBrowseController::class, 'compare'])->name('boarding-houses.compare');
        Route::get('/applications', [TenantPageController::class, 'applications'])->name('applications');
        Route::get('/reservations', [TenantPageController::class, 'reservations'])->name('reservations');
        Route::get('/saved-listings', [FavoriteController::class, 'index'])->name('saved-listings');
        Route::get('/messages', [TenantPageController::class, 'messages'])->name('messages');
        Route::get('/notifications', [TenantPageController::class, 'notifications'])->name('notifications');
        Route::get('/reviews', [TenantPageController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [TenantPageController::class, 'storeReview'])->name('reviews.store');
        Route::get('/settings', [TenantPageController::class, 'settings'])->name('settings');
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/browse-listings', [BoardingHouseBrowseController::class, 'index'])->name('browse-listings');
        Route::get('/boarding-houses', [BoardingHouseBrowseController::class, 'index'])->name('boarding-houses.index');
        Route::get('/boarding-houses/compare', [BoardingHouseBrowseController::class, 'compare'])->name('boarding-houses.compare');
        Route::get('/boarding-houses/{boardingHouse}', [BoardingHouseBrowseController::class, 'show'])->name('boarding-houses.show');

        Route::get('/applications', [TenantPageController::class, 'applications'])->name('applications');
        Route::get('/bookings', [TenantPageController::class, 'reservations'])->name('bookings');
        Route::get('/reservations', [TenantPageController::class, 'reservations'])->name('reservations');
        Route::get('/messages', [TenantPageController::class, 'messages'])->name('messages');
        Route::get('/notifications', [TenantPageController::class, 'notifications'])->name('notifications');
        Route::get('/reviews', [TenantPageController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [TenantPageController::class, 'storeReview'])->name('reviews.store');
        Route::get('/settings', [TenantPageController::class, 'settings'])->name('settings');

        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{boardingHouse}', [FavoriteController::class, 'store'])->middleware('throttle:30,1')->name('favorites.store');
        Route::delete('/favorites/{boardingHouse}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::post('/boarding-houses/{boardingHouse}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');
        Route::post('/boarding-houses/{boardingHouse}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('reservations.store');
    });

    Route::post('/boarding-houses/{boarding_house}/apply', [BoardingHouseApplicationController::class, 'store'])->name('tenant.boarding-houses.apply');
    Route::post('/boarding-houses/apply', [BoardingHouseApplicationController::class, 'storeFromSelect'])->name('tenant.boarding-houses.apply.select');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin CRUD Resource (Single definition)
    Route::resource('admins', AdminController::class)->middleware('admin');
});

require __DIR__.'/auth.php';
