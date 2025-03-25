<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
readonly class RequireAuthenticatedUserMiddleware implements MiddlewareInterface
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
        // Get the User from request attributes
        $user = $request->getAttribute('user');

        // To access this route the user should be authenticated
        if ($user === '') {
            return $this->formatter
                ->authenticationRequired(
                    $this->responseFactory->createResponse());
        }

        // If authenticated we let it use the resource
        return $handler->handle($request);
    }
}