<?php

namespace Cyclear\GameBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/archief")
 */
class ArchiefController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{

    /**
     * @Route("/", name="archief_index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $current = $em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        if (null !== $current) {
            $seizoenen = $em->getRepository("CyclearGameBundle:Seizoen")->createQueryBuilder("s")
                ->where("s != :current")
                ->andWhere('s.id < :currentId')
                ->setParameters(array('current' => $current, 'currentId' => $current->getId()));
            ;
            $res = $seizoenen->getQuery()->getResult();
        } else {
            $res = array();
        }

        return array('seizoenen' => $res);
    }
}