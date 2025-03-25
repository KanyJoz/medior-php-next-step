<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Middleware;

use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Model\Token;
use KanyJoz\AniMerged\Repository\UserRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

// ...
readonly class AuthenticateMiddleware implements MiddlewareInterface
{
    // We need the Models for DB access and the ResponseFactoryInterface to send the Response
    public function __construct(
        private UserRepositoryInterface $users,
        private ResponseFactoryInterface $responseFactory,
        private ResponseFormatter $formatter,
    ) {}

    #[\Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Retrieve the authorization header from the Request: Bearer [token]
        $authorizationHeader = $request->getHeaderLine('Authorization');

        // If the header is empty we set an anonymous user and go to the next Middleware
        if ($authorizationHeader === '') {
            $request = $request->withAttribute('user', '');
            return $handler->handle($request);
        }

        // If the header does not follow the "Bearer [token]" format, user is not authenticated
        $headerParts = explode(' ', $authorizationHeader);
        if (count($headerParts) !== 2 || $headerParts[0] !== 'Bearer') {
            return $this->formatter
                ->invalidAuthenticationToken(
                    $this->responseFactory->createResponse());
        }

        // If the authentication token is not 26 characters long or blank, user is not authenticated
        $plainToken = $headerParts[1];
        $validatedToken = Token::validate($plainToken);
        if (!$validatedToken->valid) {
            return $this->formatter
                ->invalidAuthenticationToken(
                    $this->responseFactory->createResponse());
        }

        // Get the user for the token
        try {
            $user = $this->users->getForToken(Token::SCOPE_AUTHENTICATION, $plainToken);
        } catch (ModelNotFoundException) {
            return $this->formatter
                ->invalidAuthenticationToken(
                    $this->responseFactory->createResponse());
        } catch (DatabaseException|Exception $e) {
            return $this->formatter
                ->serverError($this->responseFactory->createResponse(), $request, $e);
        }

        // Add the User to the Request attributes
        $request = $request->withAttribute('user', $user);

        // Handler the request and get the response
        $response = $handler->handle($request);

        // Modify the headers of response and send it back
        return $response->withAddedHeader("Vary", "Authorization");
    }
}