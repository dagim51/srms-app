<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telebirr Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Telebirr payment gateway integration.
    | Supports sandbox and production environments.
    |
    */

    'telebirr' => [
        'app_id' => env('TELEBIRR_APP_ID'),
        'app_key' => env('TELEBIRR_APP_KEY'),
        'public_key' => env('TELEBIRR_PUBLIC_KEY'),
        'private_key' => env('TELEBIRR_PRIVATE_KEY'),
        'api_url' => env('TELEBIRR_API_URL', 'https://api.telebirr.com/api/v1'),
        'webhook_url' => env('TELEBIRR_WEBHOOK_URL'),
        'return_url' => env('TELEBIRR_RETURN_URL'),
        'notify_url' => env('TELEBIRR_NOTIFY_URL'),
        'is_sandbox' => env('TELEBIRR_SANDBOX', true),
        'currency' => 'ETB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Idempotency Configuration
    |--------------------------------------------------------------------------
    */

    'idempotency' => [
        'ttl' => env('PAYMENT_IDEMPOTENCY_TTL', 3600), // seconds
        'prefix' => 'payment:idempotency:',
    ],
];