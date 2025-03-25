<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

// ...
enum Season: string
{
    case WI = 'Winter';
    case SP = 'Spring';
    case AU = 'Autumn';
    case SM = 'Summer';
    case NA = 'N/A';
}