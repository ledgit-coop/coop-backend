<?php

return [
    'public' => [
        'receiver_email' => env('DSPACC_PUBLIC_RECEIVER_EMAIL', 'admin@dalansapagasenso.org'),
        'carbon_copy' => explode(",", env('DSPACC_PUBLIC_RECEIVER_CC_EMAIL', '')),
    ],
];
