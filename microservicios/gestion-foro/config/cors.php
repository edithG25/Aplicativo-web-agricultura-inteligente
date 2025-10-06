<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    | Define las rutas donde se aplicará CORS. 
    | Normalmente en API se habilita en todas las rutas con '*'.
    */
    // 'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'paths' => [
        'api/citas*',
        'api/comentario*',
        'api/comentarios-tema*',
        'api/crear-cita*',
        'api/crear-comentario*',
        'api/temas*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    | Métodos HTTP que estarán permitidos desde otros microservicios.
    */
    'allowed_methods' => ['*'], // puedes limitar a ['GET', 'POST', 'PUT', 'DELETE']

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | Aquí colocas los dominios o puertos desde donde se conectará tu microservicio.
    | Ejemplo: foro en 8001 y autenticación en 8000 (con Laragon).
    */
    // 'allowed_origins' => [
    //     'http://localhost:8001', // Este microservicio Foro
    //     'http://127.0.0.1:8000',
    //     'http://127.0.0.1:8001/api/',
    // ],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:8000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    | Útil si quieres permitir por patrones (ejemplo *.tuservicio.com).
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    | Encabezados que se permiten en las solicitudes.
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    | Encabezados que pueden ser expuestos a las aplicaciones clientes.
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    | Tiempo en segundos que el navegador puede cachear la respuesta de CORS.
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    | Si se permitirá el uso de cookies o credenciales (tokens, JWT).
    */
    'supports_credentials' => true,

];
