<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717103408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tania_ksiazka (id INT AUTO_INCREMENT NOT NULL, tytul VARCHAR(255) DEFAULT NULL, autor VARCHAR(255) DEFAULT NULL, wydawnnictwo VARCHAR(255) DEFAULT NULL, rok_wydania SMALLINT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, cena NUMERIC(10, 2) DEFAULT NULL, url VARCHAR(255) NOT NULL, shop VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE empik CHANGE status status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tania_ksiazka');
        $this->addSql('ALTER TABLE empik CHANGE status status VARCHAR(255) NOT NULL');
    }
}
