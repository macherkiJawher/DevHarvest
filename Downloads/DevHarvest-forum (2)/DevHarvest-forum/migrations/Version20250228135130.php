<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228135130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parcelle_culture DROP FOREIGN KEY FK_E0F33A324433ED66');
        $this->addSql('ALTER TABLE parcelle_culture DROP FOREIGN KEY FK_E0F33A32B108249D');
        $this->addSql('DROP TABLE parcelle_culture');
        $this->addSql('ALTER TABLE commentaire CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parcelle_culture (parcelle_id INT NOT NULL, culture_id INT NOT NULL, INDEX IDX_E0F33A324433ED66 (parcelle_id), INDEX IDX_E0F33A32B108249D (culture_id), PRIMARY KEY(parcelle_id, culture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE parcelle_culture ADD CONSTRAINT FK_E0F33A324433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parcelle_culture ADD CONSTRAINT FK_E0F33A32B108249D FOREIGN KEY (culture_id) REFERENCES culture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC60BB6FE6');
        $this->addSql('ALTER TABLE commentaire CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D60BB6FE6');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
    }
}
