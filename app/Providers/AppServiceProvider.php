<?php

namespace App\Providers;

use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelSelectOption;
use App\Models\TravelYear;
use App\Models\User;
use App\Policies\CompanySettingPolicy;
use App\Policies\TravelColumnPolicy;
use App\Policies\TravelRecordPolicy;
use App\Policies\TravelSelectOptionPolicy;
use App\Policies\TravelYearPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        Gate::policy(TravelYear::class, TravelYearPolicy::class);
        Gate::policy(TravelColumn::class, TravelColumnPolicy::class);
        Gate::policy(TravelSelectOption::class, TravelSelectOptionPolicy::class);
        Gate::policy(TravelRecord::class, TravelRecordPolicy::class);
        Gate::policy(CompanySetting::class, CompanySettingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function ($user, string $ability, array $arguments = []): ?bool {
            if (! $user->isAdmin()) {
                return null;
            }

            if (($arguments[0] ?? null) instanceof TravelColumn || ($arguments[0] ?? null) instanceof User) {
                return null;
            }

            return true;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
