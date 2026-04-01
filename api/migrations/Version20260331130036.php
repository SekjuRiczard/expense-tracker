<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331130036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD username VARCHAR(100) NOT NULL, DROP first_name, DROP last_name, DROP default_currency, DROP language, DROP timezone, DROP notification_settings, CHANGE pin pin VARCHAR(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` ADD last_name VARCHAR(100) NOT NULL, ADD default_currency VARCHAR(3) NOT NULL, ADD language VARCHAR(5) NOT NULL, ADD timezone VARCHAR(50) NOT NULL, ADD notification_settings JSON NOT NULL, CHANGE pin pin VARCHAR(255) DEFAULT NULL, CHANGE username first_name VARCHAR(100) NOT NULL');
    }
}
