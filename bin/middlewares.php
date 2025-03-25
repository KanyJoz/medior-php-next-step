<?php

declare(strict_types=1);

use KanyJoz\AniMerged\Middleware\AuthenticateMiddleware;
use KanyJoz\AniMerged\Middleware\CheckForPreFlightRequestMiddleware;
use KanyJoz\AniMerged\Middleware\CommonHeadersMiddleware;
use KanyJoz\AniMerged\Middleware\CorsEnableMiddleware;
use KanyJoz\AniMerged\Middleware\LogRequestMiddleware;
use KanyJoz\AniMerged\Middleware\RateLimiterMiddleware;
use Slim\App;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;

return function(App $app)
{
    $app->add(CommonHeadersMiddleware::class);
    $app->add(AuthenticateMiddleware::class);
    $app->add(RoutingMiddleware::class);
    $app->add(BodyParsingMiddleware::class);
    $app->add(LogRequestMiddleware::class);
    $app->add(RateLimiterMiddleware::class);
    $app->add(CheckForPreFlightRequestMiddleware::class);
    $app->add(CorsEnableMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
