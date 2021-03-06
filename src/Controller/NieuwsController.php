<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Nieuws;
use App\Entity\Seizoen;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/{seizoen}/nieuws")
 */
class NieuwsController extends AbstractController
{

    /**
     * @Route("", name="nieuws")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->get('doctrine');

        $qb = $em->getRepository(Nieuws::class)->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return array('pagination' => $pagination, 'seizoen' => $seizoen);
    }

}