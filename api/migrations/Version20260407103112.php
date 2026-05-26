<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407103112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE refresh_tokens (refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `session` (id BINARY(16) NOT NULL, token_hash VARCHAR(255) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_D044D5D4B3BC57DA (token_hash), INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id BINARY(16) NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, pin VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, avatar_url VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, last_login_at DATETIME DEFAULT NULL, pin_locked_until DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `session` ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `session` DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE `session`');
        $this->addSql('DROP TABLE `user`');
    }
}
