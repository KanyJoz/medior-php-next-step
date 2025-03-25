<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

// ...
use DateTimeImmutable;
use Exception;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Exception\DuplicateEmailException;
use KanyJoz\AniMerged\Exception\EditConflictException;
use KanyJoz\AniMerged\Exception\ModelNotFoundException;
use KanyJoz\AniMerged\Exception\ReturningException;
use KanyJoz\AniMerged\Model\DateTimeHelperInterface;
use KanyJoz\AniMerged\Model\User;
use Throwable;

// ...
readonly class UserPostgreSQLRepository implements UserRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    #[\Override]
    public function getForToken(string $scope, string $plainTextToken): User
    {
        $sql = 'SELECT users.id, users.created_at, users.updated_at,
                    users.name, users.email, users.password_hash,
                    users.activated, users.version
                FROM users
                INNER JOIN tokens
                ON users.id = tokens.user_id
                WHERE tokens.hash = :tokenHash
                AND tokens.scope = :scope 
                AND tokens.expiry > :now';

        // hash the plainText token, because we only store hash in the DB
        $tokenHash = hash('sha256', $plainTextToken);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'tokenHash' => $tokenHash,
                'scope' => $scope,
                'now' => (new DateTimeImmutable())
                    ->format(DateTimeHelperInterface::NORMAL_FORMAT),
            ]);

            $user = $stmt->fetch();
            if ($user === false) {
                throw new ModelNotFoundException('user not found');
            }
        } catch (ModelNotFoundException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException('user getForToken() failed', previous: $ex);
        }

        return User::fromDatabase($user);
    }

    /**
     * @throws DatabaseException
     * @throws ReturningException
     * @throws DuplicateEmailException
     * @throws Exception
     */
    #[\Override]
    public function insert(User $user): User
    {
        $sql = 'INSERT INTO users (email, name, password_hash, activated)
                VALUES (:email, :name, :passwordHash, :activated) 
                RETURNING id, created_at, updated_at, version';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'passwordHash' => $user->getPasswordHash(),
                'activated' => $user->isActivated(),
            ]);

            $data = $stmt->fetch();
            if ($data === false) {
                throw new ReturningException(
                    'RETURNING id, created_at, updated_at, version SQL failed');
            }

            $this->pdo->commit();
        } catch (ReturningException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        } catch (Throwable $ex) {
            $this->pdo->rollBack();

            // here we triage on the error
            if ($ex->getCode() === '23505'
                && str_contains($ex->getMessage(),
                    'duplicate key value violates unique constraint "users_email_key"')
            ) {
                throw new DuplicateEmailException(
                    'user insert failed() on duplication of emails', previous: $ex);
            }

            throw new DatabaseException('user insert failed()', previous: $ex);
        }

        $user->setId($data['id']);
        $user->setCreatedAt(new DateTimeImmutable($data['created_at']));
        $user->setUpdatedAt(new DateTimeImmutable($data['updated_at']));
        $user->setVersion($data['version']);

        return $user;
    }

    /**
     * @throws ModelNotFoundException
     * @throws DatabaseException
     * @throws Exception
     */
    #[\Override]
    public function getByEmail(string $email): User
    {
        $sql = 'SELECT id, created_at, updated_at,
                    name, email, password_hash, activated, version
                FROM users
                WHERE email = :email';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch();
            if ($user === false) {
                throw new ModelNotFoundException('user not found');
            }
        } catch (ModelNotFoundException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException('user getByEmail() failed', previous: $ex);
        }

        return User::fromDatabase($user);
    }

    /**
     * @throws DatabaseException
     * @throws EditConflictException
     * @throws ReturningException
     */
    #[\Override]
    public function update(User $user): void
    {
        // Already taking care of optimistic locking
        $sql = 'UPDATE users 
                SET name = :name, email = :email, password_hash = :passwordHash,
                    activated = :activated, version = version + 1
                WHERE id = :id AND version = :version
                RETURNING version';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'passwordHash' => $user->getPasswordHash(),
                'activated' => $user->isActivated(),
                'id' => $user->getId(),
                'version' => $user->getVersion(),
            ]);

            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected === 0) {
                throw new EditConflictException('edit conflict for user');
            }

            $data = $stmt->fetch();
            if ($data === false) {
                throw new ReturningException('RETURNING version SQL failed');
            }

            $this->pdo->commit();
        } catch (ReturningException|EditConflictException $ex) {
            $this->pdo->rollBack();
            throw $ex;
        } catch (Throwable $ex) {
            $this->pdo->rollBack();

            throw new DatabaseException('user update() failed', previous: $ex);
        }
    }
}