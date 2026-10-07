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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Masuk petugas lewat Google (Socialite). Kredensial OAuth ini terpisah dari
    | GOOGLE_DRIVE_* yang dipakai untuk cadangan Drive. Kosongkan redirect agar
    | URL callback dibentuk dari domain yang sedang diakses.
    */
    'google' => [
        'client_id' => env('GOOGLE_SSO_CLIENT_ID'),
        'client_secret' => env('GOOGLE_SSO_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_SSO_REDIRECT_URI'),
    ],

];
