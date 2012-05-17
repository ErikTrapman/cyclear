<?php
namespace Cyclear\GameBundle\Listener;

use Cyclear\GameBundle\Log\CyclearLogHandler;
use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\BrowserKit\Response;

use Symfony\Component\Security\Core\SecurityContextInterface;

class ExceptionListener {

	private $logger;

	public function __construct( $logger ){
		$this->logger = $logger;
	}

	/**
	 * Handles security related exceptions.
	 *
	 * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
	 */
	public function onKernelException(GetResponseForExceptionEvent $event){
		
		$exception = $event->getException();
		$request = $event->getRequest();
		$this->logger->addError( $exception->__toString() );
		return;
	}


}