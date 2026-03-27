<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts receive stateful API authentication.
    | For a mobile app, these are typically empty.
    |
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Statefulness Headers
    |--------------------------------------------------------------------------
    */
    'stateful_headers' => explode(',', env('SANCTUM_STATEFUL_HEADERS', 'x-xsrf-token,x-csrf-token')),

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    */
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION'),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];

