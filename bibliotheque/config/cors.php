<?php

return [
    // Chemins où CORS s'applique
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    // Origines autorisées (ton frontend)
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,
    // Autorise les cookies cross-origin 
    //Sans supports_credentials => true, le navigateur refuse d'envoyer les cookies. 
    'supports_credentials' => true,
];
