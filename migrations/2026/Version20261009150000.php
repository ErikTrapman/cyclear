<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ploegen of Cyclear 2014-2016 were never linked to a user. Most are matched by team name or abbreviation
 * to a ploeg of the same user in a later season; Brieske Il Campione (2016) was Boris.
 * Duketowncycling (2015) is left out: DUK was used by two different users.
 */
final class Version20261009150000 extends AbstractMigration
{
    /** ploeg id => [afkorting, user id] */
    private const array PLOEG_USERS = [
        // Cyclear 2014
        11 => ['CSC', 34],
        12 => ['RVB', 35],
        13 => ['SDF', 1],
        16 => ['SGM', 40],
        19 => ['RVL', 45],
        21 => ['TTV', 43],
        24 => ['PPP', 36],
        26 => ['WEF', 39],
        // Cyclear 2015
        31 => ['CSC', 34],
        32 => ['SDF', 1],
        35 => ['PPP', 36],
        36 => ['TTV', 43],
        37 => ['WEF', 39],
        38 => ['RVL', 45],
        39 => ['AES', 46],
        43 => ['ATB', 38],
        45 => ['SGM', 40],
        47 => ['RVB', 35],
        // Cyclear 2016
        50 => ['SDF', 1],
        52 => ['PPP', 36],
        53 => ['SGM', 40],
        54 => ['WEF', 39],
        56 => ['ETA', 41],
        57 => ['AES', 46],
        59 => ['BIC', 48],
        63 => ['RVL', 45],
        64 => ['TTV', 43],
        66 => ['RVB', 35],
    ];

    public function getDescription(): string
    {
        return 'Link the ploegen of Cyclear 2014-2016 to their users';
    }

    public function up(Schema $schema): void
    {
        foreach (self::PLOEG_USERS as $ploegId => [$afkorting, $userId]) {
            $this->addSql(
                'UPDATE ploeg p JOIN user u ON u.id = ? SET p.user_id = u.id WHERE p.id = ? AND p.afkorting = ? AND p.user_id IS NULL',
                [$userId, $ploegId, $afkorting],
            );
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::PLOEG_USERS as $ploegId => [$afkorting, $userId]) {
            $this->addSql('UPDATE ploeg SET user_id = NULL WHERE id = ? AND user_id = ?', [$ploegId, $userId]);
        }
    }
}
