<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $em = $this->getDoctrine()->getManager();

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