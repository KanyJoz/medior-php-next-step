<?php

declare(strict_types=1);

use Slim\App;
use Psr\Container\ContainerInterface;

require_once dirname(__DIR__) . '/config/path_constants.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = require_once DOTENV_PATH;
$dotenv_rules = require_once DOTENV_RULES_PATH;
$dotenv_rules($dotenv);

/** @var ContainerInterface $container */
$container = require_once CONTAINER_PATH;
$router = require_once ROUTER_PATH;
$middlewares = require_once MIDDLEWARES_PATH;

/** @var App $app */
$app = $container->get(App::class);
$router($app);
$middlewares($app);

return $app;

