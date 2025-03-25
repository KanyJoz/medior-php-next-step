<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Controller;

use DateInterval;
use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Model\DateTimeHelperInterface;
use KanyJoz\AniMerged\Model\Token;
use KanyJoz\AniMerged\Model\User;
use KanyJoz\AniMerged\Repository\TokenRepositoryInterface;
use KanyJoz\AniMerged\Repository\UserRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use KanyJoz\AniMerged\Utils\StatusCode;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Random\RandomException;

readonly class TokenController
{
    public function __construct(
        private ResponseFormatter $formatter,
        private UserRepositoryInterface $users,
        private TokenRepositoryInterface $tokens,
    ) {}

    public function authenticate(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        // Read json input from Request
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->formatter
                ->failedParsing($response,
                    'failed to parse request body as json');
        }

        // Validate the incoming data
        $validatedUser = User::validateEmail($body);
        if (!$validatedUser->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedUser->error);
        }

        $validatedUser = User::validatePassword($body);
        if (!$validatedUser->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedUser->error);
        }

        // Get the user by email
        try {
            $user = $this->users->getByEmail($body['email']);
        } catch (ModelNotFoundException) {
            return $this->formatter
                ->invalidCredentials($response);
        } catch (DatabaseException|Exception $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Check the password
        if (!User::passwordMatches(
            $body['password'], $user->getPasswordHash())
        ) {
            return $this->formatter
                ->invalidCredentials($response);
        }

        // Create and Insert the token for the user about authentication
        try {
            $token = $this->tokens->newToken(
                $user->getId(),
                new DateInterval(DateTimeHelperInterface::ONE_DAY),
                Token::SCOPE_AUTHENTICATION
            );
        } catch (DatabaseException|RandomException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Send Response
        return $this->formatter
            ->writeJSON($response, $token->asJson(),
                StatusCode::CREATED(), 'authentication_token');
    }
}