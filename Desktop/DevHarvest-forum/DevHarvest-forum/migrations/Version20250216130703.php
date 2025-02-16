<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216130703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parcelle_culture (parcelle_id INT NOT NULL, culture_id INT NOT NULL, INDEX IDX_E0F33A324433ED66 (parcelle_id), INDEX IDX_E0F33A32B108249D (culture_id), PRIMARY KEY(parcelle_id, culture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parcelle_culture ADD CONSTRAINT FK_E0F33A324433ED66 FOREIGN KEY (parcelle_id) REFERENCES parcelle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parcelle_culture ADD CONSTRAINT FK_E0F33A32B108249D FOREIGN KEY (culture_id) REFERENCES culture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parcelle ADD culture_actuelle_id INT DEFAULT NULL, ADD description LONGTEXT NOT NULL, ADD zone VARCHAR(255) NOT NULL, ADD superficie DOUBLE PRECISION NOT NULL, ADD prix_de_location DOUBLE PRECISION NOT NULL, ADD date_de_location DATE NOT NULL, ADD date_de_fin_location DATE NOT NULL, ADD etat VARCHAR(255) NOT NULL, ADD type_sol VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF63CF489F1 FOREIGN KEY (culture_actuelle_id) REFERENCES culture (id)');
        $this->addSql('CREATE INDEX IDX_C56E2CF63CF489F1 ON parcelle (culture_actuelle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parcelle_culture DROP FOREIGN KEY FK_E0F33A324433ED66');
        $this->addSql('ALTER TABLE parcelle_culture DROP FOREIGN KEY FK_E0F33A32B108249D');
        $this->addSql('DROP TABLE parcelle_culture');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF63CF489F1');
        $this->addSql('DROP INDEX IDX_C56E2CF63CF489F1 ON parcelle');
        $this->addSql('ALTER TABLE parcelle DROP culture_actuelle_id, DROP description, DROP zone, DROP superficie, DROP prix_de_location, DROP date_de_location, DROP date_de_fin_location, DROP etat, DROP type_sol');
    }
}
