<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240526182645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users firstname';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news RENAME INDEX idx_e0aeb88885357f53 TO IDX_1DD3995085357F53');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news RENAME INDEX idx_1dd3995085357f53 TO IDX_E0AEB88885357F53');
        $this->addSql('ALTER TABLE user DROP first_name');
    }
}
