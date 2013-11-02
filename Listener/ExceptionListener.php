<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cyclear\GameBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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