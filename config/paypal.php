<?php
/**
 * PayPal Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */

return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id'     => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'app_id'        => env('PAYPAL_APP_ID', ''),
    ],
    'live' => [
        'client_id'     => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'app_id'        => env('PAYPAL_APP_ID', ''),
    ],

    // Can only be 'Sale', 'Authorization' or 'Order'
    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'),
    'notify_url'     => env('APP_URL').'/admin/webhooks/paypal',
    // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'locale'         => env('PAYPAL_LOCALE', 'en_US'),
    // Validate SSL when creating api client.
    'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true),
];
