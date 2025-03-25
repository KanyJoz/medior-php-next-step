<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Validator;

class Validator
{
    public const string EMAIL_PATTERN
        = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

    private array $errors = [];

    public function valid(): bool
    {
        return count($this->errors) === 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function addError(string $key, string $msg): void
    {
        if (!array_key_exists($key, $this->errors)) {
            $this->errors[$key] = $msg;
        }
    }

    public function check(bool $ok, string $key, string $msg): void
    {
        if (!$ok) {
            $this->addError($key, $msg);
        }
    }

    public function firstError(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $firstErrorMsg = $this->errors()[array_key_first($this->errors())];
        $firstErrorField = array_key_first($this->errors());

        return $firstErrorField . ': ' . $firstErrorMsg;
    }

    // Pure validators, return bool
    public function notBlank(string $value): bool
    {
        return trim($value) !== '';
    }

    public function maxChars(string $value, int $n): bool
    {
        return strlen($value) <= $n;
    }

    public function minChars(string $value, int $n): bool
    {
        return strlen($value) >= $n;
    }

    public function exactChars(string $value, int $n): bool
    {
        return strlen($value) === $n;
    }

    public function ne(int $number, int $to): bool
    {
        return $number !== $to;
    }

    public function ge(int $number, int $than): bool
    {
        return $number >= $than;
    }

    public function gt(int $number, int $than): bool
    {
        return $number > $than;
    }

    public function le(int $number, int $than): bool
    {
        return $number <= $than;
    }

    public function between(int $number, int $ge, int $le): bool
    {
        return $this->ge($number, $ge) && $this->le($number, $le);
    }

    public function permitted(mixed $value, array $in): bool
    {
        return in_array($value, $in);
    }

    public function matches(string $value, string $pattern): bool
    {
        return (bool)preg_match($pattern, $value);
    }

    public function unique(array $values): bool
    {
        return count($values) === count(array_unique($values));
    }
}