<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Utils;

class Arr
{
    public static function fromStr(string $str): array
    {
        return explode(',', str_replace(['{', '}'], '', $str));
    }

    public static function toStr(array $arr): string
    {
        return '{' . implode(',', $arr) . '}';
    }
}