<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Utils;

use JsonException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class ResponseFormatter
{
    // Let's inject the LoggerInterface
    public function __construct(private LoggerInterface $logger) {}

    public function writeJSON(
        Response $response,
        array|string $data = [],
        int $statusCode = 200,
        string $envelope = '',
        array $headers = [],
    ): Response
    {
        // If additional headers are given we set them on the response
        foreach ($headers as $headerKey => $headerValue) {
            $response = $response->withHeader($headerKey, $headerValue);
        }

        // If there is a top level envelope we wrap the data in it
        if (trim($envelope) !== '') {
            $data = [$envelope => $data];
        }

        // Write Content-Type header, set status code if given
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus($statusCode);

        // Write Response body
        try {
            $body = json_encode(
                $data,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_THROW_ON_ERROR
            );

            // If for some reason the data can't be encoded to JSON
            // we want the app to 500 before that we log the issue
            if ($body === false) {
                $this->logger->error('data can not be converted to JSON');
                return $response->withStatus(StatusCode::INTERNAL_SERVER_ERROR());
            }

            $response->getBody()->write($body);
        } catch (JsonException $e) {
            // We do the same if some unexpected happens
            $this->logger->error($e->getMessage());
            return $response->withStatus(StatusCode::INTERNAL_SERVER_ERROR());
        }

        // Return the modified response
        return $response;
    }

    private function logError(Request $request, Throwable $ex): void
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'];
        $method = $request->getMethod();
        $uri = $request->getServerParams()['REQUEST_URI'];

        $this->logger-> error($ex->getMessage(), [
            'ip' => $ip,
            'method' => $method,
            'uri' => $uri,
        ]);
    }

    // Helper that specialized error responses can use
    private function errorResponse(
        Response $response,
        array|string $msg,
        int $statusCode,
        string $envelope = 'error'
    ): Response
    {
        return $this->writeJSON($response, $msg, $statusCode, $envelope);
    }

    public function badRequest(Response $response): Response
    {
        $msg = 'bad request';
        return $this->errorResponse($response,
            $msg, StatusCode::BAD_REQUEST());
    }

    // We will use it for bad login attempts
    public function invalidCredentials(Response $response): Response
    {
        $msg = 'invalid authentication credentials';
        return $this->errorResponse($response,
            $msg, StatusCode::UNAUTHORIZED());
    }

    // Later we will develop a token based authentication system
    // This is for when they did supply a token, but it is invalid
    public function invalidAuthenticationToken(Response $response): Response
    {
        $response = $response->withHeader('WWW-Authenticate', 'Bearer');

        $msg = 'invalid or missing authentication token';
        return $this->errorResponse($response,
            $msg, StatusCode::UNAUTHORIZED());
    }

    // This is when they did not even supply a
    // token for a Route that is protected
    public function authenticationRequired(Response $response): Response
    {
        $msg = 'you must be authenticated to access this resource';
        return $this->errorResponse($response,
            $msg, StatusCode::UNAUTHORIZED());
    }

    // If the user is not activated
    public function inactiveAccount(Response $response): Response
    {
        $msg = 'your user account must be activated';
        return $this->errorResponse($response, $msg, StatusCode::FORBIDDEN());
    }

    // If the user does not have permission to access a resource
    public function notPermitted(Response $response): Response
    {
        $msg = 'missing necessary permission to access resrouce';
        return $this->errorResponse($response,
		$msg, StatusCode::FORBIDDEN());
    }

    public function notFound(Response $response): Response
    {
        $msg = 'the requested resource could not be found';
        return $this->errorResponse($response,
		$msg, StatusCode::NOT_FOUND());
    }

    public function methodNotAllowed(Response $response, Request $request): Response
    {
        $msg = sprintf('the %s method is not supported for this resource',
		 $request->getMethod());
        return $this->errorResponse($response,
		$msg, StatusCode::METHOD_NOT_ALLOWED());
    }

    public function editConflict(Response $response): Response
    {
        $msg = 'concurrency conflict';
        return $this->errorResponse($response,
            $msg, StatusCode::CONFLICT());
    }

    public function failedValidation(
        Response $response,
        array|string $msg
    ): Response
    {
        return $this->errorResponse($response,
            $msg, StatusCode::UNPROCESSABLE_ENTITY());
    }

    public function failedParsing(Response $response, array|string $msg): Response
    {
        return $this->errorResponse($response,
            $msg, StatusCode::UNPROCESSABLE_ENTITY());
    }

    // And we will also implement a rate limiting with Redis
    public function rateLimitExceeded(Response $response): Response
    {
        $msg = 'rate limit exceeded';
        return $this->errorResponse($response,
            $msg, StatusCode::TOO_MANY_REQUESTS());
    }

    // And if we actually have a 500 error, so it is code problem, we need that additional logging to debug it
    public function serverError(
        Response $response,
        Request $request,
        Throwable $ex
    ): Response
    {
        $this->logError($request, $ex);

        $msg = 'the server encountered a problem';
        return $this->errorResponse($response,
		$msg, StatusCode::INTERNAL_SERVER_ERROR());
    }
}