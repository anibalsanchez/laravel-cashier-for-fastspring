<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billable Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait
    | provided by Cashier For Fastspring. It will be used to retrieve and store
    | the Fastspring ID and other billing-related information.
    |
    */

    'model' => env('FASTSPRING_MODEL', App\User::class),

    /*
    |--------------------------------------------------------------------------
    | Fastspring Credentials
    |--------------------------------------------------------------------------
    |
    | The Fastspring username and password will allow your application to call
    | the Fastspring API.
    |
    */

    'username' => env('FASTSPRING_USERNAME'),

    'password' => env('FASTSPRING_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | HMAC SHA256 Secret
    |--------------------------------------------------------------------------
    |
    | Optionally specify a secret phrase for creating a digest of the payload.
    |
    | Message Security: https://developer.fastspring.com/reference/message-security
    |
    */

    'hmac_secret' => env('FASTSPRING_HMAC_SECRET'),

];
