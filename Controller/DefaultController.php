<?php

namespace Cyclear\GameBundle\Controller;

use Zend\Debug;

use Cyclear\GameBundle\Document\AppLog;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
    	/*
    	$em = $this->get('doctrine.odm.mongodb.document_manager');

    	$log = new AppLog();
    	$ip = ( date('s') % 2 ) ? '127.0.0.1' : '192.168.2.34';
    	$log->setIp($ip);
    	$log->setMessage('Im a log message ');
    	$log->setSource(__METHOD__);
    	$log->setTime(new \MongoDate());
    	
    	$em->persist($log);
    	$em->flush();
    	
    	$map = 'function() { emit(this.ip, { count: 1 } ); }';
    	
    	$reduce = 'function(key, values) { 	
    			var sum = 0;
		     	
		     	for(var i=0;i<=values.length;i++){
		     		sum += 1;
		     	}
    	
    			return {count: sum };	
    
    	}';
    	
    	
    	$qb = $em->createQueryBuilder('CyclearGameBundle:AppLog');
    	$qb->map($map)->reduce($reduce);
    	
    	//var_dump( $qb->debug('mapReduce') );
    	
    	//var_dump($qb->getType());
    	//var_dump($qb->getQueryArray());
    	
    	//
    	$rows = $qb->getQuery()->execute();
    	//var_dump( get_class_methods($query));
    	//$rows = $query->execute();
    	//var_dump( get_class_methods($rows));
    	
    	
    	foreach($rows as $row){
    		echo $row['_id'] .' has '.$row['value']['count'].' log msgs<br>';
    	}
    	*/
    	return $this->render('CyclearGameBundle:Default:index.html.twig');
    }
}
