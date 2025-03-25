<?php

declare(strict_types=1);

use Dotenv\Dotenv;

// ...
return function (Dotenv $dotenv) {
    // APP
    $dotenv->required('APP_NAME');
    $dotenv->required('APP_ENV')
        ->allowedValues(['development', 'production']);

    // ...
    // Database
    $dotenv->required('DB_HOST');
    $dotenv->required('DB_PORT');
    $dotenv->required('DB_NAME');
    $dotenv->required('DB_USER');
    $dotenv->required('DB_PASSWORD');
    $dotenv->required('DB_SSL_MODE')
        ->allowedValues(['disable', 'enable']);

    // ...
    // Email
    $dotenv->required('MAIL_MODE')
        ->allowedValues(['smtp']);
    $dotenv->required('MAIL_SENDER_ADDRESS');
    $dotenv->required('MAIL_SENDER_NAME');

    // Email SMTP
    $dotenv->required('SMTP_HOST');
    $dotenv->required('SMTP_PORT');
    $dotenv->required('SMTP_USERNAME');
    $dotenv->required('SMTP_PASSWORD');

    // ...
    // Redis
    $dotenv->required('REDIS_HOST');
    $dotenv->required('REDIS_PORT');
    $dotenv->required('REDIS_SCHEME')
        ->allowedValues(['tcp', 'tls']);
    $dotenv->required('REDIS_REQUESTS');
    $dotenv->required('REDIS_EXPIRATION');
    $dotenv->required('REDIS_STORAGE_KEY_FORMAT');

    // ...
    // FE APP
    $dotenv->required('CORS_TRUSTED_ORIGINS');
};

