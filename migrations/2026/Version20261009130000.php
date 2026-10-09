<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * gedmo/doctrine-extensions 3.22 dropped the lookup indexes of ext_translations; the unique index now starts with foreign_key.
 */
final class Version20261009130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align ext_translations indexes with gedmo/doctrine-extensions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX general_translations_lookup_idx ON ext_translations');
        $this->addSql('DROP INDEX translations_lookup_idx ON ext_translations');
        $this->addSql('DROP INDEX lookup_unique_idx ON ext_translations');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (foreign_key, locale, object_class, field)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX lookup_unique_idx ON ext_translations');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (locale, object_class, field, foreign_key)');
        $this->addSql('CREATE INDEX translations_lookup_idx ON ext_translations (locale, object_class, foreign_key)');
        $this->addSql('CREATE INDEX general_translations_lookup_idx ON ext_translations (object_class, foreign_key)');
    }
}
