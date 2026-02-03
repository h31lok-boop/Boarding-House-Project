<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BoardingHousePolicyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
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
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::resource('/boarding-houses', \App\Http\Controllers\BoardingHouseController::class)->names('boarding-houses');
        Route::get('/boarding-house-policies', [BoardingHousePolicyController::class, 'index'])->name('boarding-house-policies.index');
        Route::post('/boarding-house-policies', [BoardingHousePolicyController::class, 'update'])->name('boarding-house-policies.update');
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

    // Caretaker Dashboard (role-gated)
    Route::get('/caretaker/dashboard', function () {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('caretaker'), 403);
        return view('caretaker.dashboard');
    })->name('caretaker.dashboard');

    // OSAS Dashboard (role-gated)
    Route::get('/osas/dashboard', function () {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('osas'), 403);
        return view('osas.dashboard');
    })->name('osas.dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin CRUD Resource (Single definition)
    Route::resource('admins', AdminController::class);
});

require __DIR__.'/auth.php';
