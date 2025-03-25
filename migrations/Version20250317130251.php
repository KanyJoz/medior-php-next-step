<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// ...
final class Version20250317130251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates animations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS animations (
                id BIGSERIAL PRIMARY KEY,  
                title TEXT NOT NULL,
                year INTEGER NOT NULL,
                season TEXT NOT NULL,
                version INTEGER NOT NULL DEFAULT 0,
                genres TEXT[] NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW()
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS animations;');
    }
}
