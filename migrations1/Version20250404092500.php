<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404092500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY fk_postID');
        $this->addSql('ALTER TABLE conversation_messages DROP FOREIGN KEY conversation_messages_ibfk_1');
        $this->addSql('ALTER TABLE conversation_messages DROP FOREIGN KEY conversation_messages_ibfk_2');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY fk_id_category');
        $this->addSql('ALTER TABLE message_attachments DROP FOREIGN KEY message_attachments_ibfk_1');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY fk_eventNot');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY fk_userNot');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY fk_event');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY fk_userPart');
        $this->addSql('ALTER TABLE partnership DROP FOREIGN KEY fk_partnerid');
        $this->addSql('ALTER TABLE reclamation_attachments DROP FOREIGN KEY reclamation_attachments_ibfk_1');
        $this->addSql('ALTER TABLE reclamation_conversations DROP FOREIGN KEY reclamation_conversations_ibfk_1');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY reclamations_ibfk_1');
        $this->addSql('ALTER TABLE reclamations DROP FOREIGN KEY reclamations_ibfk_2');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY fk_role_id');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE conversation_messages');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE message_attachments');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE partnership');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reclamation_attachments');
        $this->addSql('DROP TABLE reclamation_conversations');
        $this->addSql('DROP TABLE reclamations');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP INDEX fk_user ON commande');
        $this->addSql('ALTER TABLE commande CHANGE user_id utilisateur INT NOT NULL, CHANGE etat statut VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (category_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(256) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX unique_name (name), PRIMARY KEY(category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, content TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, INDEX useridFK (user_id), INDEX fk_postID (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conversation_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, message TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX conversation_id (conversation_id), INDEX sender_id (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE event (id_event INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_event DATE DEFAULT NULL, location VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, creation_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, image TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, activities TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, user_id INT NOT NULL, Status VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX fk_id_category (category_id), PRIMARY KEY(id_event)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX fk_userid (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE message_attachments (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, file_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX message_id (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, message VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, is_read TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX fk_userNot (user_id), INDEX fk_eventNot (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE participation (id_part INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, activities TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, part_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX fk_event (event_id), INDEX fk_userPart (user_id), PRIMARY KEY(id_part)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contactInfo VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, videoPath VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE partnership (id INT AUTO_INCREMENT NOT NULL, organizerId INT NOT NULL, partnerId INT NOT NULL, contracttype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, isSigned TINYINT(1) DEFAULT 0 NOT NULL, INDEX fkuserid (organizerId), INDEX fk_partnerid (partnerId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, content TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, user_id INT NOT NULL, image_path VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamation_attachments (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, file_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX reclamation_id (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamation_conversations (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX reclamation_id (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamations (id INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, id_event INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, subject VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'untreated\' NOT NULL COLLATE `utf8mb4_general_ci`, refuseReason TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, INDEX id_user (id_user), INDEX id_event (id_event), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservation (Id INT AUTO_INCREMENT NOT NULL, event_Id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prix DOUBLE PRECISION NOT NULL, NbPlaces INT NOT NULL, ticket_id INT DEFAULT NULL, INDEX fk_ticket (ticket_id), INDEX fk_user (user_id), INDEX fk_event (event_Id), PRIMARY KEY(Id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (role_id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ticket (ticket_id INT AUTO_INCREMENT NOT NULL, ticketCode VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, seatNumber VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(ticket_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (user_id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, user_username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, user_email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, user_password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, user_firstname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_lastname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_birthday DATE DEFAULT NULL, user_gender VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_picture VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_phonenumber VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, user_level INT DEFAULT 0, user_role VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX fk_role_id (role_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT fk_postID FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT conversation_messages_ibfk_1 FOREIGN KEY (conversation_id) REFERENCES reclamation_conversations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT conversation_messages_ibfk_2 FOREIGN KEY (sender_id) REFERENCES users (user_id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT fk_id_category FOREIGN KEY (category_id) REFERENCES categorie (category_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE message_attachments ADD CONSTRAINT message_attachments_ibfk_1 FOREIGN KEY (message_id) REFERENCES conversation_messages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT fk_eventNot FOREIGN KEY (event_id) REFERENCES event (id_event) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT fk_userNot FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT fk_event FOREIGN KEY (event_id) REFERENCES event (id_event) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT fk_userPart FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE partnership ADD CONSTRAINT fk_partnerid FOREIGN KEY (partnerId) REFERENCES partner (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reclamation_attachments ADD CONSTRAINT reclamation_attachments_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamation_conversations ADD CONSTRAINT reclamation_conversations_ibfk_1 FOREIGN KEY (reclamation_id) REFERENCES reclamations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT reclamations_ibfk_1 FOREIGN KEY (id_user) REFERENCES users (user_id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamations ADD CONSTRAINT reclamations_ibfk_2 FOREIGN KEY (id_event) REFERENCES event (id_event) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_role_id FOREIGN KEY (role_id) REFERENCES role (role_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE commande CHANGE utilisateur user_id INT NOT NULL, CHANGE statut etat VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX fk_user ON commande (user_id)');
    }
}
