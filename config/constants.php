<?php

return [

    'roles' => [
        'admin' => 'Admin',
        'supervisor' => 'Supervisor',
        'user' => 'Empleado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos por rol (hardcodeados)
    | - Podés usar '*' para permitir acceso completo
    | - Lista de ejemplos de permisos. Cambiá/agregá los que necesites.
    |--------------------------------------------------------------------------
    */
    'permissions_by_role' => [
        'admin' => [
            '*', //Acceso completo
        ],

        'supervisor' => [
            'dashboard-admin',
            'users.view',
        ],

        'user' => [
            'dashboard-admin',
            'orders.view',
            'profile.edit',
        ],


    ],

    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    'twitterapiio' => [
        'base_url' => env('TWITTERAPIIIO_BASE_URL', 'https://api.twitterapi.io/twitter/tweet/advanced_search'),
        'api_key' => env('TWITTERAPIIIO_API_KEY', ''),

    ],
    'openai' => [
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1/'),
        'api_key' => env('OPENAI_API_KEY', ''),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2048),
        'version' => env('OPENAI_VERSION', 'gpt-3.5-turbo'),
    ]


];
