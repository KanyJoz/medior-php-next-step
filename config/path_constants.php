<?php

declare(strict_types=1);

// root
const ROOT_PATH = __DIR__ . '/..';

// bin
const BIN_PATH = ROOT_PATH . '/bin';
const CONTAINER_PATH = BIN_PATH . '/container.php';
const DOTENV_PATH = BIN_PATH . '/dotenv.php';
const ROUTER_PATH = BIN_PATH . '/router.php';
const MIDDLEWARES_PATH = BIN_PATH . '/middlewares.php';

// config
const CONFIG_PATH = ROOT_PATH . '/config';
const BINDINGS_PATH = CONFIG_PATH . '/bindings.php';
const DOTENV_RULES_PATH = CONFIG_PATH . '/dotenv_rules.php';
const APP_CONFIG_PATH = CONFIG_PATH . '/app.php';

// migrations
const MIGRATIONS_PATH = ROOT_PATH . '/migrations';

// public
const PUBLIC_PATH = ROOT_PATH . '/public';

// templates
const TEMPLATES_PATH = ROOT_PATH . '/templates';

// tmp
const TMP_PATH = ROOT_PATH . '/tmp';
const TWIG_CACHE_PATH = TMP_PATH . '/twig-cache';

// var
const VAR_PATH = ROOT_PATH . '/var';
const LOG_PATH = VAR_PATH . '/log';
const INFO_LOG_PATH = LOG_PATH . '/info.log';
const ERROR_LOG_PATH = LOG_PATH . '/error.log';

