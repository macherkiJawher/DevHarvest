<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216091617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grange ADD zone_id INT NOT NULL');
        $this->addSql('ALTER TABLE grange ADD CONSTRAINT FK_F19633159F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('CREATE INDEX IDX_F19633159F2C3FAB ON grange (zone_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grange DROP FOREIGN KEY FK_F19633159F2C3FAB');
        $this->addSql('DROP INDEX IDX_F19633159F2C3FAB ON grange');
        $this->addSql('ALTER TABLE grange DROP zone_id');
    }
}
