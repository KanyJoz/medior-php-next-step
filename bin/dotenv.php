<?php

declare(strict_types=1);

$dotenv = Dotenv\Dotenv::createMutable(ROOT_PATH);
$dotenv->load();
return $dotenv;

