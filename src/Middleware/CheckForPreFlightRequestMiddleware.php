<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Utils\HTTPMethod;
use KanyJoz\AniMerged\Utils\StatusCode;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
readonly class CheckForPreFlightRequestMiddleware
    implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {}

    #[\Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $origin = $request->getHeaderLine('Origin');
        $method = $request->getMethod();
        $access = $request->getHeaderLine('Access-Control-Request-Method');

        // If it is a Preflight request we return 200
        // -> Routing middleware will not see the request, so can't error out on OPTIONS route missing
        // -> CORS middleware on the way out will set the appropriate response headers though
        if ($origin !== '' && $method === HTTPMethod::OPTIONS() && $access !== '') {
            return $this->responseFactory->createResponse(StatusCode::OK());
        }

        // Otherwise we skip this middleware
        return $handler->handle($request);
    }
}