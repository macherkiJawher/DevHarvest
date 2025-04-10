<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303033445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE culture ADD description LONGTEXT NOT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD date_plantation DATE NOT NULL, ADD date_recolte DATE NOT NULL, ADD saison VARCHAR(255) NOT NULL, ADD categorie VARCHAR(255) NOT NULL, ADD quantite DOUBLE PRECISION NOT NULL, DROP date_semis, DROP date_recolte_prevue, CHANGE type_culture nom VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE parcelle ADD culture_actuelle_id INT DEFAULT NULL, ADD description LONGTEXT NOT NULL, ADD prix_de_location DOUBLE PRECISION NOT NULL, ADD date_de_location DATE NOT NULL, ADD date_de_fin_location DATE NOT NULL, ADD etat VARCHAR(255) NOT NULL, ADD type_sol VARCHAR(255) NOT NULL, ADD image VARCHAR(255) DEFAULT NULL, CHANGE localisation zone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF63CF489F1 FOREIGN KEY (culture_actuelle_id) REFERENCES culture (id)');
        $this->addSql('CREATE INDEX IDX_C56E2CF63CF489F1 ON parcelle (culture_actuelle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE culture ADD type_culture VARCHAR(255) NOT NULL, ADD date_semis DATE NOT NULL, ADD date_recolte_prevue DATE NOT NULL, DROP nom, DROP description, DROP image, DROP date_plantation, DROP date_recolte, DROP saison, DROP categorie, DROP quantite');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF63CF489F1');
        $this->addSql('DROP INDEX IDX_C56E2CF63CF489F1 ON parcelle');
        $this->addSql('ALTER TABLE parcelle ADD localisation VARCHAR(255) NOT NULL, DROP culture_actuelle_id, DROP description, DROP zone, DROP prix_de_location, DROP date_de_location, DROP date_de_fin_location, DROP etat, DROP type_sol, DROP image');
    }
}
