<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! app()->isProduction());

        \Illuminate\Support\Facades\Route::bind('organization', function (string $value) {
            return \App\Models\Organization::where('slug', $value)->firstOrFail();
        });

        \Illuminate\Support\Facades\Route::bind('asset', function (string $value) {
            return \App\Models\Asset::where('code', $value)->firstOrFail();
        });

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\BorrowingApproved::class,
            [\App\Listeners\SendBorrowingReviewedNotification::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\BorrowingRejected::class,
            [\App\Listeners\SendBorrowingReviewedNotification::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\MemberRemoved::class,
            [\App\Listeners\SendRoleChangedNotification::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\MemberRoleChanged::class,
            [\App\Listeners\SendRoleChangedNotification::class, 'handle']
        );
    }
}
