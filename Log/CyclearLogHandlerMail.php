<?php
namespace Cyclear\GameBundle\Log;

use Monolog\Handler\AbstractHandler;

class CyclearLogHandlerMail extends AbstractHandler {

	/**
	 *
	 * @var Swift_Mailer
	 */
	private $mailer;


	public function __construct($level = Logger::DEBUG, $bubble = true, \Swift_Mailer $mailer){
		parent::__construct($level, $bubble);
		$this->mailer = $mailer;
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
	public function handle(array $record){
		//INSERT INTO `applog` (`time`, `ip`, `source`, `message`) VALUES ('2011-10-08 13:40:31', '127.0.0.1', 'dssdfds', 'dfdfd')
		if($record['channel'] == 'cyclear'){
			$this->handleMail($record);
		}
	}

	private function handleMail($record){
		$message = \Swift_Message::newInstance()
		->setSubject('[Cyclear] Error from '. $_SERVER['REQUEST_URI'])
		->setFrom('error@cyclear.nl')
		->setTo('veggatron+cyclear+log@gmail.com')
		->setBody( 'Holy crap, een foutmelding:'."\n". $record['message']  );
		$this->mailer->send($message);
	}

}