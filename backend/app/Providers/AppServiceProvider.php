<?php

namespace App\Providers;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Policies\StudyGroupPolicy;
use App\Domain\Users\Policies\UserPolicy;
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
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('access-admin', function (User $user) {
            return $user->hasPermission('admin.access');
        });

        // RTL/LTR Blade directive
        \Blade::directive('direction', function () {
            return "<?php echo app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?>";
        });
    }
}
