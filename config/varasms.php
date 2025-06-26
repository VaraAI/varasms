<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VaraSMS Authentication Method
    |--------------------------------------------------------------------------
    |
    | Here you can specify which authentication method to use. Available options are:
    | - 'basic' (username/password)
    | - 'token' (authorization token)
    |
    */

    'auth_method' => env('VARASMS_AUTH_METHOD', 'basic'),

    /*
    |--------------------------------------------------------------------------
    | VaraSMS API Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may configure your VaraSMS API credentials. These credentials will
    | be used to authenticate with the VaraSMS API service. You can either use
    | username/password combination or an authorization token.
    |
    */

    'username' => env('VARASMS_USERNAME'),
    'password' => env('VARASMS_PASSWORD'),
    'token' => env('VARASMS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | VaraSMS API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for the VaraSMS API. You can change this if you need
    | to use a different endpoint or if you're using the test environment.
    |
    */

    'base_url' => env('VARASMS_BASE_URL', 'https://messaging-service.co.tz'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender ID
    |--------------------------------------------------------------------------
    |
    | This is the default sender ID that will be used when sending SMS messages.
    | You can override this when sending individual messages.
    |
    */

    'sender_id' => env('VARASMS_SENDER_ID'),
]; 