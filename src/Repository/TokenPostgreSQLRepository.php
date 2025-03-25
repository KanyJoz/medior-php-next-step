<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Repository;

// ...
use DateInterval;
use KanyJoz\AniMerged\Exception\DatabaseException;
use KanyJoz\AniMerged\Model\DateTimeHelperInterface;
use KanyJoz\AniMerged\Model\Token;
use PDO;
use Random\RandomException;
use Throwable;

readonly class TokenPostgreSQLRepository implements TokenRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @throws RandomException
     * @throws DatabaseException
     */
    public function newToken(
        int $userId,
        DateInterval $durationFromNow,
        string $scope
    ): Token
    {
        $token = Token::generate($userId, $durationFromNow, $scope);

        $this->insert($token);

        return $token;
    }

    /**
     * @throws DatabaseException
     */
    public function insert(Token $token): void
    {
        $insertSQL = 'INSERT INTO tokens (hash, user_id, expiry, scope)
                      VALUES (:tokenHash, :userId, :expiry, :scope)';

        try {
            $stmt = $this->pdo->prepare($insertSQL);
            $stmt->execute([
                'tokenHash' => $token->getTokenHash(),
                'userId' => $token->getUserId(),
                'expiry' => $token->getExpiry()
                    ->format(DateTimeHelperInterface::NORMAL_FORMAT),
                'scope' => $token->getScope(),
            ]);

        } catch (Throwable $ex) {
            throw new DatabaseException('token insert() failed', previous: $ex);
        }
    }

    /**
     * @throws DatabaseException
     */
    public function deleteAllForUser(string $scope, int $userId): void
    {
        $deleteSQL = 'DELETE FROM tokens 
                      WHERE scope = :scope AND user_id = :userId';

        try {
            $stmt = $this->pdo->prepare($deleteSQL);
            $stmt->execute([
                'scope' => $scope,
                'userId' => $userId,
            ]);

        } catch (Throwable $ex) {
            throw new DatabaseException('token delete() failed', previous: $ex);
        }
    }
}