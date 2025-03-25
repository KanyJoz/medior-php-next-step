<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use KanyJoz\AniMerged\Configuration;
use Psr\Container\ContainerInterface;

// Container from bootstrap_cli with the bindings and env variables loaded
/** @var ContainerInterface $container */
$container = require_once dirname(__DIR__) . '/bin/bootstrap_cli.php';

/** @var Configuration $config */
$config = $container->get(Configuration::class);

// Load Doctrine migrations config we configured before
$migrationsConfig = new PhpFile('config/migrations.php');

// Expose database Connection for migrations like we did with PDO
$conn = DriverManager::getConnection([
    'driver' => 'pdo_pgsql',
    'host' => $config->get('db.pgsql.host'),
    'port' => $config->get('db.pgsql.port'),
    'dbname' => $config->get('db.pgsql.dbname'),
    'user' => $config->get('db.pgsql.user'),
    'password' => $config->get('db.pgsql.pass'),
]);

return DependencyFactory::fromConnection($migrationsConfig, new ExistingConnection($conn));

