<?php

return [

    // 'paths' => ['api/*', 'carts', 'carts/deleteproducts{productId}', 'carts/items', 'carts/items/{productId}',  'orders', 'orders/seller/orders', 'orders/user/orders', 'orders/{orderId}', 'orders/{orderId}/status'],
    'paths' => [
        'api/carts*',
        'api/orders*',
        'api/returns*',
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
