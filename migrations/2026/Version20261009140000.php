<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The country translations were stored under the class name from before the move to the App namespace,
 * so Gedmo Translatable never found them.
 */
final class Version20261009140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Point country translations to App\Entity\Country';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE ext_translations SET object_class = ? WHERE object_class = ?', ['App\Entity\Country', 'Cyclear\GameBundle\Entity\Country']);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE ext_translations SET object_class = ? WHERE object_class = ?', ['Cyclear\GameBundle\Entity\Country', 'App\Entity\Country']);
    }
}
