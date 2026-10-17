<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billing gateway
    |--------------------------------------------------------------------------
    |
    | This demo only ships a fake gateway. "fake" records a local checkout and
    | accepts signed webhook fixtures. It never calls Stripe and needs no keys.
    |
    */

    'gateway' => env('BILLING_GATEWAY', 'fake'),

    'webhook_secret' => env('BILLING_WEBHOOK_SECRET', 'whsec_local_demo'),

    'signature_tolerance' => 300,

    'currency' => 'usd',

];
