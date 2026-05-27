<?php

use App\Http\Controllers\AdminListingController;
use App\Http\Controllers\AdminOwnerController;
use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Map\BoardingHouseMapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\BoardingHouseBrowseController;
use App\Http\Controllers\User\InquiryController;
use App\Http\Controllers\User\MatchProfileController;
use App\Http\Controllers\User\RecommendationController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\User\RoommateMatchRequestController;
use App\Http\Controllers\User\TenantAreaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware('guest')->get('/auth', fn () => redirect()->route('login'))->name('auth.choice');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(Auth::user()?->dashboardRouteName() ?? 'user.dashboard');
    })->name('dashboard');

    Route::prefix('map')->name('map.')->group(function () {
        Route::get('/admin/listings', [BoardingHouseMapController::class, 'admin'])
            ->middleware('admin')
            ->name('admin.listings');
        Route::get('/user/listings', [BoardingHouseMapController::class, 'user'])
            ->middleware('user')
            ->name('user.listings');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminOwnerController::class, 'dashboard'])->name('dashboard');

        Route::get('/users', [AdminOwnerController::class, 'users'])->name('users');
        Route::post('/users', [AdminOwnerController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}', [AdminOwnerController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminOwnerController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/boarding-houses', [AdminOwnerController::class, 'boardingHouses'])->name('boarding-houses');
        Route::get('/my-boarding-house', [AdminListingController::class, 'myBoardingHouse'])->name('my-boarding-house');
        Route::get('/listings', [AdminOwnerController::class, 'boardingHouses'])->name('listings');
        Route::post('/listings', [BoardingHouseController::class, 'store'])->name('listings.store');
        Route::put('/listings/{boarding_house}', [BoardingHouseController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{boarding_house}', [BoardingHouseController::class, 'destroy'])->name('listings.destroy');

        Route::get('/rooms', [AdminListingController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/tenant-profiles', [AdminOwnerController::class, 'tenantProfiles'])->name('tenant-profiles');
        Route::patch('/tenant-profiles/{user}', [AdminOwnerController::class, 'updateTenantProfile'])->name('tenant-profiles.update');
        Route::delete('/tenant-profiles/{tenantProfile}', [AdminOwnerController::class, 'destroyTenantProfile'])->name('tenant-profiles.destroy');

        Route::get('/compatibility-scores', [AdminOwnerController::class, 'compatibilityScores'])->name('compatibility-scores');
        Route::get('/recommendations', [AdminOwnerController::class, 'recommendations'])->name('recommendations');
        Route::get('/match-requests', [AdminOwnerController::class, 'matchRequests'])->name('match-requests');
        Route::post('/match-requests', [AdminOwnerController::class, 'storeMatchRequest'])->name('match-requests.store');
        Route::patch('/match-requests/{matchRequest}', [AdminOwnerController::class, 'updateMatchRequest'])->name('match-requests.update');

        Route::get('/inquiries', [AdminOwnerController::class, 'inquiries'])->name('inquiries');
        Route::patch('/inquiries/{inquiry}', [AdminOwnerController::class, 'updateInquiry'])->name('inquiries.update');
        Route::get('/messages', [AdminOwnerController::class, 'messages'])->name('messages');
        Route::get('/reservations', [AdminOwnerController::class, 'reservations'])->name('reservations');
        Route::patch('/reservations/{reservation}', [AdminOwnerController::class, 'updateReservation'])->name('reservations.update');
        Route::get('/payments', [AdminOwnerController::class, 'payments'])->name('payments');
        Route::post('/payments', [AdminOwnerController::class, 'storePayment'])->name('payments.store');
        Route::patch('/payments/{payment}', [AdminOwnerController::class, 'updatePayment'])->name('payments.update');

        Route::get('/reviews', [AdminOwnerController::class, 'reviews'])->name('reviews');
        Route::patch('/reviews/{review}', [AdminOwnerController::class, 'updateReview'])->name('reviews.update');
        Route::get('/reports', [AdminOwnerController::class, 'reports'])->name('reports');
        Route::get('/notifications', [AdminOwnerController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [AdminOwnerController::class, 'storeNotification'])->name('notifications.store');
        Route::patch('/notifications/{notification}', [AdminOwnerController::class, 'updateNotification'])->name('notifications.update');

        Route::get('/settings', [AdminOwnerController::class, 'settings'])->name('settings');
        Route::patch('/settings/profile', [AdminOwnerController::class, 'updateSettingsProfile'])->name('settings.profile');
        Route::patch('/settings/security', [AdminOwnerController::class, 'updateSettingsSecurity'])->name('settings.security');
        Route::post('/settings/action', [AdminOwnerController::class, 'settingsAction'])->name('settings.action');
    });

    Route::middleware('user')->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');
        Route::get('/browse-listings', [BoardingHouseBrowseController::class, 'index'])->name('browse');
        Route::get('/browse-listings/compare', [BoardingHouseBrowseController::class, 'compare'])->name('browse.compare');
        Route::get('/browse-listings/{boardingHouse}', [BoardingHouseBrowseController::class, 'show'])->name('browse.show');

        Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations');
        Route::get('/recommendations/{candidate}', [RecommendationController::class, 'show'])->name('recommendations.show');
        Route::get('/recommendations/{candidate}/explain', [RecommendationController::class, 'explain'])->name('recommendations.explain');
        Route::post('/recommendations/{candidate}/requests', [RoommateMatchRequestController::class, 'store'])->name('recommendations.requests.store');
        Route::post('/match-requests/{matchRequest}/accept', [RoommateMatchRequestController::class, 'accept'])->name('match-requests.accept');
        Route::post('/match-requests/{matchRequest}/decline', [RoommateMatchRequestController::class, 'decline'])->name('match-requests.decline');
        Route::post('/match-requests/{matchRequest}/cancel', [RoommateMatchRequestController::class, 'cancel'])->name('match-requests.cancel');

        Route::get('/reservations', [TenantAreaController::class, 'reservations'])->name('reservations');
        Route::patch('/reservations/{reservation}/cancel', [TenantAreaController::class, 'cancelReservation'])->name('reservations.cancel');
        Route::get('/payments', [TenantAreaController::class, 'payments'])->name('payments');
        Route::get('/messages', [TenantAreaController::class, 'messages'])->name('messages');
        Route::post('/messages', [TenantAreaController::class, 'storeMessage'])->name('messages.store');
        Route::get('/reviews', [TenantAreaController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [TenantAreaController::class, 'storeReview'])->name('reviews.store');
        Route::patch('/reviews/{review}', [TenantAreaController::class, 'updateReview'])->name('reviews.update');
        Route::delete('/reviews/{review}', [TenantAreaController::class, 'destroyReview'])->name('reviews.destroy');
        Route::get('/profile', [MatchProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [MatchProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [AccountController::class, 'show'])->name('settings');
        Route::put('/settings', [AccountController::class, 'update'])->name('settings.update');

        Route::post('/browse-listings/{boardingHouse}/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');
        Route::post('/browse-listings/{boardingHouse}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('reservations.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
