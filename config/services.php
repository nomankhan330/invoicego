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

    'fbr' => [
        'sandbox_url' => env('FBR_SANDBOX_BASE_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb'),
        'sandbox_validation_url' => env('FBR_SANDBOX_VALIDATION_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb'),
        'production_url' => env('FBR_PRODUCTION_BASE_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata'),
        'production_validation_url' => env('FBR_PRODUCTION_VALIDATION_URL', 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata'),
        'provinces_url' => env('FBR_PROVINCES_URL', 'https://gw.fbr.gov.pk/pdi/v1/provinces'),
        'use_simulator' => env('FBR_USE_SIMULATOR', false),
    ],

];
