<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430181424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1869AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1869AC0396 FOREIGN KEY (conversation_id) REFERENCES reclamation_conversations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY FK_27BBA42F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments CHANGE message_id message_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT FK_27BBA42F537A1329 FOREIGN KEY (message_id) REFERENCES conversation_messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP INDEX UNIQ_61690F722D6BA2D9, ADD INDEX IDX_61690F722D6BA2D9 (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations CHANGE reclamation_id reclamation_id INT DEFAULT NULL, CHANGE status status VARCHAR(20) DEFAULT 'active' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1869AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1869AC0396 FOREIGN KEY (conversation_id) REFERENCES reclamation_conversations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY FK_27BBA42F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments CHANGE message_id message_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT FK_27BBA42F537A1329 FOREIGN KEY (message_id) REFERENCES conversation_messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP INDEX IDX_61690F722D6BA2D9, ADD UNIQUE INDEX UNIQ_61690F722D6BA2D9 (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations CHANGE reclamation_id reclamation_id INT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON DELETE CASCADE
        SQL);
    }
}
