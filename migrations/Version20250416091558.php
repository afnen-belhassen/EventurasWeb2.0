<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416091558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE partner ADD website VARCHAR(255) DEFAULT NULL, ADD address VARCHAR(255) NOT NULL, ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', DROP company, DROP position, DROP status
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE partner ADD position VARCHAR(255) NOT NULL, ADD status VARCHAR(50) NOT NULL, DROP website, DROP updated_at, CHANGE address company VARCHAR(255) NOT NULL
        SQL);
    }
}
