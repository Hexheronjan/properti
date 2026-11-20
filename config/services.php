<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'midtrans' => [
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),
        'payment_enabled' => env('MIDTRANS_PAYMENT_ENABLED', true),
    ],

    'doku' => [
        'client_id' => env('DOKU_CLIENT_ID'),
        'secret_key' => env('DOKU_SECRET_KEY'),
        'api_key' => env('DOKU_API_KEY'),
        'environment' => env('DOKU_ENVIRONMENT', 'production'),
        'base_url' => env('DOKU_BASE_URL', 'https://api.doku.com'),
        'notification_url' => env('DOKU_NOTIFICATION_URL', env('APP_URL') . '/doku/notifications'),
        'payment_due_minutes' => env('DOKU_PAYMENT_DUE_MINUTES', 1440),
    ],

    'whatsapp' => [
        'gateway_url' => env('WHATSAPP_GATEWAY_URL'),
        'api_key' => env('WHATSAPP_GATEWAY_API_KEY'),
        'sender_number' => env('WHATSAPP_GATEWAY_SENDER_NUMBER'),
        'notification_enabled' => env('WHATSAPP_NOTIFICATION_ENABLED', false),
    ],

    'ematerai' => [
        'api_url' => env('EMATERAI_API_URL', 'https://api.ematerai.com/v1'),
        'api_key' => env('EMATERAI_API_KEY'),
        'secret_key' => env('EMATERAI_SECRET_KEY'),
        'enabled' => env('EMATERAI_ENABLED', false),
    ],

];
