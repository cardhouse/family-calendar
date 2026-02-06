<?php

declare(strict_types=1);

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

    'school_lunch' => [
        'source_url' => env('SCHOOL_LUNCH_SOURCE_URL'),
        'menu_id' => env('SCHOOL_LUNCH_MENU_ID'),
        'site_code' => env('SCHOOL_LUNCH_SITE_CODE'),
        'cache_ttl_minutes' => (int) env('SCHOOL_LUNCH_CACHE_TTL_MINUTES', 30),
        'request_timeout' => (float) env('SCHOOL_LUNCH_REQUEST_TIMEOUT', 10),
    ],

    'weather' => [
        'request_timeout' => (float) env('WEATHER_REQUEST_TIMEOUT', 8),
    ],

];
