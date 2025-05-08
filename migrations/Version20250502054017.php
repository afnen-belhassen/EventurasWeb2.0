<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502054017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations ADD CONSTRAINT FK_1CAD6B76D52B4B97 FOREIGN KEY (id_event) REFERENCES event (id_event) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1CAD6B76D52B4B97 ON reclamations (id_event)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations DROP FOREIGN KEY FK_1CAD6B76D52B4B97
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1CAD6B76D52B4B97 ON reclamations
        SQL);
    }
}
