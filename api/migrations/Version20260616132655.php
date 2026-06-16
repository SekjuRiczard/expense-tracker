<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260616132655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demo_data_batch CHANGE generated_at generated_at DATETIME NOT NULL, CHANGE cleared_at cleared_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE demo_data_batch RENAME INDEX idx_demo_batch_user TO IDX_BA8E54A5A76ED395');
        $this->addSql('ALTER TABLE demo_data_record CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE demo_data_record RENAME INDEX idx_demo_record_batch TO IDX_BC745B3DF39EBE7A');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demo_data_batch CHANGE generated_at generated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE cleared_at cleared_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE demo_data_batch RENAME INDEX idx_ba8e54a5a76ed395 TO IDX_DEMO_BATCH_USER');
        $this->addSql('ALTER TABLE demo_data_record CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE demo_data_record RENAME INDEX idx_bc745b3df39ebe7a TO IDX_DEMO_RECORD_BATCH');
    }
}
