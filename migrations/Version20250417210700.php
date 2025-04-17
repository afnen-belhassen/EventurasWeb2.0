<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417210700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bad_word (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE categorie (category_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, content LONGTEXT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9474526C4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id_event INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, date_event DATETIME DEFAULT NULL, location VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, image LONGTEXT DEFAULT NULL, activities LONGTEXT DEFAULT NULL, user_id INT NOT NULL, status VARCHAR(255) NOT NULL, prix NUMERIC(10, 0) NOT NULL, nb_places INT NOT NULL, date_fin_eve DATETIME DEFAULT NULL, INDEX IDX_3BAE0AA712469DE2 (category_id), PRIMARY KEY(id_event)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `like` (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, liked_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participation (id_part INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, status VARCHAR(255) DEFAULT NULL, activities LONGTEXT NOT NULL, part_date DATETIME NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id_part)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, user_id INT NOT NULL, image_path VARCHAR(150) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(255) NOT NULL, ticket_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (ticket_id INT AUTO_INCREMENT NOT NULL, ticket_code VARCHAR(255) NOT NULL, seat_number VARCHAR(255) NOT NULL, PRIMARY KEY(ticket_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA712469DE2 FOREIGN KEY (category_id) REFERENCES categorie (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX conversation_id ON conversation_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages CHANGE message message LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY message_attachments_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY message_attachments_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments CHANGE message_id message_id INT DEFAULT NULL, CHANGE uploaded_at uploaded_at DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT FK_27BBA42F537A1329 FOREIGN KEY (message_id) REFERENCES conversation_messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX message_id ON message_attachments
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_27BBA42F537A1329 ON message_attachments (message_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT message_attachments_ibfk_1 FOREIGN KEY (message_id) REFERENCES conversation_messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments DROP FOREIGN KEY reclamation_attachments_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments DROP FOREIGN KEY reclamation_attachments_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments CHANGE reclamation_id reclamation_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments ADD CONSTRAINT FK_5B904A472D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX reclamation_id ON reclamation_attachments
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5B904A472D6BA2D9 ON reclamation_attachments (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments ADD CONSTRAINT reclamation_attachments_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY reclamation_conversations_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY reclamation_conversations_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations CHANGE reclamation_id reclamation_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE status status LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX reclamation_id ON reclamation_conversations
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_61690F722D6BA2D9 ON reclamation_conversations (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT reclamation_conversations_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations CHANGE created_at created_at DATETIME NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE refuse_reason refuse_reason LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA712469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bad_word
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `like`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notifications
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ticket
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages CHANGE message message TEXT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX conversation_id ON conversation_messages (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY FK_27BBA42F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments DROP FOREIGN KEY FK_27BBA42F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments CHANGE message_id message_id INT NOT NULL, CHANGE uploaded_at uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT message_attachments_ibfk_1 FOREIGN KEY (message_id) REFERENCES conversation_messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_27bba42f537a1329 ON message_attachments
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX message_id ON message_attachments (message_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_attachments ADD CONSTRAINT FK_27BBA42F537A1329 FOREIGN KEY (message_id) REFERENCES conversation_messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE refuse_reason refuse_reason TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments DROP FOREIGN KEY FK_5B904A472D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments DROP FOREIGN KEY FK_5B904A472D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments CHANGE reclamation_id reclamation_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments ADD CONSTRAINT reclamation_attachments_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_5b904a472d6ba2d9 ON reclamation_attachments
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX reclamation_id ON reclamation_attachments (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_attachments ADD CONSTRAINT FK_5B904A472D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations DROP FOREIGN KEY FK_61690F722D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations CHANGE reclamation_id reclamation_id INT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE status status TEXT DEFAULT 'active' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT reclamation_conversations_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_61690f722d6ba2d9 ON reclamation_conversations
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX reclamation_id ON reclamation_conversations (reclamation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations ADD CONSTRAINT FK_61690F722D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id)
        SQL);
    }
}
