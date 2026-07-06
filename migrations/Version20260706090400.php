<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260706090400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE is_activate is_active TINYINT NOT NULL');
        $this->addSql('ALTER TABLE price_history CHANGE book_id book_id INT NOT NULL');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB81716A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4C9CB81716A2B381 ON price_history (book_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE is_active is_activate TINYINT NOT NULL');
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB81716A2B381');
        $this->addSql('DROP INDEX IDX_4C9CB81716A2B381 ON price_history');
        $this->addSql('ALTER TABLE price_history CHANGE book_id book_id BIGINT NOT NULL');
    }
}
