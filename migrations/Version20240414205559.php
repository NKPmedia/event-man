<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240414205559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration ADD COLUMN telegram_chat_id INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__registration AS SELECT id, event_id, telegram_id, mail, status, rank, telegram_username, telegram_first_name, telegram_last_name FROM registration');
        $this->addSql('DROP TABLE registration');
        $this->addSql('CREATE TABLE registration (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_id INTEGER NOT NULL, telegram_id VARCHAR(255) DEFAULT NULL, mail VARCHAR(255) DEFAULT NULL, status INTEGER NOT NULL, rank INTEGER NOT NULL, telegram_username VARCHAR(255) DEFAULT NULL, telegram_first_name VARCHAR(255) DEFAULT NULL, telegram_last_name VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_62A8A7A771F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO registration (id, event_id, telegram_id, mail, status, rank, telegram_username, telegram_first_name, telegram_last_name) SELECT id, event_id, telegram_id, mail, status, rank, telegram_username, telegram_first_name, telegram_last_name FROM __temp__registration');
        $this->addSql('DROP TABLE __temp__registration');
        $this->addSql('CREATE INDEX IDX_62A8A7A771F7E88B ON registration (event_id)');
    }
}
