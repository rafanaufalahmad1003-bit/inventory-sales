<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Owner dan Admin memiliki wewenang penuh yang sama
        Gate::define('admin-or-owner', function (User $user) {
            return $user->isAdminOrOwner();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdminOrOwner();
        });

        Gate::define('delete-master', function (User $user) {
            return $user->isAdminOrOwner();
        });

        Gate::define('cancel-transaction', function (User $user) {
            return $user->isAdminOrOwner();
        });

        Gate::define('manage-master', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'staff']);
        });

        Gate::define('manage-transactions', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'staff']);
        });

        Gate::define('manage-customers', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'staff']);
        });
    }
}
