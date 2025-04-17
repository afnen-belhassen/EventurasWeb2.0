<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412081125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD adresse VARCHAR(255) NOT NULL, ADD telephone VARCHAR(20) NOT NULL, ADD quantite INT NOT NULL, ADD date_commande DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP date, CHANGE utilisateur produit_id INT NOT NULL, CHANGE statut nom_client VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF347EFB ON commande (produit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF347EFB');
        $this->addSql('DROP INDEX IDX_6EEAA67DF347EFB ON commande');
        $this->addSql('ALTER TABLE commande ADD utilisateur INT NOT NULL, ADD date DATE NOT NULL, ADD statut VARCHAR(255) NOT NULL, DROP produit_id, DROP nom_client, DROP adresse, DROP telephone, DROP quantite, DROP date_commande');
    }
}
