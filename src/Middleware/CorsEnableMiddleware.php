<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use KanyJoz\AniMerged\Configuration;
use KanyJoz\AniMerged\Utils\HTTPMethod;
use KanyJoz\AniMerged\Utils\StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
readonly class CorsEnableMiddleware implements MiddlewareInterface
{
    // Inject Configuration so we can get the env variable we set up
    public function __construct(private Configuration $config) {}

    #[\Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get the values from the Request we need to verify if the request is preflight
        // Same check we did in the CheckForPreFlightRequestMiddleware
        $origin = $request->getHeaderLine('Origin');
        $method = $request->getMethod();
        $access = $request->getHeaderLine('Access-Control-Request-Method');

        $response = $handler->handle($request);

        // Add the "Vary: Access-Control-Request-Method" header as well
        $response = $response->withAddedHeader("Vary", "Origin");
        $response = $response->withAddedHeader("Vary", "Access-Control-Request-Method");

        if ($origin !== '') {
            foreach ($this->config->get('cors.trusted_origins') as $trustedOrigin) {
                if ($origin === $trustedOrigin) {
                    $response = $response->withHeader('Access-Control-Allow-Origin', $origin);

                    // Check if the request has the HTTP method OPTIONS and contains the
                    // "Access-Control-Request-Method" header. If it does, then we treat
                    // it as a preflight request.
                    if ($method === HTTPMethod::OPTIONS() && $access !== '') {
                        // Set the necessary preflight response headers, as discussed
                        // previously.
                        $response = $response->withHeader("Access-Control-Allow-Methods", "OPTIONS, PUT, PATCH, DELETE");
                        $response = $response->withHeader("Access-Control-Allow-Headers", "Authorization, Content-Type");

                        // Write the headers along with a 200 OK status and return from
                        // the middleware with no further action.
                        return $response->withStatus(StatusCode::OK());
                    }

                    break;
                }
            }
        }

        return $response;
    }
}