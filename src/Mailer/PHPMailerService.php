<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Mailer;

use Exception;
use KanyJoz\AniMerged\Mailer\DTO\HelloMail;
use KanyJoz\AniMerged\Mailer\DTO\WelcomeMail;
use KanyJoz\AniMerged\Mailer\Exception\MailerException;
use KanyJoz\AniMerged\Mailer\Exception\TemplateException;
use KanyJoz\AniMerged\Model\Token;
use KanyJoz\AniMerged\Model\User;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

// ...
readonly class PHPMailerService implements MailerInterface
{
    private const string SUB_PATH = 'emails' . DIRECTORY_SEPARATOR;

    public function __construct(
        private PHPMailer $mailer,
        private Twig $twig,
    ) {}

    /**
     * @throws Exception
     * @throws MailerException
     * @throws TemplateException
     */
    #[\Override]
    public function welcome(
        WelcomeMail $welcomeMail,
        User $user,
        Token $token
    ): void
    {
        if (!$this->mailer->addAddress(
                $welcomeMail->address, $welcomeMail->name)) {
            throw new MailerException('invalid mail address');
        }
        $this->mailer->Subject = $welcomeMail->subject;

        // We pass the $token to the welcome.txt and welcome.twig as well
        $this->mailer->isHTML();
        try {
            $htmlBody = $this->twig->fetch(
                    self::SUB_PATH . $welcomeMail->templateName . '.twig', [
                'user' => $user,
                'token' => $token,
            ]);
            $textBody = $this->twig->fetch(
                    self::SUB_PATH . $welcomeMail->templateName . '.txt', [
                'user' => $user,
                'token' => $token,
            ]);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new TemplateException('template parse failed',
                previous: $e);
        }
        $this->mailer->Body = $htmlBody;
        $this->mailer->AltBody = $textBody;

        if (!$this->mailer->send()) {
            throw new MailerException('failed sending mail');
        }
    }

    #[\Override]
    public function hello(HelloMail $mail): void {
        // Configure email
        if (!$this->mailer->addAddress($mail->address, $mail->name)) {
            throw new MailerException('invalid mail address');
        }
        $this->mailer->Subject = $mail->subject;

        // Set HTML and Text body
        $this->mailer->isHTML();
        try {
            $htmlBody = $this->twig->fetch(self::SUB_PATH . $mail->templateName . '.twig');
            $textBody = $this->twig->fetch(self::SUB_PATH . $mail->templateName . '.txt');
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new TemplateException('template parse failed', previous: $e);
        }
        $this->mailer->Body = $htmlBody;
        $this->mailer->AltBody = $textBody;

        // Send the email
        if (!$this->mailer->send()) {
            throw new MailerException('failed sending mail');
        }
    }
}