<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

use DateTimeImmutable;
use Exception;
use KanyJoz\AniMerged\DTO\Validated;
use KanyJoz\AniMerged\Validator\Validator;
use Override;

class User implements AsJsonInterface
{
    // Properties
    // We won't store the password in the database
    // But we can store it temporary in memory
    // So I added a field for it
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private string $passwordHash;
    private int $activated;
    private int $version;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    // Default Values
    public function __construct()
    {
        $this->id = 0;
        $this->email = '';
        $this->name = '';
        $this->password = '';
        $this->passwordHash = '';
        $this->activated = 0;
        $this->version = 0;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    // Factory Functions
    public static function fromRequest(array $requestBody): User
    {
        $user = new User();
        $user->setEmail($requestBody['email']);
        $user->setPassword($requestBody['password']);
        $user->setName($requestBody['name']);
        $user->setActivated($requestBody['activated']);
        $user->setPasswordHash(User::hashPassword($requestBody['password']));

        return $user;
    }

    /**
     * @throws Exception
     */
    public static function fromDatabase(array $queryResult): User
    {
        $user = new User();

        $user->setId($queryResult['id']);
        $user->setName($queryResult['name']);
        $user->setEmail($queryResult['email']);
        $user->setVersion($queryResult['version']);
        $user->setPasswordHash($queryResult['password_hash']);
        // Convert from bool to int when the User comes from DB
        $user->setActivated(
            $queryResult['activated'] === false ? 0 : 1);
        $user->setCreatedAt(
            new DateTimeImmutable($queryResult['created_at']));
        $user->setUpdatedAt(
            new DateTimeImmutable($queryResult['updated_at']));

        return $user;
    }

    // JSON
    #[Override]
    public function asJson(): array
    {
        // Mandatory
        return [
            'id' => $this->id,
            // Using the helper interface we created before
            'created_at' => $this->createdAt
                ->format(DateTimeHelperInterface::NORMAL_FORMAT),
            'updated_at' => $this->updatedAt
                ->format(DateTimeHelperInterface::NORMAL_FORMAT),
            'name' => $this->name,
            'email' => $this->email,
            'activated' => $this->activated,
        ];
    }

    // ...
    // Validators
    public static function validateEmail(array $requestBody): Validated
    {
        $v = new Validator();

        $v->check($v->notBlank($requestBody['email']),
            'email', 'must be provided');
        $v->check($v->matches($requestBody['email'],
            Validator::EMAIL_PATTERN), 'email',
            'must be a valid email address');

        return new Validated($v->valid(), $v->firstError());
    }

    public static function validatePassword(array $requestBody): Validated
    {
        $v = new Validator();

        $v->check($v->notBlank($requestBody['password']),
            'password', 'must be provided');
        $v->check($v->minChars($requestBody['password'], 8),
            'password', 'must be at least 8 characters long');
        $v->check($v->maxChars($requestBody['password'], 72),
            'password', 'must not be more than 72 characters long');

        return new Validated($v->valid(), $v->firstError());
    }

    public static function validate(array $requestBody): Validated
    {
        $validatedUser = self::validateEmail($requestBody);
        if (!$validatedUser->valid) {
            return new Validated(
                $validatedUser->valid, $validatedUser->error);
        }

        $validatedUser = self::validatePassword($requestBody);
        if (!$validatedUser->valid) {
            return new Validated(
                $validatedUser->valid, $validatedUser->error);
        }

        $v = new Validator();

        $v->check($v->notBlank($requestBody['name']),
            'name', 'must be provided');
        $v->check($v->maxChars($requestBody['name'],
            255), 'name', 'must not be more than 255 characters long');

        return new Validated($v->valid(), $v->firstError());
    }
    // ...

    // Creates password_hash from plain text password string
    public static function hashPassword(string $password): string
    {
        return password_hash(
            $password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    // Verifies password
    public static function passwordMatches(
        string $password,
        string $hashedPassword
    ): bool
    {
        return password_verify(
            $password, $hashedPassword);
    }

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): User
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function isActivated(): int
    {
        return $this->activated;
    }

    public function setActivated(int $activated): User
    {
        $this->activated = $activated;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): User
    {
        $this->version = $version;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}