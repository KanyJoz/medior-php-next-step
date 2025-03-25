<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Model\Permission;
use KanyJoz\AniMerged\Model\User;
use KanyJoz\AniMerged\Repository\PermissionsRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
class RequirePermissionMiddleware implements MiddlewareInterface
{
    private string $guard = '';

    public function __construct(
        private readonly PermissionsRepositoryInterface $permissions,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ResponseFormatter $formatter,
    ) {}

    #[\Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get the User from request attributes,
        // it should be of type User at this point
        /** @var User $user */
        $user = $request->getAttribute('user');

        // Get all permissions for given User
        try {
            $permissions = $this->permissions
                ->getAllForUser($user->getId());
        } catch (DatabaseException $e) {
            return $this->formatter
                ->serverError($this->responseFactory
                    ->createResponse(), $request, $e);
        }

        // Check if the permissions of the user include the guard permission or not
        if (!Permission::has($permissions, $this->guard)) {
            return $this->formatter
                ->notPermitted($this->responseFactory->createResponse());
        }

        // If the user has Permission we let through the request
        return $handler->handle($request);
    }

    public function renew(): RequirePermissionMiddleware
    {
        return new static($this->permissions, $this->responseFactory, $this->formatter);
    }

    public function setGuard(string $guard): RequirePermissionMiddleware
    {
        $this->guard = $guard;

        return $this;
    }
}