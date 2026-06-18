<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Play Package Name
    |--------------------------------------------------------------------------
    |
    | The application id of the Android app on Google Play. Used as the
    | `packageName` argument when calling the Google Play Developer API.
    |
    */
    'package_name' => env('GOOGLE_PLAY_PACKAGE_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Absolute path to the Google service-account JSON key used to authenticate
    | calls to the Google Play Developer API (purchases.subscriptionsv2.get).
    | When unset, RTDN ingestion logs and no-ops instead of erroring.
    |
    */
    'credentials' => env('GOOGLE_PLAY_CREDENTIALS'),

    /*
    |--------------------------------------------------------------------------
    | RTDN Webhook Shared Secret
    |--------------------------------------------------------------------------
    |
    | Shared secret the Pub/Sub push subscription must present (via ?token=
    | query param or X-RTDN-Token header) to reach the RTDN webhook.
    |
    */
    'rtdn_secret' => env('RTDN_WEBHOOK_SECRET'),

];
