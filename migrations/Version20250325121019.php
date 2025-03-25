<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// ...
final class Version20250325121019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates permission tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS permissions (
                id bigserial PRIMARY KEY,
                code text NOT NULL
            );
        ");

        $this->addSql("
            CREATE TABLE IF NOT EXISTS permissions_users (
                permission_id bigint NOT NULL REFERENCES permissions ON DELETE CASCADE,
                user_id bigint NOT NULL REFERENCES users ON DELETE CASCADE,
                PRIMARY KEY (permission_id, user_id)
            );
        ");

        $this->addSql("
            INSERT INTO permissions (code)
            VALUES 
                ('animations/read'),
                ('animations/write');
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS permissions_users;");
        $this->addSql("DROP TABLE IF EXISTS permissions;");
    }
}
