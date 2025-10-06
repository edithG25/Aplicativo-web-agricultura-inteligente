<?php

return [

    // 'patahs' => ['api/*', 'metodos-pago', 'pagos', 'metodos-pago{metodos_pago}' ],
    'paths' => [
        'api/metodos-pago*',
        'api/pagos*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
