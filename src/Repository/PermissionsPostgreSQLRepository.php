<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Model\Permission;
use KanyJoz\AniMerged\Utils\Arr;
use PDO;
use Throwable;

// ...
readonly class PermissionsPostgreSQLRepository
    implements PermissionsRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    /**
     * @throws DatabaseException
     */
    #[\Override]
    public function getAllForUser(int $userId): array
    {
        $sql = 'SELECT permissions.code
				FROM permissions
				INNER JOIN permissions_users
				    ON permissions_users.permission_id = permissions.id
				INNER JOIN users
				    ON permissions_users.user_id = users.id
				WHERE users.id = :userId';

        try {
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'userId' => $userId,
            ]);

            $data = $stmt->fetchAll();
        } catch (Throwable $ex) {
            throw new DatabaseException(
                'permission getAllForUser() failed',
                    previous: $ex);
        }

        $permissions = [];

        foreach ($data as $row) {
            $permission = Permission::fromDatabase($row);

            $permissions[] = $permission;
        }

        return $permissions;
    }

    // ...
    /**
     * @throws DatabaseException
     */
    #[\Override]
    public function addForUser(int $userId, array $codes): void
    {
        // This will create id pairs
        // Will pair all the permission codes to the one userId we give
        // Then pastes them into the join table
        $sql = 'INSERT INTO permissions_users
                SELECT permissions.id, :userID 
                FROM permissions WHERE permissions.code = ANY(:permissions)';

        $permissions = Arr::toStr($codes);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'userID' => $userId,
                'permissions' => $permissions,
            ]);
        } catch (Throwable $ex) {
            throw new DatabaseException(
                'permission addForUser addForUser()',
                previous: $ex);
        }
    }
}