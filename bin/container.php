<?php

declare(strict_types=1);

use DI\ContainerBuilder;

$container = new ContainerBuilder();
$container->addDefinitions(require_once BINDINGS_PATH);
return $container->build();

