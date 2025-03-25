<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use DateInterval;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Model\Token;
use Random\RandomException;

// ...
interface TokenRepositoryInterface
{
    /**
     * @throws RandomException
     * @throws DatabaseException
     */
    public function newToken(
        int $userId,
        DateInterval $durationFromNow,
        string $scope
    ): Token;

    /**
     * @throws DatabaseException
     */
    public function insert(Token $token): void;

    /**
     * @throws DatabaseException
     */
    public function deleteAllForUser(
        string $scope,
        int $userId
    ): void;
}