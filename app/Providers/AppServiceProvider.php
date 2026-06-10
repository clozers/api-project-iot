<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\SensorLogRepositoryInterface;
use App\Repositories\SensorLogRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SensorLogRepositoryInterface::class, SensorLogRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
