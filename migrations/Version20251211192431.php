<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211192431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__people AS SELECT id, name, phone FROM people');
        $this->addSql('DROP TABLE people');
        $this->addSql('CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, password VARCHAR(255) DEFAULT NULL, roles CLOB NOT NULL)');
        $this->addSql('INSERT INTO people (id, name, phone) SELECT id, name, phone FROM __temp__people');
        $this->addSql('DROP TABLE __temp__people');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28166A26444F97DD ON people (phone)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__people AS SELECT id, name, phone FROM people');
        $this->addSql('DROP TABLE people');
        $this->addSql('CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO people (id, name, phone) SELECT id, name, phone FROM __temp__people');
        $this->addSql('DROP TABLE __temp__people');
    }
}
