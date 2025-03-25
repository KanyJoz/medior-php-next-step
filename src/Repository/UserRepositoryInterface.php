<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\DuplicateEmailException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Model\User;

// ...
interface UserRepositoryInterface
{
    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    public function getForToken(string $scope, string $plainTextToken): User;
    // ...

    /**
     * @throws DatabaseException
     * @throws ReturningException
     * @throws DuplicateEmailException
     * @throws Exception
     */
    public function insert(User $user): User;

    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    public function getByEmail(string $email): User;

    /**
     * @throws DatabaseException
     * @throws EditConflictException
     * @throws ReturningException
     */
    public function update(User $user): void;
}