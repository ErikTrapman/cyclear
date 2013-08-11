<?php

namespace Cyclear\ApplicationBundle\Log;

use Monolog\Handler\AbstractHandler;

class CyclearLogHandlerDb extends AbstractHandler
{
    /**
     * 
     * @var PDO
     */
    private $pdo;

    public function __construct($level = Logger::DEBUG, $bubble = true, \PDO $pdo)
    {
        parent::__construct($level, $bubble);
        $this->pdo = $pdo;
    }

    /**
     * Handles a record.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     *
     * @param array $record The record to handle
     * @return Boolean True means that this handler handled the record, and that bubbling is not permitted.
     *                 False means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        //INSERT INTO `applog` (`time`, `ip`, `source`, `message`) VALUES ('2011-10-08 13:40:31', '127.0.0.1', 'dssdfds', 'dfdfd')
        if ($record['channel'] == 'cyclear') {
            $this->handleDb($record);
        }
    }

    private function handleDb($record)
    {
        $st = $this->pdo->prepare("INSERT INTO `AppLog` (`time`, `ip`, `source`, `message`) VALUES (:datetime, :ip, :source, :message)");

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $st->bindParam(':datetime', $now);
        $st->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        $st->bindParam(':source', $_SERVER['REQUEST_URI']);
        $msg = $record['message'];
        $st->bindParam(':message', $msg);
        $st->execute();
    }
}