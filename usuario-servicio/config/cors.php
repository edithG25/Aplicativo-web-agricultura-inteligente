 <?php

return [

    // 'paths' => ['api/*', 'login', 'logout', 'register', 'productos', 'profile'],
    'paths' => [
        'api/login',
        'api/logout',
        'api/profile*',
        'api/register',
        'api/validate',
        'api/vendedores',
        'api/vendedores/*',
        'api/usuarios/*',
        'sanctum/csrf-cookie'
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
