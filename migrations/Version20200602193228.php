<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200602193228 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, artnum VARCHAR(255) NOT NULL, parentid INT DEFAULT NULL, asy_packaging INT DEFAULT NULL, asy_min_order INT DEFAULT NULL, asy_installation VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, shortdesc VARCHAR(255) DEFAULT NULL, longdesc LONGTEXT DEFAULT NULL, unitname VARCHAR(255) DEFAULT NULL, asy_deltext_standard_1 VARCHAR(255) DEFAULT NULL, asy_deltext_standard_schweiz VARCHAR(255) DEFAULT NULL, asy_deltext_standard_2 VARCHAR(255) DEFAULT NULL, alphabytes_variantenmerkmale VARCHAR(255) DEFAULT NULL, asy_deltext_standard VARCHAR(255) DEFAULT NULL, varname VARCHAR(255) DEFAULT NULL, varselect VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, swffexporttoff VARCHAR(255) DEFAULT NULL, swffexporttitle VARCHAR(255) DEFAULT NULL, pos INT DEFAULT NULL, displayinbasket TINYINT(1) DEFAULT NULL, cmiuuid VARCHAR(255) NOT NULL, unit VARCHAR(255) DEFAULT NULL, variant_attribute_sort INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, cmiuuid VARCHAR(255) DEFAULT NULL, parentid VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, sort INT DEFAULT NULL, active TINYINT(1) DEFAULT NULL, hidden TINYINT(1) DEFAULT NULL, template VARCHAR(255) DEFAULT NULL, asy_cattype INT DEFAULT NULL, crosssellingtitle VARCHAR(255) DEFAULT NULL, crosssellingtitle_1 VARCHAR(255) DEFAULT NULL, crosssellingtitle_2 VARCHAR(255) DEFAULT NULL, asy_setcategory TINYINT(1) DEFAULT NULL, cat_desc VARCHAR(255) DEFAULT NULL, shortdesc VARCHAR(255) DEFAULT NULL, longdesc LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE object2attribute (id INT AUTO_INCREMENT NOT NULL, object_id INT NOT NULL, attr_id INT NOT NULL, alpha_variantmerkmal TINYINT(1) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, value_2 VARCHAR(255) DEFAULT NULL, INDEX IDX_9DDC5FBC232D562B (object_id), INDEX IDX_9DDC5FBC747AE5C2 (attr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE object2category (id INT AUTO_INCREMENT NOT NULL, catnid INT NOT NULL, objectid INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE object2attribute ADD CONSTRAINT FK_9DDC5FBC232D562B FOREIGN KEY (object_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE object2attribute ADD CONSTRAINT FK_9DDC5FBC747AE5C2 FOREIGN KEY (attr_id) REFERENCES attribute (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE object2attribute DROP FOREIGN KEY FK_9DDC5FBC232D562B');
        $this->addSql('ALTER TABLE object2attribute DROP FOREIGN KEY FK_9DDC5FBC747AE5C2');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE object2attribute');
        $this->addSql('DROP TABLE object2category');
    }
}
