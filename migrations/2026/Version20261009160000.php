<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Awards a trophy to the winner of every closed season, using the same order as the standings:
 * most ploegpunten first, then afkorting.
 */
final class Version20261009160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create award table and award the winners of closed seasons';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE award (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(32) NOT NULL, ploeg_id INT NOT NULL, UNIQUE INDEX award_ploeg_type_unique (ploeg_id, type), INDEX IDX_8A5B2EE712E5FE42 (ploeg_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE712E5FE42 FOREIGN KEY (ploeg_id) REFERENCES ploeg (id) ON DELETE CASCADE');
        $this->addSql(<<<'SQL'
            INSERT INTO award (ploeg_id, type)
            SELECT ranked.ploeg_id, 'season_winner'
            FROM (
                SELECT p.id AS ploeg_id,
                       ROW_NUMBER() OVER (PARTITION BY p.seizoen_id ORDER BY COALESCE(SUM(u.ploegPunten), 0) DESC, p.afkorting ASC) AS position
                FROM ploeg p
                INNER JOIN seizoen s ON s.id = p.seizoen_id AND s.closed = 1
                LEFT JOIN uitslag u ON u.ploeg_id = p.id
                GROUP BY p.id, p.seizoen_id, p.afkorting
            ) ranked
            WHERE ranked.position = 1
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE award');
    }
}
