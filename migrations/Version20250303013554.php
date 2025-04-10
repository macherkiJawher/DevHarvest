<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303013554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD quantite_stock INT NOT NULL, ADD date_ajout DATETIME NOT NULL, CHANGE quantitestock agriculteur_id INT NOT NULL, CHANGE prixunitaire prix_unitaire NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC277EBB810E FOREIGN KEY (agriculteur_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC277EBB810E ON produit (agriculteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC277EBB810E');
        $this->addSql('DROP INDEX IDX_29A5EC277EBB810E ON produit');
        $this->addSql('ALTER TABLE produit ADD quantitestock INT NOT NULL, DROP agriculteur_id, DROP quantite_stock, DROP date_ajout, CHANGE prix_unitaire prixunitaire NUMERIC(10, 2) NOT NULL');
    }
}
