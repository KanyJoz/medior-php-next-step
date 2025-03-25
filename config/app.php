<?php

declare(strict_types=1);

$app_env = $_ENV['APP_ENV'];
$cors_trusted_origins = $_ENV['CORS_TRUSTED_ORIGINS'];
$cors_trusted_origins = explode(' ', $cors_trusted_origins);

// ...
return [
    'app' => [
        'environment' => $_ENV['APP_ENV'],
        'name' => $_ENV['APP_NAME'],
    ],

    'errors' => [
        'display' => $app_env === 'development',
        'use_logger' => true,
        'log_details' => true,
    ],

    // ...
    'twig' => [
        'template' => [
            'path' => TEMPLATES_PATH,
            'options' => [
                'cache' => TWIG_CACHE_PATH,
                'auto_reload' => $app_env === 'development',
            ],
        ],
    ],

    // ...
    'db' => [
        'pgsql' => [
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASSWORD'],
            'sslmode' => $_ENV['DB_SSL_MODE'],
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'redis' => [
            'connection' => [
                'scheme' => $_ENV['REDIS_SCHEME'],
                'host' => $_ENV['REDIS_HOST'],
                'port' => intval($_ENV['REDIS_PORT']),
            ],
            'config' => [
                'requests' => intval($_ENV['REDIS_REQUESTS']),
                'expiration' => intval($_ENV['REDIS_EXPIRATION']),
                'storage_key_format' => $_ENV['REDIS_STORAGE_KEY_FORMAT'],
            ],
        ],
    ],

    'mail' => [
        'mode' => $_ENV['MAIL_MODE'],
        'sender' => [
            'address' => $_ENV['MAIL_SENDER_ADDRESS'],
            'name' => $_ENV['MAIL_SENDER_NAME'],
        ],
        'smtp' => [
            'host' => $_ENV['SMTP_HOST'],
            'port' => intval($_ENV['SMTP_PORT']),
            'username' => $_ENV['SMTP_USERNAME'],
            'password' => $_ENV['SMTP_PASSWORD'],
        ],
    ],

    // ...
    // we exploded the env variable by the space
    // and saved it as an array in the Configuration
    'cors' => [
        'trusted_origins' => $cors_trusted_origins,
    ],
];
