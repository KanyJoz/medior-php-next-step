<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

// Load scripts: constants setter, autoloader, env loader
require_once dirname(__DIR__) . '/config/path_constants.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Setup ENV variables
$dotenv = require_once DOTENV_PATH;
$dotenv_rules = require_once DOTENV_RULES_PATH;
$dotenv_rules($dotenv);

// Require DI Container
/** @var ContainerInterface $container */
$container = require_once CONTAINER_PATH;
return $container;
