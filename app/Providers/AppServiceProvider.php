<?php

namespace App\Providers;

use App\Builders\HealthRecordBuilder;
use App\Facades\HealthRecordFacade;
use App\Facades\UserFacade;
use App\Repositories\HealthRecord\HealthRecordRepository;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(HealthRecordRepositoryInterface::class, HealthRecordRepository::class);

        // Register the UserFacade
        $this->app->singleton(UserFacade::class, function ($app) {
            return new UserFacade(
                $app->make(UserRepositoryInterface::class),
            );
        });

        // Register the HealthRecordFacade
        $this->app->singleton(HealthRecordFacade::class, function ($app) {
            return new HealthRecordFacade(
                $app->make(HealthRecordBuilder::class),
                $app->make(HealthRecordRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
