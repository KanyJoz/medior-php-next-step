<?php

declare(strict_types=1);

namespace KanyJoz\Tests\Repository;

use KanyJoz\AniMerged\Model\Animation;
use KanyJoz\AniMerged\Model\Season;
use KanyJoz\AniMerged\Repository\AnimationPostgreSQLRepository;
use KanyJoz\AniMerged\Repository\AnimationRepositoryInterface;
use PDO;
use PHPUnit\Framework\TestCase;

class AnimationPostgreSQLRepositoryTest extends TestCase
{
    private PDO $pdo;
    private AnimationRepositoryInterface $animations;

    protected function setUp(): void
    {
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s;sslmode=%s",
            "localhost",
            "5432",
            "ani-merged-test",
            "postgres",
            "postgres",
            "disable",
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, options: $options);
        $this->animations = new AnimationPostgreSQLRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo->query("DELETE FROM animations;");
    }

    public function testInsertAndGet(): void
    {
        $title = 'Naruto';
        $year = 2002;
        $season = 'Autumn';
        $genres = ['shonen', 'action', 'ninja'];

        $animation = Animation::fromRequest([
            'title' => $title,
            'year' => $year,
            'season' => $season,
            'genres' => $genres,
        ]);

        $animation = $this->animations->insert($animation);

        $returnedAnimation = $this->animations->get($animation->getId());

        $this->assertSame($title, $returnedAnimation->getTitle());
        $this->assertSame($year, $returnedAnimation->getYear());
        $this->assertSame(Season::AU, $returnedAnimation->getSeason());
        $this->assertSame($genres, $returnedAnimation->getGenres());
    }
}