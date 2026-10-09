<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Winners of the edition of the game before Cyclear 2014 have no seasons or ploegen in this database,
 * so their awards carry the user, season and team themselves.
 */
final class Version20261009170000 extends AbstractMigration
{
    /** [season, team, user id] */
    private const array WINNERS = [
        ['Cyclear 2008', 'RVL', 45],
        ['Cyclear 2009', 'TBI', 34],
        ['Cyclear 2010', 'TGF', 34],
        ['Cyclear 2011', 'INK', 44],
        ['Cyclear 2012', 'DUK', 52],
        ['Cyclear 2013', 'CSC', 34],
    ];

    public function getDescription(): string
    {
        return 'Allow awards without a ploeg and award the winners of 2008-2013';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE award ADD season VARCHAR(32) DEFAULT NULL, ADD team VARCHAR(64) DEFAULT NULL, ADD user_id INT DEFAULT NULL, CHANGE ploeg_id ploeg_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8A5B2EE7A76ED395 ON award (user_id)');
        foreach (self::WINNERS as [$season, $team, $userId]) {
            $this->addSql(
                'INSERT INTO award (type, season, team, user_id) SELECT ?, ?, ?, id FROM user WHERE id = ?',
                ['season_winner', $season, $team, $userId],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM award WHERE ploeg_id IS NULL');
        $this->addSql('ALTER TABLE award DROP FOREIGN KEY FK_8A5B2EE7A76ED395');
        $this->addSql('DROP INDEX IDX_8A5B2EE7A76ED395 ON award');
        $this->addSql('ALTER TABLE award DROP season, DROP team, DROP user_id, CHANGE ploeg_id ploeg_id INT NOT NULL');
    }
}
