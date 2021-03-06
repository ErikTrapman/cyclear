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

use App\Entity\Seizoen;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/transfer")
 */
class TransferController extends AbstractController
{

    /**
     * @Route("s", name="transfer_list")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->get('doctrine');

        $qb = $em->getRepository(Transfer::class)->createQueryBuilder('t')
            ->where('t.seizoen = :seizoen')
            ->andWhere('t.ploegNaar IS NOT NULL')->andWhere('t.transferType > :draft')
            ->setParameter('seizoen', $seizoen)->setParameter('draft', Transfer::DRAFTTRANSFER)
            ->orderBy('t.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return array('pagination' => $pagination, 'seizoen' => $seizoen);
    }


}