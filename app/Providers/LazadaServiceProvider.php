<?php

namespace App\Providers;

use App\Services\LazadaApiService;
use Illuminate\Support\ServiceProvider;

class LazadaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LazadaApiService::class, function ($app) {
            return new LazadaApiService();
        });
    }

    public function boot(): void
    {
        //
    }
}