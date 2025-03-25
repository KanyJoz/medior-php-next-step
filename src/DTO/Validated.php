<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\DTO;

readonly class Validated
{
    public function __construct(
        public bool $valid,
        public string $error
    ) {}
}