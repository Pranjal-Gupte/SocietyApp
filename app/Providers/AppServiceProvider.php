<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Models\Notice;
use App\Policies\NoticePolicy;
use App\Models\Complaint;
use App\Policies\ComplaintPolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Visitor;
use App\Policies\VisitorPolicy;
use App\Models\Payment;
use App\Policies\PaymentPolicy;

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
        Gate::define('manage-society', function ($user) {
            return $user->canManageSociety();
        });
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected $policies = [
    Notice::class => NoticePolicy::class,
    Complaint::class => ComplaintPolicy::class,
    User::class => UserPolicy::class,
    Visitor::class => VisitorPolicy::class,
    Payment::class => PaymentPolicy::class,
    ];
}
