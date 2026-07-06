<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Termii API Key
    |--------------------------------------------------------------------------
    |
    | The API key for your Termii account. You can find it on your Termii
    | dashboard at https://accounts.termii.com under the "API" section.
    |
    */

    'api_key' => env('TERMII_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Termii Base URL
    |--------------------------------------------------------------------------
    |
    | Termii now issues an account-specific base URL. You can find yours on
    | your Termii dashboard. If none is provided we fall back to the shared
    | default host. Do not include a trailing slash or the "/api" segment.
    |
    */

    'base_url' => env('TERMII_BASE_URL', 'https://v3.api.termii.com'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender ID
    |--------------------------------------------------------------------------
    |
    | The Sender ID used as the "from" value when one is not passed explicitly
    | to a message/OTP call. Must be an approved Sender ID on your account.
    |
    */

    'sender_id' => env('TERMII_SENDER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | The default route used for messages: "generic", "dnd" or "whatsapp".
    | Transactional/OTP traffic should generally use "dnd".
    |
    */

    'channel' => env('TERMII_CHANNEL', 'generic'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for a response from the Termii API.
    |
    */

    'timeout' => env('TERMII_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Throw On Failure
    |--------------------------------------------------------------------------
    |
    | When true, any 4xx/5xx response from Termii throws a
    | Illuminate\Http\Client\RequestException instead of returning the
    | response for you to inspect manually.
    |
    */

    'throw' => env('TERMII_THROW', false),

];
