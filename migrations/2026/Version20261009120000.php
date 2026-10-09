<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * DBAL 4 drops the 'array' and 'object' types, which store PHP-serialized data.
 */
final class Version20261009120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store user roles as JSON and the parsing strategy as a class name';
    }

    public function up(Schema $schema): void
    {
        foreach ($this->connection->fetchAllAssociative('SELECT id, roles FROM user') as $row) {
            $roles = unserialize($row['roles'], ['allowed_classes' => false]);
            $this->abortIf(!is_array($roles), sprintf('Cannot unserialize roles of user %d', $row['id']));
            $this->addSql('UPDATE user SET roles = ? WHERE id = ?', [json_encode(array_values($roles)), $row['id']]);
        }
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');

        foreach ($this->connection->fetchAllAssociative('SELECT id, cqParsingStrategy FROM uitslag_type') as $row) {
            $matched = preg_match('/^O:\d+:"([^"]+)"/', $row['cqParsingStrategy'], $matches);
            $this->abortIf(1 !== $matched, sprintf('Cannot read the parsing strategy of uitslag_type %d', $row['id']));
            $this->addSql('UPDATE uitslag_type SET cqParsingStrategy = ? WHERE id = ?', [$matches[1], $row['id']]);
        }
        $this->addSql('ALTER TABLE uitslag_type CHANGE cqParsingStrategy cqParsingStrategy VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)'");
        foreach ($this->connection->fetchAllAssociative('SELECT id, roles FROM user') as $row) {
            $this->addSql('UPDATE user SET roles = ? WHERE id = ?', [serialize(json_decode($row['roles'], true)), $row['id']]);
        }

        $this->addSql("ALTER TABLE uitslag_type CHANGE cqParsingStrategy cqParsingStrategy LONGTEXT NOT NULL COMMENT '(DC2Type:object)'");
        foreach ($this->connection->fetchAllAssociative('SELECT id, cqParsingStrategy FROM uitslag_type') as $row) {
            $class = $row['cqParsingStrategy'];
            $this->addSql('UPDATE uitslag_type SET cqParsingStrategy = ? WHERE id = ?', [sprintf('O:%d:"%s":0:{}', strlen($class), $class), $row['id']]);
        }
    }
}
