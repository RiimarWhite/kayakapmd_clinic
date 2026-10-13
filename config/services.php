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

    /**
     * Detailed Comment: PhilHealth e-Claims API and encryption configuration settings.
     * url: Base URL of the PhilHealth e-Claims web service endpoint.
     * passphrase: Secret passphrase utilized for AES-256-CBC encryption of XML payloads.
     */
    'philhealth' => [
        'url' => env('PHILHEALTH_URL', 'https://epress.philhealth.gov.ph'),
        'passphrase' => env('PHILHEALTH_PASSPHRASE', 'kayakapmd'),
    ],

];
