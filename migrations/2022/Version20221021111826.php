<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021111826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE AwardedBadge DROP FOREIGN KEY FK_23EED6F7A2C2FC');
        $this->addSql('ALTER TABLE AwardedBadge DROP FOREIGN KEY FK_23EED6A76ED395');
        $this->addSql('DROP TABLE AppLog');
        $this->addSql('DROP TABLE AwardedBadge');
        $this->addSql('DROP TABLE Badge');
        $this->addSql('ALTER TABLE ext_translations CHANGE object_class object_class VARCHAR(191) NOT NULL');
        $this->addSql('CREATE INDEX general_translations_lookup_idx ON ext_translations (object_class, foreign_key)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE AppLog (id INT AUTO_INCREMENT NOT NULL, time DATETIME NOT NULL, ip VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, source VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, message LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE AwardedBadge (id INT AUTO_INCREMENT NOT NULL, badge_id INT DEFAULT NULL, user_id INT DEFAULT NULL, recurringAmount INT DEFAULT NULL, INDEX IDX_23EED6F7A2C2FC (badge_id), INDEX IDX_23EED6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, updatedAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE AwardedBadge ADD CONSTRAINT FK_23EED6F7A2C2FC FOREIGN KEY (badge_id) REFERENCES Badge (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE AwardedBadge ADD CONSTRAINT FK_23EED6A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP INDEX general_translations_lookup_idx ON ext_translations');
        $this->addSql('ALTER TABLE ext_translations CHANGE object_class object_class VARCHAR(255) NOT NULL');
    }
}
