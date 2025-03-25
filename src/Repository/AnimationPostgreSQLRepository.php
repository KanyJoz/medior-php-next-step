<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use DateTimeImmutable;
use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Model\Animation;
use KanyJoz\AniMerged\Model\Filters;
use KanyJoz\AniMerged\Utils\Arr;
use Throwable;

// ...
readonly class AnimationPostgreSQLRepository implements AnimationRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function getAll(
        string $title,
        array $genres,
        Filters $filters
    ): array
    {
        $sql = sprintf(
                "SELECT id, title, year, season, genres, created_at, updated_at, version
                FROM animations
                WHERE (to_tsvector('simple', title) @@ plainto_tsquery('simple', :title) OR :title = '') 
                AND (genres @> :genres OR :genres = '{}') 
                ORDER BY %s %s, id ASC
                LIMIT :limit OFFSET :offset",
            $filters->sortColumn(), $filters->sortDirection());

        // We need the array string from the PHP array this time for genres
        $genresStr = Arr::toStr($genres);

        try {
            // We need prepare() because we use placeholders
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'title' => $title,
                'genres' => $genresStr,
                'limit' => $filters->limit(),
                'offset' => $filters->offset(),
            ]);

            $data = $stmt->fetchAll();
        } catch (Throwable $ex) {
            throw new DatabaseException('animation getAll() failed',
                previous: $ex);
        }

        // ... no changes from here

        $animations = [];

        foreach ($data as $row) {
            $row['genres'] = Arr::fromStr($row['genres']);

            $animation = Animation::fromDatabase($row);

            $animations[] = $animation;
        }

        return $animations;
    }

    /**
     * @throws ReturningException
     * @throws DatabaseException
     * @throws Exception
     */
    #[\Override]
    public function insert(Animation $animation): Animation
    {
        $sql = 'INSERT INTO animations (title, year, season, genres) 
            VALUES (:title, :year, :season, :genres)
            RETURNING id, created_at, updated_at, version';

        $genres = Arr::toStr($animation->getGenres());

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'title' => $animation->getTitle(),
                'year' => $animation->getYear(),
                'season' => $animation->getSeason()->value,
                'genres' => $genres,
            ]);

            $data = $stmt->fetch();
            if ($data === false) {
                throw new ReturningException('RETURNING id, created_at, updated_at version SQL failed');
            }

            $this->pdo->commit();
        } catch (ReturningException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        } catch (Throwable $ex) {
            $this->pdo->rollBack();
            throw new DatabaseException('animations insert failed()', previous: $ex);
        }

        $animation->setId($data['id']);
        $animation->setCreatedAt(new DateTimeImmutable($data['created_at']));
        $animation->setUpdatedAt(new DateTimeImmutable($data['updated_at']));
        $animation->setVersion($data['version']);

        return $animation;
    }

    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    #[\Override]
    public function get(int $id): Animation
    {
        if ($id < 1) {
            throw new ModelNotFoundException('animation not found');
        }

        $sql = 'SELECT id, title, year, season, version, genres, created_at, updated_at
            FROM animations
            WHERE id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            $data = $stmt->fetch();
            if ($data === false) {
                throw new ModelNotFoundException('animation not found');
            }
        } catch (ModelNotFoundException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException('animations get() failed', previous: $ex);
        }

        // We convert the string array to a PHP array using our helper
        $data['genres'] = Arr::fromStr($data['genres']);

        return Animation::fromDatabase($data);
    }

    // ...

    /**
     * @throws DatabaseException
     * @throws ReturningException
     * @throws EditConflictException
     */
    #[\Override]
    public function update(Animation $animation): Animation
    {
        // We allow the UPDATE if the version is not changed
        $sql = 'UPDATE animations 
            SET title = :title, year = :year, season = :season,
                genres = :genres, version = version + 1
            WHERE id = :id AND version = :version
            RETURNING version';

        $genres = Arr::toStr($animation->getGenres());

        try {
            $this->pdo->beginTransaction();

            // Add new bound value here
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'title' => $animation->getTitle(),
                'year' => $animation->getYear(),
                'season' => $animation->getSeason()->value,
                'genres' => $genres,
                'id' => $animation->getId(),
                'version' => $animation->getVersion(),
            ]);

            // if 0 rows were updated, there was a conflict with version
            // throw new custom Exception
            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected === 0) {
                throw new EditConflictException('edit conflict for animation');
            }

            $data = $stmt->fetch();
            if ($data === false) {
                throw new ReturningException('RETURNING version SQL failed');
            }

            $this->pdo->commit();
        } catch (ReturningException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        } catch (Throwable $ex) {
            $this->pdo->rollBack();

            throw new DatabaseException('animation update failed()', previous: $ex);
        }

        $animation->setVersion($data['version']);

        return $animation;
    }

    /**
     * @throws DatabaseException
     * @throws ModelNotFoundException
     */
    #[\Override]
    public function delete(int $id): void
    {
        if ($id < 1) {
            throw new ModelNotFoundException('animation not found');
        }

        $sql = 'DELETE FROM animations
			WHERE id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected === 0) {
                throw new ModelNotFoundException('animation not found');
            }
        } catch (ModelNotFoundException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException(
                'animation delete() failed', previous: $ex);
        }
    }
}