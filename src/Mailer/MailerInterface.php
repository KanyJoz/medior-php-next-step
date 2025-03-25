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

// ...
interface MailerInterface
{
    /**
     * @throws Exception
     * @throws MailerException
     * @throws TemplateException
     */
    public function hello(HelloMail $mail): void;

    /**
     * @throws Exception
     * @throws MailerException
     * @throws TemplateException
     */
    public function welcome(
        WelcomeMail $welcomeMail,
        User $user,
        Token $token
    ): void;
}