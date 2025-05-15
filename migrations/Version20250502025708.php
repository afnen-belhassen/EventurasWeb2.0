<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502025708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE role (role_id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(50) DEFAULT NULL, PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (user_id INT AUTO_INCREMENT NOT NULL, role_id INT DEFAULT NULL, user_username VARCHAR(255) NOT NULL, user_email VARCHAR(255) NOT NULL, user_password VARCHAR(255) NOT NULL, user_firstname VARCHAR(255) DEFAULT NULL, user_lastname VARCHAR(255) DEFAULT NULL, user_birthday DATE DEFAULT NULL, user_gender VARCHAR(50) DEFAULT NULL, user_picture VARCHAR(255) DEFAULT NULL, user_phonenumber VARCHAR(20) DEFAULT NULL, user_level INT DEFAULT 0, statut INT NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, INDEX fk_role_id (role_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (role_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1869AC0396 FOREIGN KEY (conversation_id) REFERENCES reclamation_conversations (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3B4CA1869AC0396 ON conversation_messages (conversation_id)
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
            ALTER TABLE reclamation_conversations CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE status status VARCHAR(20) DEFAULT 'active' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamations ADD is_rated TINYINT(1) NOT NULL, ADD rating SMALLINT DEFAULT NULL, ADD closed_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1869AC0396
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3B4CA1869AC0396 ON conversation_messages
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
            ALTER TABLE reclamations DROP is_rated, DROP rating, DROP closed_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation_conversations CHANGE created_at created_at DATETIME NOT NULL, CHANGE status status LONGTEXT NOT NULL
        SQL);
    }
}
