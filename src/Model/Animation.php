<?php

declare(strict_types=1);

namespace KanyJoz\AniMerged\Model;

use DateTimeImmutable;
use Exception;
use KanyJoz\AniMerged\DTO\Validated;
use KanyJoz\AniMerged\Validator\Validator;
use Override;

// ...
class Animation implements AsJsonInterface
{
    // Properties
    private int $id;
    private string $title;
    private int $year;
    private Season $season;
    private int $version;
    private array $genres;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    // Default Values
    public function __construct()
    {
        $this->id = 0;
        $this->title = '';
        $this->year = 0;
        $this->season = Season::NA;
        $this->version = 0;
        $this->genres = [];
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    // Factory Functions
    public static function fromRequest(array $requestBody): static
    {
        $animation = new Animation();
        $animation->setTitle($requestBody['title']);
        $animation->setYear($requestBody['year']);
        $animation->setSeason(Season::from($requestBody['season']));
        $animation->setGenres($requestBody['genres']);

        return $animation;
    }

    /**
     * @throws Exception
     */
    public static function fromDatabase(array $queryResult): static
    {
        $animation = new Animation();

        $animation->setId($queryResult['id']);
        $animation->setTitle($queryResult['title']);
        $animation->setYear($queryResult['year']);
        $animation->setSeason(Season::from($queryResult['season']));
        $animation->setVersion($queryResult['version']);
        $animation->setGenres($queryResult['genres']);
        $animation->setCreatedAt(new DateTimeImmutable($queryResult['created_at']));
        $animation->setUpdatedAt(new DateTimeImmutable($queryResult['updated_at']));

        return $animation;
    }

    // Validators
    public static function validate(array $requestBody): Validated
    {
        $v = new Validator();
        $v->check($v->notBlank($requestBody['title']), 'title', 'This field cannot be blank');
        $v->check($v->maxChars($requestBody['title'], 255), 'title', 'This field must be at most 255 characters long');

        $v->check(is_int($requestBody['year']), 'year', 'This field must be an integer');
        $requestBody['year'] = intval($requestBody['year']);
        $v->check($v->ge($requestBody['year'], 1900), 'year', 'This field must be at least 1900');
        $v->check($v->le($requestBody['year'], intval((new DateTimeImmutable())->format('Y'))), 'year', 'This field must be at most ' . (new DateTimeImmutable())->format('Y'));

        $v->check($v->notBlank($requestBody['season']), 'season', 'This field cannot be blank');
        $v->check($v->permitted(ucfirst($requestBody['season']), ['Winter', 'Summer', 'Spring', 'Autumn']), 'season', 'Possible values: Winter|Summer|Autumn|Spring');

        $v->check(is_array($requestBody['genres']), 'genres', 'This field must be a list');
        if (!is_array($requestBody['genres'])) {
            $requestBody['genres'] = [];
        }
        $v->check($v->ge(count($requestBody['genres']), 1), 'genres', 'This field must contain at least 1 element');
        $v->check($v->le(count($requestBody['genres']), 10), 'genres', 'This field must contain at most 10 elements');
        $v->check($v->unique($requestBody['genres']), 'genres', 'This field must not contain duplicate values');

        return new Validated($v->valid(), $v->firstError());
    }

    // ...
    public static function validatePartially(array $requestBody): Validated
    {
        $v = new Validator();

        if (isset($requestBody['title'])) {
            $v->check($v->notBlank($requestBody['title']), 'title', 'This field cannot be blank');
            $v->check($v->maxChars($requestBody['title'], 255), 'title', 'This field must be at most 255 characters long');
        }

        if (isset($requestBody['year'])) {
            $v->check(is_int($requestBody['year']), 'year', 'This field must be an integer');
            $requestBody['year'] = intval($requestBody['year']);
            $v->check($v->ge($requestBody['year'], 1900), 'year', 'This field must be at least 1900');
            $v->check($v->le($requestBody['year'], intval((new DateTimeImmutable())->format('Y'))), 'year', 'This field must be at most ' . (new DateTimeImmutable())->format('Y'));
        }

        if (isset($requestBody['season'])) {
            $v->check($v->notBlank($requestBody['season']), 'season', 'This field cannot be blank');
            $v->check($v->permitted(ucfirst($requestBody['season']), ['Winter', 'Summer', 'Spring', 'Autumn']), 'season', 'Possible values: Winter|Summer|Autumn|Spring');
        }

        if (isset($requestBody['genres'])) {
            $v->check(is_array($requestBody['genres']), 'genres', 'This field must be a list');
            if (!is_array($requestBody['genres'])) {
                $requestBody['genres'] = [];
            }
            $v->check($v->ge(count($requestBody['genres']), 1), 'genres', 'This field must contain at least 1 element');
            $v->check($v->le(count($requestBody['genres']), 10), 'genres', 'This field must contain at most 10 elements');
            $v->check($v->unique($requestBody['genres']), 'genres', 'This field must not contain duplicate values');
        }

        return new Validated($v->valid(), $v->firstError());
    }
    // ...

    // JSON
    #[Override]
    public function asJson(): array
    {
        // Mandatory
        $animation = [
            'id' => $this->id,
            'title' => $this->title,
            'version' => $this->version,
        ];

        // Omitempty
        if ($this->year !== 0) {
            $animation['year'] = $this->year;
        }

        if ($this->season !== Season::NA) {
            $animation['season'] = $this->season->value;
        }

        if ($this->genres !== []) {
            $animation['genres'] = $this->genres;
        }

        // Return array
        return $animation;
    }

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Animation
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Animation
    {
        $this->title = $title;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): Animation
    {
        $this->year = $year;
        return $this;
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): Animation
    {
        $this->season = $season;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): Animation
    {
        $this->version = $version;
        return $this;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function setGenres(array $genres): Animation
    {
        $this->genres = $genres;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Animation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): Animation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}