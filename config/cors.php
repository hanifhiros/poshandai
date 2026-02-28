<?php

return [


    'allowed_origins_patterns' => [],

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => ['*'], // atau ['http://localhost:*', 'http://127.0.0.1:*']
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],


    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
