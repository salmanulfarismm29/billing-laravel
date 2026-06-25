<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bill App Configuration
    |--------------------------------------------------------------------------
    */
    'qr_path' => env('BILLAPP_QR_PATH', 'bills/qr-codes'),
    'hash_salt' => env('BILLAPP_HASH_SALT', 'tea-billing-app-secret-salt'),
    'encryption_enabled' => env('BILLAPP_ENCRYPTION_ENABLED', env('APP_ENV') === 'production'),
];
