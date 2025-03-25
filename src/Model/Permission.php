<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

// ...
class Permission
{
    // Properties
    private int $id;
    private string $code;

    // Default Values
    public function __construct()
    {
        $this->id = 0;
        $this->code = '';
    }

    // Static Factories
    public static function fromDatabase(array $queryResult): Permission
    {
        $permission = new Permission();

        $permission->setCode($queryResult['code']);

        return $permission;
    }

    // Business Logic, we will use it in the middleware
    public static function has(array $permissions, string $needle): bool
    {
        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            if ($permission->getCode() === $needle) {
                return true;
            }
        }

        return false;
    }

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Permission
    {
        $this->id = $id;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): Permission
    {
        $this->code = $code;
        return $this;
    }
}