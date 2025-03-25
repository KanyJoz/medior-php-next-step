<?php

namespace KanyJoz\AniMerged\Middleware;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;

readonly class LogRequestMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $method = $request->getMethod();
        $uri = $request->getServerParams()['REQUEST_URI'];

        $this->logger->info('New Request Received', [
            'method' => $method,
            'uri' => $uri,
        ]);

        return $handler->handle($request);
    }
}
