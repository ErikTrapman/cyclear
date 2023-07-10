<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230710184104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename table news and add index';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('RENAME TABLE nieuws to news');
        $this->addSql('CREATE INDEX IDX_4720FB2F51B48EB3 ON wedstrijd (externalIdentifier)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_4720FB2F51B48EB3 ON wedstrijd');
    }
}
