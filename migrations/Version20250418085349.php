<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418085349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_61690f722d6ba2d9 ON reclamation_conversations
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_61690F722D6BA2D9 ON reclamation_conversations (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations CHANGE id_event id_event INT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE refuse_reason refuse_reason LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations CHANGE id_event id_event INT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE refuse_reason refuse_reason TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_61690f722d6ba2d9 ON reclamation_conversations
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_61690F722D6BA2D9 ON reclamation_conversations (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
    }
}
