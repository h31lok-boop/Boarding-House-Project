<?php

use App\Http\Controllers\Admin\AdminHelpCenterController;
use App\Http\Controllers\Admin\PaymentReceiptVerificationController;
use App\Http\Controllers\AdminListingController;
use App\Http\Controllers\AdminOwnerController;
use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Map\BoardingHouseMapController;
use App\Http\Controllers\Owner\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\User\BoardingHouseBrowseController;
use App\Http\Controllers\User\HelpCenterController;
use App\Http\Controllers\User\InquiryController;
use App\Http\Controllers\User\PaymentReceiptController;
use App\Http\Controllers\User\RecommendationController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\RoommateMatchRequestController;
use App\Http\Controllers\User\TenantAreaController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserPreferenceController;
use App\Http\Controllers\User\UserSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware('guest')->get('/auth', fn () => redirect()->route('login'))->name('auth.choice');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $role = $user?->role ? strtolower($user->role) : null;

        if (in_array($role, ['owner'], true)) {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->route($user?->dashboardRouteName() ?? 'user.dashboard');
    })->name('dashboard');

    Route::prefix('map')->name('map.')->group(function () {
        Route::get('/admin/listings', [BoardingHouseMapController::class, 'admin'])
            ->middleware('admin')
            ->name('admin.listings');
        Route::get('/user/listings', [BoardingHouseMapController::class, 'user'])
            ->middleware('user')
            ->name('user.listings');
    });

    Route::get('/payment-receipts/{receipt}', [PaymentReceiptController::class, 'show'])->name('payment-receipts.show');
    Route::get('/payment-receipts/{receipt}/download', [PaymentReceiptController::class, 'download'])->name('payment-receipts.download');

    /*
    |------------------------------------------------------------------
    | Admin workspace (super-admins only) — system-wide administration.
    |------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminOwnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/export', [AdminOwnerController::class, 'dashboardExport'])->name('dashboard.export');
        Route::get('/search', [AdminOwnerController::class, 'search'])->name('search');

        // User management
        Route::get('/users', [AdminOwnerController::class, 'users'])->name('users');
        Route::post('/users', [AdminOwnerController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}', [AdminOwnerController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminOwnerController::class, 'destroyUser'])->name('users.destroy');

        // My property (owner's single-property dashboard)
        Route::get('/my-boarding-house', [AdminListingController::class, 'myBoardingHouse'])->name('my-boarding-house');

        // Boarding-house oversight & approval (view all; edits gated by BoardingHousePolicy)
        Route::get('/boarding-houses', [AdminOwnerController::class, 'boardingHouses'])->name('boarding-houses');
        Route::get('/boarding-houses/create', [AdminOwnerController::class, 'boardingHouses'])->name('boarding-houses.create');
        Route::get('/listings', [AdminOwnerController::class, 'boardingHouses'])->name('listings');
        Route::post('/listings', [BoardingHouseController::class, 'store'])->name('listings.store');
        Route::put('/listings/{boarding_house}', [BoardingHouseController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{boarding_house}', [BoardingHouseController::class, 'destroy'])->name('listings.destroy');

        // Rooms, reservations & payments (shared admin/owner pages)
        Route::get('/rooms', [AdminListingController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::get('/reservations', [AdminOwnerController::class, 'reservations'])->name('reservations');
        Route::get('/reservations/list', [AdminOwnerController::class, 'reservations'])->name('reservations.index');
        Route::get('/reservations/export', [AdminOwnerController::class, 'exportReservations'])->name('reservations.export');
        Route::patch('/reservations/{reservation}', [AdminOwnerController::class, 'updateReservation'])->name('reservations.update');
        Route::delete('/reservations/{reservation}', [AdminOwnerController::class, 'destroyReservation'])->name('reservations.destroy');
        Route::get('/api/boarding-houses/{boardingHouse}/available-rooms', [AdminOwnerController::class, 'availableRooms'])->name('api.boarding-houses.available-rooms');
        Route::get('/payments', [AdminOwnerController::class, 'payments'])->name('payments');
        Route::get('/transactions', [AdminOwnerController::class, 'payments'])->name('transactions.index');
        Route::post('/payments', [AdminOwnerController::class, 'storePayment'])->name('payments.store');
        Route::patch('/payments/{payment}', [AdminOwnerController::class, 'updatePayment'])->name('payments.update');

        // Matchmaking system controls
        Route::get('/compatibility-scores', [AdminOwnerController::class, 'compatibilityScores'])->name('compatibility-scores');
        Route::get('/recommendations', [AdminOwnerController::class, 'recommendations'])->name('recommendations');
        Route::get('/match-requests', [AdminOwnerController::class, 'matchRequests'])->name('match-requests');
        Route::post('/match-requests', [AdminOwnerController::class, 'storeMatchRequest'])->name('match-requests.store');
        Route::patch('/match-requests/{matchRequest}', [AdminOwnerController::class, 'updateMatchRequest'])->name('match-requests.update');

        // Reviews moderation
        Route::get('/reviews', [AdminOwnerController::class, 'reviews'])->name('reviews');
        Route::patch('/reviews/{review}', [AdminOwnerController::class, 'updateReview'])->name('reviews.update');

        // Reports
        Route::get('/reports/export', [AdminOwnerController::class, 'exportReports'])->name('reports.export');
        Route::get('/reports', [AdminOwnerController::class, 'reports'])->name('reports.index');

        // Payment-receipt verification
        Route::get('/payment-verification', [PaymentReceiptVerificationController::class, 'index'])->name('payment-receipts.index');
        Route::patch('/payment-verification/{receipt}/approve', [PaymentReceiptVerificationController::class, 'approve'])->name('payment-receipts.approve');
        Route::patch('/payment-verification/{receipt}/reject', [PaymentReceiptVerificationController::class, 'reject'])->name('payment-receipts.reject');

        // Tenant profiles
        Route::get('/tenants', [AdminOwnerController::class, 'tenantProfiles'])->name('tenants.index');
        Route::get('/tenants/create', [AdminOwnerController::class, 'tenantProfiles'])->name('tenants.create');
        Route::get('/tenant-profiles', [AdminOwnerController::class, 'tenantProfiles'])->name('tenant-profiles');
        Route::patch('/tenant-profiles/{user}', [AdminOwnerController::class, 'updateTenantProfile'])->name('tenant-profiles.update');

        // Inquiries & messages
        Route::get('/inquiries', [AdminOwnerController::class, 'inquiries'])->name('inquiries');
        Route::get('/inquiries/list', [AdminOwnerController::class, 'inquiries'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [AdminOwnerController::class, 'updateInquiry'])->name('inquiries.update');
        Route::get('/messages', [AdminOwnerController::class, 'messages'])->name('messages');
        Route::get('/messages/inbox', [AdminOwnerController::class, 'messages'])->name('messages.index');

        // Notifications (admin's own)
        Route::get('/notifications', [AdminOwnerController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications', [AdminOwnerController::class, 'storeNotification'])->name('notifications.store');
        Route::delete('/notifications/clear-all', [AdminOwnerController::class, 'clearNotifications'])->name('notifications.clear');
        Route::patch('/notifications/{notification}', [AdminOwnerController::class, 'updateNotification'])->name('notifications.update');
        Route::delete('/notifications/{notification}', [AdminOwnerController::class, 'destroyNotification'])->name('notifications.destroy');

        // Help
        Route::get('/help-center', [AdminHelpCenterController::class, 'index'])->name('help-center.index');

        // Settings (admin account)
        Route::get('/settings/account', [AdminOwnerController::class, 'settings'])->name('settings');
        Route::get('/settings', [AdminOwnerController::class, 'settings'])->name('settings.index');
        Route::put('/settings/profile', [AdminOwnerController::class, 'updateSettingsProfile'])->name('settings.profile.update');
        Route::patch('/settings/profile', [AdminOwnerController::class, 'updateSettingsProfile'])->name('settings.profile');
        Route::put('/settings/password', [AdminOwnerController::class, 'updateSettingsSecurity'])->name('settings.password.update');
        Route::patch('/settings/security', [AdminOwnerController::class, 'updateSettingsSecurity'])->name('settings.security');
        Route::put('/settings/two-factor', [AdminOwnerController::class, 'updateSettingsTwoFactor'])->name('settings.two-factor.update');
        Route::post('/settings/action', [AdminOwnerController::class, 'settingsAction'])->name('settings.action');
        Route::post('/settings/logout-other-devices', [AdminOwnerController::class, 'logoutOtherDevices'])->name('settings.logout-other-devices');
    });

    /*
    |------------------------------------------------------------------
    | Owner workspace (property owners only) — scoped to owned houses.
    |------------------------------------------------------------------
    */
    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');

        // My property
        Route::get('/my-boarding-house', [OwnerController::class, 'property'])->name('my-boarding-house');
        Route::get('/boarding-houses', [OwnerController::class, 'boardingHousesIndex'])->name('boarding-houses');
        Route::get('/boarding-houses/create', [OwnerController::class, 'boardingHousesIndex'])->name('boarding-houses.create');
        Route::post('/listings', [BoardingHouseController::class, 'store'])->name('listings.store');
        Route::put('/listings/{boarding_house}', [BoardingHouseController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{boarding_house}', [BoardingHouseController::class, 'destroy'])->name('listings.destroy');

        // Rooms
        Route::get('/rooms', [OwnerController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Reservations
        Route::get('/reservations', [OwnerController::class, 'reservations'])->name('reservations');
        Route::get('/reservations/list', [OwnerController::class, 'reservations'])->name('reservations.index');
        Route::get('/reservations/export', [OwnerController::class, 'exportReservations'])->name('reservations.export');
        Route::patch('/reservations/{reservation}', [OwnerController::class, 'updateReservation'])->name('reservations.update');
        Route::delete('/reservations/{reservation}', [OwnerController::class, 'destroyReservation'])->name('reservations.destroy');
        Route::get('/api/boarding-houses/{boardingHouse}/available-rooms', [OwnerController::class, 'availableRooms'])->name('api.boarding-houses.available-rooms');

        // Payments
        Route::get('/payments', [OwnerController::class, 'payments'])->name('payments');
        Route::get('/transactions', [OwnerController::class, 'payments'])->name('transactions.index');
        Route::post('/payments', [OwnerController::class, 'storePayment'])->name('payments.store');
        Route::patch('/payments/{payment}', [OwnerController::class, 'updatePayment'])->name('payments.update');

        // Tenants
        Route::get('/tenants', [OwnerController::class, 'tenantProfiles'])->name('tenants.index');
        Route::get('/tenant-profiles', [OwnerController::class, 'tenantProfiles'])->name('tenant-profiles');
        Route::patch('/tenant-profiles/{user}', [OwnerController::class, 'updateTenantProfile'])->name('tenant-profiles.update');

        // Inquiries
        Route::get('/inquiries', [OwnerController::class, 'inquiries'])->name('inquiries');
        Route::get('/inquiries/list', [OwnerController::class, 'inquiries'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [OwnerController::class, 'updateInquiry'])->name('inquiries.update');

        // Messages
        Route::get('/messages', [OwnerController::class, 'messages'])->name('messages');
        Route::get('/messages/inbox', [OwnerController::class, 'messages'])->name('messages.index');

        // Notifications
        Route::get('/notifications', [OwnerController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications', [OwnerController::class, 'storeNotification'])->name('notifications.store');
        Route::delete('/notifications/clear-all', [OwnerController::class, 'clearNotifications'])->name('notifications.clear');
        Route::patch('/notifications/{notification}', [OwnerController::class, 'updateNotification'])->name('notifications.update');
        Route::delete('/notifications/{notification}', [OwnerController::class, 'destroyNotification'])->name('notifications.destroy');

        // Settings (owner account)
        Route::get('/settings/account', [OwnerController::class, 'settings'])->name('settings');
        Route::get('/settings', [OwnerController::class, 'settings'])->name('settings.index');
        Route::put('/settings/profile', [OwnerController::class, 'updateSettingsProfile'])->name('settings.profile.update');
        Route::patch('/settings/profile', [OwnerController::class, 'updateSettingsProfile'])->name('settings.profile');
        Route::put('/settings/password', [OwnerController::class, 'updateSettingsSecurity'])->name('settings.password.update');
        Route::patch('/settings/security', [OwnerController::class, 'updateSettingsSecurity'])->name('settings.security');
        Route::put('/settings/two-factor', [OwnerController::class, 'updateSettingsTwoFactor'])->name('settings.two-factor.update');
        Route::post('/settings/action', [OwnerController::class, 'settingsAction'])->name('settings.action');
        Route::post('/settings/logout-other-devices', [OwnerController::class, 'logoutOtherDevices'])->name('settings.logout-other-devices');
    });

    Route::middleware('user')->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');

        Route::get('/boarding-houses', [BoardingHouseBrowseController::class, 'index'])->name('boarding-houses.index');
        Route::get('/boarding-houses/recommended', [BoardingHouseBrowseController::class, 'recommended'])->name('boarding-houses.recommended');
        Route::get('/boarding-houses/compare', [BoardingHouseBrowseController::class, 'compare'])->name('boarding-houses.compare');
        Route::get('/boarding-houses/{boardingHouse}', [BoardingHouseBrowseController::class, 'show'])->name('boarding-houses.show');
        Route::get('/browse-listings', [BoardingHouseBrowseController::class, 'index'])->name('browse');
        Route::get('/browse-listings/compare', [BoardingHouseBrowseController::class, 'compare'])->name('browse.compare');
        Route::get('/browse-listings/{boardingHouse}', [BoardingHouseBrowseController::class, 'show'])->name('browse.show');

        Route::get('/matchmaking', [RecommendationController::class, 'index'])->name('matchmaking.index');
        Route::post('/matchmaking/generate', [RecommendationController::class, 'generate'])->name('matchmaking.generate');
        Route::get('/matchmaking/{candidate}', [RecommendationController::class, 'show'])->name('matchmaking.show');
        Route::get('/matchmaking/{candidate}/explain', [RecommendationController::class, 'explain'])->name('matchmaking.explain');
        Route::post('/matchmaking/{candidate}/requests', [RoommateMatchRequestController::class, 'store'])->name('matchmaking.requests.store');
        Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations');
        Route::get('/recommendations/{candidate}', [RecommendationController::class, 'show'])->name('recommendations.show');
        Route::get('/recommendations/{candidate}/explain', [RecommendationController::class, 'explain'])->name('recommendations.explain');
        Route::post('/recommendations/{candidate}/requests', [RoommateMatchRequestController::class, 'store'])->name('recommendations.requests.store');
        Route::post('/match-requests/{matchRequest}/accept', [RoommateMatchRequestController::class, 'accept'])->name('match-requests.accept');
        Route::post('/match-requests/{matchRequest}/decline', [RoommateMatchRequestController::class, 'decline'])->name('match-requests.decline');
        Route::post('/match-requests/{matchRequest}/cancel', [RoommateMatchRequestController::class, 'cancel'])->name('match-requests.cancel');

        Route::get('/reservations', [TenantAreaController::class, 'reservations'])->name('reservations');
        Route::get('/reservations', [TenantAreaController::class, 'reservations'])->name('reservations.index');
        Route::patch('/reservations/{reservation}/cancel', [TenantAreaController::class, 'cancelReservation'])->name('reservations.cancel');
        Route::get('/payments', [TenantAreaController::class, 'payments'])->name('payments');
        Route::get('/payments', [TenantAreaController::class, 'payments'])->name('payments.index');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('/payment-methods', [TenantAreaController::class, 'storePaymentMethod'])->name('payment-methods.store');
        Route::patch('/payment-methods/{method}/default', [TenantAreaController::class, 'setDefaultPaymentMethod'])->name('payment-methods.default');
        Route::delete('/payment-methods/{method}', [TenantAreaController::class, 'destroyPaymentMethod'])->name('payment-methods.destroy');
        Route::post('/payments/confirm', [TenantAreaController::class, 'confirmPayment'])->name('payments.confirm');
        Route::post('/payment-receipts', [PaymentReceiptController::class, 'store'])->name('payment-receipts.store');
        Route::delete('/payment-receipts/{receipt}', [PaymentReceiptController::class, 'destroy'])->name('payment-receipts.destroy');
        Route::get('/messages', [TenantAreaController::class, 'messages'])->name('messages');
        Route::get('/messages/inbox', [TenantAreaController::class, 'messages'])->name('messages.index');
        Route::post('/messages', [TenantAreaController::class, 'storeMessage'])->name('messages.store');
        Route::get('/help-center', [HelpCenterController::class, 'index'])->name('help');
        Route::get('/help-center', [HelpCenterController::class, 'index'])->name('help-center.index');
        Route::post('/help-center/support-request', [HelpCenterController::class, 'store'])->name('help.store');
        Route::post('/help-center/support-request', [HelpCenterController::class, 'store'])->name('help-center.store');
        Route::get('/reviews', [TenantAreaController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [TenantAreaController::class, 'storeReview'])->name('reviews.store');
        Route::patch('/reviews/{review}', [TenantAreaController::class, 'updateReview'])->name('reviews.update');
        Route::delete('/reviews/{review}', [TenantAreaController::class, 'destroyReview'])->name('reviews.destroy');
        Route::get('/preferences', [UserPreferenceController::class, 'edit'])->name('preferences.index');
        Route::post('/preferences', [UserPreferenceController::class, 'store'])->name('preferences.store');
        Route::put('/preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
        Route::get('/profile', [UserPreferenceController::class, 'edit'])->name('profile');
        Route::put('/profile', [UserPreferenceController::class, 'update'])->name('profile.update');
        Route::get('/settings', [UserSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/personal-information', [UserSettingsController::class, 'updatePersonalInfo'])->name('settings.personal.update');
        Route::put('/settings/contact-information', [UserSettingsController::class, 'updateContactInfo'])->name('settings.contact.update');
        Route::put('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings.password.update');
        Route::put('/settings/notifications', [UserSettingsController::class, 'updateNotificationPreferences'])->name('settings.notifications.update');
        Route::put('/settings/two-factor', [UserSettingsController::class, 'updateTwoFactor'])->name('settings.two-factor.update');
        Route::post('/settings/logout-other-devices', [UserSettingsController::class, 'logoutOtherDevices'])->name('settings.logout-other-devices');
        Route::put('/settings/privacy', [UserSettingsController::class, 'updatePrivacySettings'])->name('settings.privacy.update');
        Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::delete('/notifications/clear-all', [UserNotificationController::class, 'clearAll'])->name('notifications.clearAll');
        Route::post('/notifications/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{id}', [UserNotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::post('/boarding-houses/{boardingHouse}/favorite', [BoardingHouseBrowseController::class, 'favorite'])->name('boarding-houses.favorite');
        Route::post('/boarding-houses/{boardingHouse}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1')->name('boarding-houses.inquiries.store');
        Route::post('/boarding-houses/{boardingHouse}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('boarding-houses.reservations.store');
        Route::post('/browse-listings/{boardingHouse}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');
        Route::post('/browse-listings/{boardingHouse}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('reservations.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
