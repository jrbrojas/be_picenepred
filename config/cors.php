<?php

return [
    'paths'                => ['api/*', 'storage/*'],
    'allowed_methods'      => ['*'],
    'allowed_origins'      => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
    'allowed_headers'      => ['*'],
    'exposed_headers'      => [],
    'max_age'              => 3600,
    'supports_credentials' => false,
];