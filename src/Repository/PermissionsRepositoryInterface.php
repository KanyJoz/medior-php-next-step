<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use KanyJoz\AniMerged\Exception\DatabaseException;

interface PermissionsRepositoryInterface
{
    /**
     * @throws DatabaseException
     */
    public function getAllForUser(int $userId): array;

    /**
     * @throws DatabaseException
     */
    public function addForUser(int $userId, array $codes): void;
}