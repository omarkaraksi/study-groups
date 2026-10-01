<?php

namespace App\Providers;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Policies\StudyGroupPolicy;
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
        Gate::policy(StudyGroup::class, StudyGroupPolicy::class);

        Gate::define('access-admin', function (User $user) {
            return $user->hasPermission('admin.access');
        });
    }
}
