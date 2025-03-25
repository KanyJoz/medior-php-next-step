<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// ...
final class Version20250317132005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds constraints to animations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE animations ADD CONSTRAINT animations_season_check
                CHECK (season IN ('Autumn', 'Summer', 'Winter', 'Spring'));
        ");
        $this->addSql("
			ALTER TABLE animations ADD CONSTRAINT animations_year_check
			    CHECK (year BETWEEN 1900 AND date_part('year', now()));
        ");
        $this->addSql("
			ALTER TABLE animations ADD CONSTRAINT genres_length_check
			    CHECK (array_length(genres, 1) BETWEEN 1 AND 10);
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
			ALTER TABLE animations DROP CONSTRAINT IF EXISTS animations_season_check;
        ");
        $this->addSql("
			ALTER TABLE animations DROP CONSTRAINT IF EXISTS animations_year_check;
        ");
        $this->addSql("
			ALTER TABLE animations DROP CONSTRAINT IF EXISTS genres_length_check;
        ");
    }
}
