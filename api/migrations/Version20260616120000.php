<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add demo data batch tracking tables for persistent demo-data status.
 */
final class Version20260616120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add demo_data_batch and demo_data_record tables for persistent demo-data tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE demo_data_batch (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) NOT NULL, seed INT NOT NULL, status VARCHAR(20) NOT NULL, wallets_count INT NOT NULL, budgets_count INT NOT NULL, transactions_count INT NOT NULL, generated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', cleared_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DEMO_BATCH_USER (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE demo_data_record (id INT AUTO_INCREMENT NOT NULL, batch_id INT NOT NULL, entity_class VARCHAR(255) NOT NULL, entity_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DEMO_RECORD_BATCH (batch_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE demo_data_batch ADD CONSTRAINT FK_DEMO_BATCH_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demo_data_record ADD CONSTRAINT FK_DEMO_RECORD_BATCH FOREIGN KEY (batch_id) REFERENCES demo_data_batch (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo_data_record DROP FOREIGN KEY FK_DEMO_RECORD_BATCH');
        $this->addSql('ALTER TABLE demo_data_batch DROP FOREIGN KEY FK_DEMO_BATCH_USER');
        $this->addSql('DROP TABLE demo_data_record');
        $this->addSql('DROP TABLE demo_data_batch');
    }
}
