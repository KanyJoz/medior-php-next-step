<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Utils;

enum HTTPMethod: string
{
    case options = 'OPTIONS';

    public static function OPTIONS(): string
    {
        return self::options->value;
    }
}