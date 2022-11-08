<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/transfer")
 */
class TransferController extends AbstractController
{
    /**
     * @Route ("s", name="transfer_list")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @return (Seizoen|mixed)[]
     *
     * @psalm-return array{pagination: mixed, seizoen: Seizoen}
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
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
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }
}
