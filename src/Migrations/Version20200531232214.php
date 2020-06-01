<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200531232214 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article ADD asy_packaging INT DEFAULT NULL, ADD asy_min_order INT DEFAULT NULL, ADD asy_installation VARCHAR(255) DEFAULT NULL, ADD title VARCHAR(255) DEFAULT NULL, ADD shortdesc VARCHAR(255) DEFAULT NULL, ADD longdesc VARCHAR(255) DEFAULT NULL, ADD unitname VARCHAR(255) DEFAULT NULL, ADD asy_deltext_standard_1 VARCHAR(255) DEFAULT NULL, ADD asy_deltext_standard_schweiz VARCHAR(255) DEFAULT NULL, ADD asy_deltext_standard_2 VARCHAR(255) DEFAULT NULL, ADD alphabytes_variantenmerkmale VARCHAR(255) DEFAULT NULL, ADD asy_deltext_standard VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE article DROP asy_packaging, DROP asy_min_order, DROP asy_installation, DROP title, DROP shortdesc, DROP longdesc, DROP unitname, DROP asy_deltext_standard_1, DROP asy_deltext_standard_schweiz, DROP asy_deltext_standard_2, DROP alphabytes_variantenmerkmale, DROP asy_deltext_standard');
    }
}
