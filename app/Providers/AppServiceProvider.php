<?php

namespace App\Providers;

use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Policies\BoardingHousePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RoomPolicy;
use App\Services\IntegrationSettingsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\MattiServiceArea::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A stale public/hot file can make a reverse proxy expose the local
        // Vite dev server (for example http://[::1]:5173). Public requests
        // must use the committed build manifest instead.
        if ($this->app->bound('request')) {
            $host = strtolower((string) $this->app['request']->getHost());
            $localHosts = ['localhost', '127.0.0.1', '::1'];
            $isLocalHost = in_array($host, $localHosts, true) || str_ends_with($host, '.test');
            $configuredHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
            $isPubliclyConfigured = $configuredHost !== ''
                && ! in_array($configuredHost, $localHosts, true)
                && ! str_ends_with($configuredHost, '.test');

            if (! $isLocalHost || $isPubliclyConfigured) {
                Vite::useHotFile(storage_path('framework/vite-public-hot'));
            }
        }

        // Apply encrypted administrator overrides before any integration is
        // resolved. When no override exists, config/services.php and .env
        // remain the source of truth.
        app(IntegrationSettingsService::class)->applyToRuntimeConfig();

        // Ownership policies enforce strict per-record separation between owners.
        Gate::policy(BoardingHouse::class, BoardingHousePolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        // System administration is super-admin only.
        Gate::define('manage-users', function (User $user): bool {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-boarding-houses', function (User $user): bool {
            return $user->isSuperAdmin() || $user->isStrictOwner();
        });

        Gate::define('access-map-features', function (User $user): bool {
            return $user->isManager() || $user->isUser();
        });
    }
}
