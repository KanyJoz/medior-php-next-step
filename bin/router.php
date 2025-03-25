<?php

declare(strict_types=1);

use KanyJoz\AniMerged\Controller\AnimationController;
use KanyJoz\AniMerged\Controller\DummyEmailController;
use KanyJoz\AniMerged\Controller\HealthCheckController;
use KanyJoz\AniMerged\Controller\TokenController;
use KanyJoz\AniMerged\Controller\UserController;
use KanyJoz\AniMerged\Middleware\RequireActivatedUserMiddleware;
use KanyJoz\AniMerged\Middleware\RequireAuthenticatedUserMiddleware;
use KanyJoz\AniMerged\Middleware\RequirePermissionMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

// ...
return function(App $app): void
{
    /** @var RequirePermissionMiddleware $permissionMiddleware */
    $permissionMiddleware = $app->getContainer()
        ->get(RequirePermissionMiddleware::class);

    // Version 1
    $app->group('/v1', function (RouteCollectorProxy $version) use ($permissionMiddleware) {
        // Dummy Remove Later
        $version->get('/emails/test', [DummyEmailController::class, 'test'])
            ->setName('emails:test');
        // ...

        // Healthcheck
        $version->get('/ping', [HealthCheckController::class, 'health'])
            ->setName('app:health');

        // Animations
        $version->group('/animations', function (RouteCollectorProxy $animations) use ($permissionMiddleware) {
            $animations->post('', [AnimationController::class, 'create'])
                ->setName('animations:create')
                ->add($permissionMiddleware->renew()->setGuard('animations/write'));
            $animations->get('/{id}', [AnimationController::class, 'show'])
                ->setName('animations:show')
                ->add($permissionMiddleware->renew()->setGuard('animations/read'));
            $animations->patch('/{id}', [AnimationController::class, 'update'])
                ->setName('animations:update')
                ->add($permissionMiddleware->renew()->setGuard('animations/write'));
            $animations->delete('/{id}', [AnimationController::class, 'destroy'])
                ->setName('animations:destroy')
                ->add($permissionMiddleware->renew()->setGuard('animations/write'));
            $animations->get('', [AnimationController::class, 'index'])
                ->setName('animations:index')
                ->add($permissionMiddleware->renew()->setGuard('animations/read'));
        })
            ->add(RequireActivatedUserMiddleware::class)
            ->add(RequireAuthenticatedUserMiddleware::class);
        // ...

        // Users
        $version->group('/users', function (RouteCollectorProxy $users) {
            $users->post('', [UserController::class, 'register'])
                ->setName('users:register');

            $users->put('/activated', [UserController::class, 'activate'])
                ->setName('users:activate');
        });

        // Tokens
        $version->group('/tokens', function (RouteCollectorProxy $users) {
            $users->post('/authentication', [TokenController::class, 'authenticate'])
                ->setName('tokens:authenticate');
        });
    });
};
