<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250324073934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS users (
                id bigserial PRIMARY KEY,
                name text NOT NULL,
                email text UNIQUE NOT NULL,
                password_hash text NOT NULL,
                activated bool NOT NULL DEFAULT false,
                version integer NOT NULL DEFAULT 0,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW()
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS users;");
    }
}
