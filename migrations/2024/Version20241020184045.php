<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020184045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Speed up general classification query';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_transfer_renner_season ON transfer (renner_id, seizoen_id, transferType, ploegnaar_id);');
        $this->addSql('CREATE INDEX idx_transfer_renner_season_ploeg ON transfer (renner_id, seizoen_id, ploegNaar_id, transferType);');
        $this->addSql('CREATE INDEX idx_wedstrijd_seizoen_datum ON wedstrijd (seizoen_id, datum, id);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
