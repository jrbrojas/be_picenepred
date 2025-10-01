<?php

return [
    'paths' => ['api/*', 'storage/*'],
    'allowed_methods'   => ['*'],
    'allowed_origins'   => ['http://localhost:5177', 'http://127.0.0.1:5177'],
    'allowed_headers'   => ['*'],
    'exposed_headers'   => [],
    'max_age'           => 3600,
    'supports_credentials' => false,
];
