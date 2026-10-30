<?php declare(strict_types=1);

namespace App\Tests\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * Tests run on SQLite, the app on MySQL: adds the MySQL functions the repositories use that SQLite lacks.
 */
#[AsMiddleware]
class SqliteMysqlFunctionsMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class($driver) extends AbstractDriverMiddleware {
            public function connect(#[\SensitiveParameter] array $params): Connection
            {
                $connection = parent::connect($params);
                $native = $connection->getNativeConnection();
                if ($native instanceof \Pdo\Sqlite) {
                    // UitslagRepository::getCountForPosition() counts with SUM(IF(...))
                    $native->createFunction('IF', static fn ($condition, $then, $else) => $condition ? $then : $else, 3);
                }

                return $connection;
            }
        };
    }
}
