<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428205031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_attachments (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, file_path VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, INDEX IDX_27BBA42F537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation_attachments (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT DEFAULT NULL, file_path VARCHAR(255) NOT NULL, INDEX IDX_5B904A472D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation_conversations (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT DEFAULT NULL, created_at DATETIME NOT NULL, status LONGTEXT NOT NULL, INDEX IDX_61690F722D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamations (id INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, id_event INT DEFAULT NULL, created_at DATETIME NOT NULL, description LONGTEXT NOT NULL, subject VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, refuse_reason LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT FK_27BBA42F537A1329 FOREIGN KEY (message_id) REFERENCES conversation_messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments ADD CONSTRAINT FK_5B904A472D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partner ADD rating DOUBLE PRECISION DEFAULT NULL, ADD rating_count INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY FK_27BBA42F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments DROP FOREIGN KEY FK_5B904A472D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_attachments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation_attachments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation_conversations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE partner DROP rating, DROP rating_count
        SQL);
    }
}
