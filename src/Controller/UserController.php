<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Controller;

use DateInterval;
use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\DuplicateEmailException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Mailer\DTO\WelcomeMail;
use KanyJoz\AniMerged\Mailer\Exception\MailerException;
use KanyJoz\AniMerged\Mailer\Exception\TemplateException;
use KanyJoz\AniMerged\Mailer\MailerInterface;
use KanyJoz\AniMerged\Model\DateTimeHelperInterface;
use KanyJoz\AniMerged\Model\Token;
use KanyJoz\AniMerged\Model\User;
use KanyJoz\AniMerged\Repository\PermissionsRepositoryInterface;
use KanyJoz\AniMerged\Repository\TokenRepositoryInterface;
use KanyJoz\AniMerged\Repository\UserRepositoryInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use KanyJoz\AniMerged\Utils\StatusCode;
use Random\RandomException;

// ...
readonly class UserController
{
    // We inject the new repo
    public function __construct(
        private ResponseFormatter $formatter,
        private UserRepositoryInterface $users,
        private TokenRepositoryInterface $tokens,
        private MailerInterface $mailer,
        private PermissionsRepositoryInterface $permissions,
    ) {}

    public function activate(Request $request, Response $response, array $args): Response
    {
        // Read json input from Request
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->formatter
                ->failedParsing($response,
                    'failed to parse request body as json');
        }

        // Validate the incoming data
        $validatedToken = Token::validate($body['token']);
        if (!$validatedToken->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedToken->error);
        }

        // Get the user for the token
        try {
            $user = $this->users
                ->getForToken(Token::SCOPE_ACTIVATION, $body['token']);
        } catch (ModelNotFoundException) {
            return $this->formatter
                ->failedValidation($response, 'invalid or expired activation token');
        } catch (DatabaseException|Exception $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Activate the user
        $user->setActivated(1);

        // Update the user
        try {
            $this->users->update($user);
        } catch (EditConflictException) {
            return $this->formatter
                ->editConflict($response);
        } catch (ReturningException|DatabaseException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Delete all tokens for the user on successful update
        try {
            $this->tokens
                ->deleteAllForUser(Token::SCOPE_ACTIVATION, $user->getId());
        } catch (DatabaseException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Send Response
        return $this->formatter
            ->writeJSON($response, $user->asJson(), envelope: 'user');
    }

    // ...
    public function register(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->formatter
                ->failedParsing($response,
                    'failed to parse request body as json');
        }

        $validatedUser = User::validate($body);
        if (!$validatedUser->valid) {
            return $this->formatter
                ->failedValidation($response, $validatedUser->error);
        }

        $body['activated'] = 0;
        $user = User::fromRequest($body);

        try {
            $user = $this->users->insert($user);
        } catch (DuplicateEmailException) {
            return $this->formatter->
            failedValidation($response,
                'a user with this email address already exists');
        } catch (DatabaseException|ReturningException|Exception $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        // Here we add the permissions for the new User
        try {
            $this->permissions
                ->addForUser($user->getId(), ['animations/read']);
        } catch (DatabaseException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        try {
            $token = $this->tokens->newToken(
                $user->getId(),
                new DateInterval(DateTimeHelperInterface::THREE_DAYS),
                Token::SCOPE_ACTIVATION
            );
        } catch (DatabaseException|RandomException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        try {
            $this->mailer->welcome(
                new WelcomeMail(
                    $user->getEmail(),
                    $user->getName(),
                    'Welcome to AniMerged!',
                    'welcome'
                ),
                $user,
                $token
            );
        } catch (MailerException|Exception|TemplateException $e) {
            return $this->formatter
                ->serverError($response, $request, $e);
        }

        return $this->formatter
            ->writeJSON($response, $user->asJson(),
                StatusCode::CREATED(), 'user');
    }
}