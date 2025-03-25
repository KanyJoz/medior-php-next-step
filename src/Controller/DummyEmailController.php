<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Controller;

use KanyJoz\AniMerged\Mailer\DTO\HelloMail;
use KanyJoz\AniMerged\Mailer\Exception\MailerException;
use KanyJoz\AniMerged\Mailer\Exception\TemplateException;
use KanyJoz\AniMerged\Mailer\MailerInterface;
use KanyJoz\AniMerged\Utils\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// ...
readonly class DummyEmailController
{
    // We will inject the Service here later
    public function __construct(
        private MailerInterface $mailer,
        private ResponseFormatter $formatter
    ) { }

    // We will use this route to send a test email
    public function test(
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $helloMail = new HelloMail(
            'guest@example.com',
            'Guest User',
            'Hello at AniMerged',
            'hello'
        );

        try {
            $this->mailer->hello($helloMail);
        } catch (MailerException|TemplateException|\Exception $e) {
            return $this->formatter->serverError($response, $request, $e);
        }

        $response->getBody()->write('Email sent');
        return $response;
    }
}