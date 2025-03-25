<?php

namespace KanyJoz\AniMerged\Middleware;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CommonHeadersMiddleware implements MiddlewareInterface
{
    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        $response = $response->withHeader(
            'Content-Security-Policy',
            "default-src 'self'; style-src 'self'"
        );
        $response = $response->withHeader(
            'Referrer-Policy',
            'origin-when-cross-origin'
        );
        $response = $response->withHeader(
            'X-Content-Type-Options',
            'nosniff'
        );
        $response = $response->withHeader('X-Frame-Options', 'deny');
        return $response->withHeader('X-XSS-Protection', '0');
    }
}
