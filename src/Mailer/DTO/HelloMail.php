<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Mailer\DTO;

readonly class HelloMail
{
    public function __construct(
        public string $address,
        public string $name,
        public string $subject,
        public string $templateName
    ) {}
}