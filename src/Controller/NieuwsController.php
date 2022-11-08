<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Nieuws;
use App\Entity\Seizoen;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/nieuws")
 */
class NieuwsController extends AbstractController
{
    /**
     * @Route ("", name="nieuws")
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

        $qb = $em->getRepository(Nieuws::class)->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }
}
