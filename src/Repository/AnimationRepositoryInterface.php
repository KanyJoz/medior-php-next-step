<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Model\Animation;
use KanyJoz\AniMerged\Model\Filters;

// ...
interface AnimationRepositoryInterface
{
    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function getAll(
        string $title,
        array $genres,
        Filters $filters
    ): array;

    /**
     * @throws ReturningException
     * @throws DatabaseException
     * @throws Exception
     */
    public function insert(Animation $animation): Animation;

    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    public function get(int $id): Animation;

    /**
     * @throws DatabaseException
     * @throws ReturningException
     * @throws EditConflictException
     */
    public function update(Animation $animation): Animation;

    /**
     * @throws DatabaseException
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void;
}