<?php

namespace VaraSMS\Laravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use VaraSMS\Laravel\VaraSMSServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            VaraSMSServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'VaraSMS' => 'VaraSMS\Laravel\Facades\VaraSMS',
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('varasms.username', 'test_user');
        $app['config']->set('varasms.password', 'test_password');
        $app['config']->set('varasms.base_url', 'https://messaging-service.co.tz');
        $app['config']->set('varasms.sender_id', 'TEST');
    }
} 