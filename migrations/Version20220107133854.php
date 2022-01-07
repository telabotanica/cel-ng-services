<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220107133854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add unique index to speedup requests';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BEFD81F3D6516949 ON occurrence (plantnet_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX uniq_befd81f3d6516949 ON occurrence');    
    }
}
