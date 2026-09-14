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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ocr' => [
        'base_url' => env('OCR_BASE_URL'),
        'internal_url' => env('OCR_INTERNAL_URL'),
    ],

    'elog' => [
        'api_base_url' => env('ELOG_API_BASE_URL'),
        'api_login_username' => env('ELOG_API_LOGIN_USERNAME'),
        'api_login_password' => env('ELOG_API_LOGIN_PASSWORD'),
        'api_access_token_key' => env('ELOG_API_ACCESS_TOKEN_KEY', 'elog_api_access_token'),
    ],

];
