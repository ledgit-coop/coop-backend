<?php

return [
    'public' => [
        'receiver_email' => env('KOOP_LEDGET_PUBLIC_RECEIVER_EMAIL', 'admin@dalansapagasenso.org'),
        'carbon_copy' => explode(",", env('KOOP_LEDGET_PUBLIC_RECEIVER_CC_EMAIL', '')),
    ],
];
