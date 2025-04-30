<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430004353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1869AC0396 FOREIGN KEY (conversation_id) REFERENCES reclamation_conversations (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3B4CA1869AC0396 ON conversation_messages (conversation_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1869AC0396
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3B4CA1869AC0396 ON conversation_messages
        SQL);
    }
}
