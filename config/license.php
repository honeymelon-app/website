<?php

declare(strict_types=1);

return [
    'signing' => [
        'public_key' => env('LICENSE_SIGNING_PUBLIC_KEY'),
        /**
         * Ed25519 keypair secret (base64 encoded). Only needed on the web marketing app.
         * Do not commit this value; store it in infrastructure secrets.
         */
        'private_key' => env('LICENSE_SIGNING_PRIVATE_KEY'),
    ],

    'key' => [
        /**
         * Number of characters per group in the human-readable license key.
         */
        'group_size' => env('LICENSE_GROUP_SIZE', 5),
    ],
];
