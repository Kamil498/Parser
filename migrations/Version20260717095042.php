<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717095042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonito CHANGE tytul tytul VARCHAR(255) DEFAULT NULL, CHANGE autor autor VARCHAR(255) DEFAULT NULL, CHANGE wydawnictwo wydawnictwo VARCHAR(255) DEFAULT NULL, CHANGE rok_wydania rok_wydania SMALLINT DEFAULT NULL, CHANGE cena cena NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonito CHANGE tytul tytul VARCHAR(255) NOT NULL, CHANGE autor autor VARCHAR(255) NOT NULL, CHANGE wydawnictwo wydawnictwo VARCHAR(255) NOT NULL, CHANGE rok_wydania rok_wydania SMALLINT NOT NULL, CHANGE cena cena NUMERIC(10, 2) NOT NULL');
    }
}
