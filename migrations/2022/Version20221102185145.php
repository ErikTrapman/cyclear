<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221102185145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Misc rewrites of data';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        foreach ($this->connection->executeQuery('SELECT * FROM uitslag_type')->fetchAllAssociative() as $type) {
            preg_match('/"([^"]+)"/', $type['cqParsingStrategy'], $matches);
            $mold = 'O:%d:"%s":0:{}';

            $search = 'ErikTrapman\Bundle\CQRankingParserBundle';
            $replace = 'App\CQRanking';

            if (!count($matches)) {
                continue;
            }

            $class = str_replace($search, $replace, $matches[1]);

            $replaced = sprintf($mold, strlen($class), $class);
            $this->connection->update('uitslag_type', ['cqParsingStrategy' => $replaced], ['id' => $type['id']]);
        }

        $this->connection->update('acl_classes', ['class_type' => 'App\Entity\Ploeg'], ['id' => 1]);

        foreach ($this->connection->executeQuery('SELECT * FROM acl_security_identities')->fetchAllAssociative() as $acl) {
            $identifier = str_replace('Cyclear\GameBundle\Entity\User', 'App\Entity\User', $acl['identifier']);
            $this->connection->update('acl_security_identities', ['identifier' => $identifier], ['id' => $acl['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
