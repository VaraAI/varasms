<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VaraSMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the VaraSMS package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Configure your authentication method and credentials here.
    | Supported methods: 'basic' (username/password) or 'token'
    |
    */
    'auth_method' => env('VARASMS_AUTH_METHOD', 'basic'),
    'username' => env('VARASMS_USERNAME'),
    'password' => env('VARASMS_PASSWORD'),
    'token' => env('VARASMS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Configure your API settings here.
    |
    */
    'base_url' => env('VARASMS_BASE_URL', 'https://messaging-service.co.tz'),
    'sender_id' => env('VARASMS_SENDER_ID'),
    'test_mode' => env('VARASMS_TEST_MODE', false),
]; 