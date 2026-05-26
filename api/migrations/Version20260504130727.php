<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504130727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session ADD refresh_token_hash VARCHAR(255) DEFAULT NULL, ADD refresh_token_expires_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D044D5D47288F9EC ON session (refresh_token_hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D044D5D47288F9EC ON `session`');
        $this->addSql('ALTER TABLE `session` DROP refresh_token_hash, DROP refresh_token_expires_at');
    }
}
