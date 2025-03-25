<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Override;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

// ...
class RateLimiterMiddleware implements MiddlewareInterface
{
    // Properties with default Values
    private int $requests = 30;
    private int $expiration = 60; // seconds
    private string $storageKeyFormatString = 'rate:%s:requests';

    // We need the ResponseFactoryInterface
    // to send back a 429 Response
    // And the Redis client to talk to redis
    // And the ResponseFormatter
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ClientInterface $redis,
        private readonly ResponseFormatter $formatter,
    ) {}

    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get the IP address of the user
        $identifier = $request->getServerParams()['REMOTE_ADDR'];

        // If the set Rate limit is exceeded we return with error code 429
        if ($this->hasExceededRateLimit($identifier)) {
            return $this->
                formatter->rateLimitExceeded(
                    $this->responseFactory->createResponse());
        }

        // Otherwise we increment the number of requests for the user with this IP address
        $this->incrementRequestCount($identifier);

        // And forward the request
        return $handler->handle($request);
    }

    // Business Logic
    private function hasExceededRateLimit(string $identifier): bool
    {
        if ($this->redis->get($this->getStorageKey($identifier)) >= $this->requests) {
            return true;
        }

        return false;
    }

    private function incrementRequestCount(string $identifier): void
    {
        $this->redis->incr($this->getStorageKey($identifier));

        $this->redis->expire($this->getStorageKey($identifier), $this->expiration);
    }


    private function getStorageKey(string $identifier): string
    {
        return sprintf($this->storageKeyFormatString, $identifier);
    }

    // Setters
    public function setRequests(int $requests): RateLimiterMiddleware
    {
        $this->requests = $requests;

        return $this;
    }

    public function setExpiration(int $expiration): RateLimiterMiddleware
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function setStorageKeyFormatString(string $storageKeyFormatString): RateLimiterMiddleware
    {
        $this->storageKeyFormatString = $storageKeyFormatString;

        return $this;
    }
}