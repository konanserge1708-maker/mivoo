<?php

return [
    'default' => env('PAYMENT_DRIVER', 'sandbox'),

    'currency' => 'XOF',

    'drivers' => [
        'sandbox' => [
            'enabled' => true,
        ],
        'jeko' => [
            'base_url' => env('JEKO_BASE_URL', 'https://api.jeko.africa'),
            'api_key' => env('JEKO_API_KEY'),
            'api_key_id' => env('JEKO_API_KEY_ID'),
            'store_id' => env('JEKO_STORE_ID'),
            'webhook_secret' => env('JEKO_WEBHOOK_SECRET'),
            /*
             | XOF : amountCents = montant_FCFA * 100 (centimes, multiple de 100).
             */
            'amount_multiplier' => 100,
        ],
    ],

    'methods' => [
        'wave' => 'Wave',
        'orange' => 'Orange Money',
        'mtn' => 'MTN Mobile Money',
        'moov' => 'Moov Money',
        'djamo' => 'Djamo',
    ],
];
