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
            $config = config('varasms');
            return new VaraSMSClient(
                $config['base_url'] ?? 'https://messaging-service.co.tz',
                [
                    'auth_method' => $config['auth_method'] ?? 'basic',
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'token' => $config['token'],
                ]
            );
        });
    }
} 