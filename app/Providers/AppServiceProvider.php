<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user): bool {
            return $user->isSuperDuperAdmin() || in_array(strtolower((string) $user->role), ['admin'], true);
        });

        Gate::define('manage-boarding-houses', function (User $user): bool {
            return $user->isSuperDuperAdmin()
                || in_array(strtolower((string) $user->role), ['admin', 'owner', 'manager'], true);
        });

        Gate::define('access-map-features', function (User $user): bool {
            return $user->isSuperDuperAdmin()
                || in_array(strtolower((string) $user->role), ['admin', 'owner', 'manager', 'tenant', 'user'], true);
        });
    }
}
