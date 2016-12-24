<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\ApplicationBundle\Log;

use Monolog\Handler\AbstractHandler;

class CyclearLogHandlerMail extends AbstractHandler
{
    /**
     *
     * @var Swift_Mailer
     */
    private $mailer;

    private $mailTo;

    public function __construct($level = Logger::DEBUG, $bubble = true, \Swift_Mailer $mailer, $mailTo)
    {
        parent::__construct($level, $bubble);
        $this->mailer = $mailer;
        $this->mailTo = $mailTo;
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
        if ($record['channel'] == 'cyclear') {
            $this->handleMail($record);
        }
    }

    private function handleMail($record)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('[Cyclear] Error from ' . $_SERVER['REQUEST_URI'])
            ->setFrom('error@cyclear.nl')
            // TODO FIXME emailadres uit config halen
            ->setTo($this->mailTo)
            ->setBody('Holy crap, een foutmelding:' . "\n" . $record['message']);
        $this->mailer->send($message);
    }
}