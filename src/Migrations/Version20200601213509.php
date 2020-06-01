<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200601213509 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, cmiuuid VARCHAR(255) DEFAULT NULL, parentid INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, sort INT DEFAULT NULL, active TINYINT(1) DEFAULT NULL, hidden TINYINT(1) DEFAULT NULL, template VARCHAR(255) DEFAULT NULL, asy_cattype INT DEFAULT NULL, crosssellingtitle VARCHAR(255) DEFAULT NULL, crosssellingtitle_1 VARCHAR(255) DEFAULT NULL, crosssellingtitle_2 VARCHAR(255) DEFAULT NULL, asy_setcategory TINYINT(1) DEFAULT NULL, cat_desc VARCHAR(255) DEFAULT NULL, shortdesc VARCHAR(255) DEFAULT NULL, longdesc LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE category');
    }
}
