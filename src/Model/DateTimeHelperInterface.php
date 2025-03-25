<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

interface DateTimeHelperInterface
{
    public const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s.uP';
    public const string NORMAL_FORMAT = 'Y-m-d H:i:sP';

    public const string ONE_DAY = 'P1D';
    public const string TWO_DAYS = 'P2D';
    public const string THREE_DAYS = 'P3D';
}