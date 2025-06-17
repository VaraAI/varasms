<?php

namespace VaraSMS\Laravel;

use Illuminate\Support\ServiceProvider;
use VaraSMS\Laravel\VaraSMSClient;

class VaraSMSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/varasms.php' => config_path('varasms.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/varasms.php', 'varasms'
        );

        $this->app->singleton('varasms', function ($app) {
            return new VaraSMSClient(
                config('varasms.username'),
                config('varasms.password'),
                config('varasms.base_url', 'https://messaging-service.co.tz')
            );
        });
    }
} 