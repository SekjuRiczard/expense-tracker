<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602130100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add version column to wallet for optimistic locking on balance updates.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `wallet` ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `wallet` DROP version');
    }
}
