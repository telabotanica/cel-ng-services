<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210813124325 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pn_tb_pair (id INT AUTO_INCREMENT NOT NULL, occurrence_id INT NOT NULL, plant_net_occurrence_id INT NOT NULL, plantnet_occurrence_updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C22295B430572FAC (occurrence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pn_tb_pair ADD CONSTRAINT FK_C22295B430572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pn_tb_pair');
    }
}
