<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// ...
final class Version20250324131442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tokens table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS tokens (
                hash text PRIMARY KEY,
                user_id bigint NOT NULL REFERENCES users ON DELETE CASCADE,
                expiry timestamp(0) with time zone NOT NULL,
                scope text NOT NULL
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS tokens;");
    }
}
