<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Model\User;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
readonly class RequireActivatedUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFormatter $formatter,
        private ResponseFactoryInterface $responseFactory
    ) {}

    #[\Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get the User from request attributes,
        // it should be of type User at this point, but we can make sure
        /** @var User|string $user */
        $user = $request->getAttribute('user');

        if (is_string($user)) {
            return $this->formatter
                ->inactiveAccount(
                    $this->responseFactory->createResponse());
        }

        // To access this route the user should be activated already
        if (!$user->isActivated()) {
            return $this->formatter
                ->inactiveAccount(
                    $this->responseFactory->createResponse());
        }

        // If authenticated and activated, we let it use the resource
        return $handler->handle($request);
    }
}