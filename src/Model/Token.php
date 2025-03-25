<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

use DateInterval;
use DateTimeImmutable;
use KanyJoz\AniMerged\DTO\Validated;
use KanyJoz\AniMerged\Validator\Validator;
use Override;
use Random\RandomException;
use Selective\Base32\Base32;

// ...
class Token implements AsJsonInterface
{
    // Scopes, we want to see if the token we generate is to activate the user (switch activated from 0 to 1 in database)
    // or user is already activated and we want to check if the user is also authenticated
    public const string SCOPE_ACTIVATION = 'activation';
    public const string SCOPE_AUTHENTICATION = 'authentication';

    // Properties, we store the id of the user the token belongs to, the plainText version is also stored despite not having a database record
    // You can store anything with a default value and never use it, what suits you
    private int $userId;
    private string $plainText;
    private string $tokenHash;
    private DateTimeImmutable $expiry;
    private string $scope;

    // Default values
    public function __construct()
    {
        $this->userId = 0;
        $this->plainText = '';
        $this->tokenHash = '';
        $this->expiry = new DateTimeImmutable();
        $this->scope = '';
    }

    /**
     * @throws RandomException
     */
    public static function generate(
        int $userId,
        DateInterval $durationFromNow,
        string $scope
    ): Token
    {
        // Create a Token object with default values and set all args we receive
        $token = new Token();
        $token->setUserId($userId);
        $token->setExpiry((new DateTimeImmutable())
            ->add($durationFromNow));
        $token->setScope($scope);

        // We create 16 random bytes
        $randomBytes = random_bytes(16);

        // We use the base32 package to create encode these bytes into base32 string without padding and we set it as the plain text property
        // This is what we will send in the email to the user
        $base32 = new Base32();
        $token->setPlainText($base32
            ->encode($randomBytes, false));

        // We then create a hash from this, this is what we store in the database and we also set it to create a full Token object
        $token->setTokenHash(hash('sha256',
            $token->getPlainText()));

        // So from 16 bytes -> base32 string -> hashed string of the base32 string of the 16 bytes
        return $token;
    }

    // Validators
    public static function validate(string $token): Validated
    {
        $v = new Validator();

        // This validation is for the plainText token string
        // If you create 16 random bytes and encode them into base32 AND you don't use padding, it will always be 26 characters
        // So we check against that
        $v->check($v->notBlank($token),
            'token', 'must be provided');
        $v->check($v->exactChars($token, 26),
            'token', 'must be 26 bytes long');

        return new Validated($v->valid(), $v->firstError());
    }

    // JSON
    #[Override]
    public function asJson(): array
    {
        // Mandatory, We send back the plainText token and the expiration of the token only
        return [
            'token' => $this->plainText,
            'expiry' => $this->expiry
                ->format(DateTimeHelperInterface::TIMESTAMP_FORMAT),
        ];
    }

    // Setters and Getters
    // ...
    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Token
    {
        $this->userId = $userId;
        return $this;
    }

    public function getPlainText(): string
    {
        return $this->plainText;
    }

    public function setPlainText(string $plainText): Token
    {
        $this->plainText = $plainText;
        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): Token
    {
        $this->tokenHash = $tokenHash;
        return $this;
    }

    public function getExpiry(): DateTimeImmutable
    {
        return $this->expiry;
    }

    public function setExpiry(DateTimeImmutable $expiry): Token
    {
        $this->expiry = $expiry;
        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): Token
    {
        $this->scope = $scope;
        return $this;
    }
}