<?php

declare(strict_types=1);

use KanyJoz\AniMerged\Configuration;
use KanyJoz\AniMerged\Mailer\MailerInterface;
use KanyJoz\AniMerged\Mailer\PHPMailerService;
use KanyJoz\AniMerged\Middleware\RateLimiterMiddleware;
use KanyJoz\AniMerged\Repository\AnimationPostgreSQLRepository;
use KanyJoz\AniMerged\Repository\AnimationRepositoryInterface;
use KanyJoz\AniMerged\Repository\PermissionsPostgreSQLRepository;
use KanyJoz\AniMerged\Repository\PermissionsRepositoryInterface;
use KanyJoz\AniMerged\Repository\TokenPostgreSQLRepository;
use KanyJoz\AniMerged\Repository\TokenRepositoryInterface;
use KanyJoz\AniMerged\Repository\UserPostgreSQLRepository;
use KanyJoz\AniMerged\Repository\UserRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPMailer\PHPMailer\PHPMailer;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Slim\Views\Twig;

// ...
return [
    App::class => function(ContainerInterface $container) {
        return AppFactory::createFromContainer($container);
    },

    Configuration::class => function() {
        return new Configuration(require_once APP_CONFIG_PATH);
    },

    LoggerInterface::class => function() {
        $logger = new Logger('app');

        // Formats date of log entry
        $dateFormat = 'Y-m-d H:i:s';
        $formatter = new LineFormatter(dateFormat: $dateFormat);

        // Handles DEBUG and INFO levels
        $debugHandler = new StreamHandler(INFO_LOG_PATH);
        $debugHandler->setLevel(Level::Debug);
        $debugHandler->setFormatter($formatter);
        $logger->pushHandler($debugHandler);

        // Handles WARNING and ERROR levels
        $errorHandler = new StreamHandler(ERROR_LOG_PATH);
        $errorHandler->setLevel(Level::Warning);
        $errorHandler->setBubble(false);
        $errorHandler->setFormatter($formatter);
        $logger->pushHandler($errorHandler);

        return $logger;
    },

    // ResponseFactory
    ResponseFactoryInterface::class => function() {
        return new Psr17Factory();
    },

    ErrorMiddleware::class => function(
        App $app,
        Configuration $config,
        LoggerInterface $logger
    ) {
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $config->get('errors.display'),
            $config->get('errors.use_logger'),
            $config->get('errors.log_details'),
            $logger
        );
    },
    RoutingMiddleware::class => function(App $app) {
        return new RoutingMiddleware(
            $app->getRouteResolver(),
            $app->getRouteCollector()->getRouteParser()
        );
    },
    BodyParsingMiddleware::class => function() {
        $bodyParsers = [];

        return new BodyParsingMiddleware($bodyParsers);
    },

    // ...
    RateLimiterMiddleware::class => function(
        ResponseFactoryInterface $responseFactory,
        ClientInterface $client,
        ResponseFormatter $formatter,
        Configuration $config
    ) {
        return (new RateLimiterMiddleware($responseFactory, $client, $formatter))
            ->setRequests($config->get('db.redis.config.requests'))
            ->setExpiration($config->get('db.redis.config.expiration'))
            ->setStorageKeyFormatString($config->get('db.redis.config.storage_key_format'));
    },

    // ...
    PDO::class => function(Configuration $config) {
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s;sslmode=%s",
            $config->get('db.pgsql.host'),
            $config->get('db.pgsql.port'),
            $config->get('db.pgsql.dbname'),
            $config->get('db.pgsql.user'),
            $config->get('db.pgsql.pass'),
            $config->get('db.pgsql.sslmode'),
        );

        $options = $config->get('db.pgsql.options');

        return new PDO($dsn, options: $options);
    },

    // ...
    AnimationRepositoryInterface::class => function(PDO $pdo) {
        return new AnimationPostgreSQLRepository($pdo);
    },

    // ...
    Twig::class => function(Configuration $config) {
        return Twig::create(
            $config->get('twig.template.path'),
            $config->get('twig.template.options')
        );
    },

    // ...

    // Mailer
    PHPMailer::class => function(Configuration $config) {
        $mail = new PHPMailer(true); // Enable exceptions

        // SMTP Configuration
        if ($config->get('mail.mode') === 'smtp') {
            $mail->isSMTP();
            $mail->SMTPAuth = true;

            $mail->Host = $config->get('mail.smtp.host');
            $mail->Username = $config->get('mail.smtp.username');
            $mail->Password = $config->get('mail.smtp.password');
            $mail->Port = $config->get('mail.smtp.port');
            $mail->setFrom(
                $config->get('mail.sender.address'),
                $config->get('mail.sender.name')
            );

            return $mail;
        }


        throw new Exception(
            sprintf('Unknown mail mode: %s',
                $config->get('mail.mode')));
    },

    // ...
    MailerInterface::class => function(PHPMailer $PHPMailer, Twig $twig) {
        return new PHPMailerService($PHPMailer, $twig);
    },

    // ...
    // (Redis) Database
    ClientInterface::class => function(Configuration $config) {
        return new Client([
            'scheme' => $config->get('db.redis.connection.scheme'),
            'host' => $config->get('db.redis.connection.host'),
            'port' => $config->get('db.redis.connection.port'),
        ]);
    },

    // ...
    UserRepositoryInterface::class => function(PDO $pdo) {
        return new UserPostgreSQLRepository($pdo);
    },

    // ...
    TokenRepositoryInterface::class => function(PDO $pdo) {
        return new TokenPostgreSQLRepository($pdo);
    },

    // ...
    PermissionsRepositoryInterface::class => function(PDO $pdo) {
        return new PermissionsPostgreSQLRepository($pdo);
    },
];
