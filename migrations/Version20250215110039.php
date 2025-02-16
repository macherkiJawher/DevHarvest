<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215110039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail_commande (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, produit_id INT NOT NULL, quantite INT NOT NULL, soustotal NUMERIC(10, 2) NOT NULL, INDEX IDX_98344FA682EA2E54 (commande_id), INDEX IDX_98344FA6F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_enum (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA682EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA6F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE commande ADD datecommande DATETIME NOT NULL, ADD total NUMERIC(10, 2) NOT NULL, DROP date_de_creation, DROP montant, CHANGE status etat VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD nom VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD prixunitaire NUMERIC(10, 2) NOT NULL, ADD quantitestock INT NOT NULL, ADD image VARCHAR(255) NOT NULL, ADD categorie VARCHAR(255) NOT NULL, DROP nom_produit, DROP quantite, DROP prix_unitaire');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA682EA2E54');
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA6F347EFB');
        $this->addSql('DROP TABLE detail_commande');
        $this->addSql('DROP TABLE role_enum');
        $this->addSql('ALTER TABLE commande ADD date_de_creation DATE NOT NULL, ADD montant DOUBLE PRECISION NOT NULL, DROP datecommande, DROP total, CHANGE etat status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD nom_produit VARCHAR(255) NOT NULL, ADD quantite VARCHAR(255) NOT NULL, ADD prix_unitaire DOUBLE PRECISION NOT NULL, DROP nom, DROP description, DROP prixunitaire, DROP quantitestock, DROP image, DROP categorie');
    }
}
