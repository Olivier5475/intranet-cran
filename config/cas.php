<?php
// config/cas.php

return [
    'host' => env('CAS_HOST', 'votre-serveur-cas.com'),
    'port' => (int) env('CAS_PORT', 443),
    'uri' => env('CAS_URI', ''),
    'ca_cert_path' => env('CAS_CA_CERT_PATH', false), // Mettez le chemin '/path/to/cacert.pem'
    'debug' => env('CAS_DEBUG', false),
];
