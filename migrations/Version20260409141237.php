<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409141237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY `FK_D8698A76BD775E6A`');
        $this->addSql('ALTER TABLE document CHANGE date_upload date_upload DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BD775E6A FOREIGN KEY (typedocument_id) REFERENCES type_document (id)');
        $this->addSql('ALTER TABLE type_document ADD abreviation VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BD775E6A');
        $this->addSql('ALTER TABLE document CHANGE date_upload date_upload DATETIME NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT `FK_D8698A76BD775E6A` FOREIGN KEY (typedocument_id) REFERENCES type_document (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE type_document DROP abreviation');
    }
}
