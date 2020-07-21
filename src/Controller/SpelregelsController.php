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
use App\Entity\Spelregels;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/spelregels")
 */
class SpelregelsController extends AbstractController
{

    /**
     * @Route("/", name="spelregels_index")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $spelregels = $this->getDoctrine()->getRepository(Spelregels::class)->createQueryBuilder("s")
            ->where('s.seizoen = :seizoen')->orderBy('s.id', 'DESC')->setMaxResults(1)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult();
        if (array_key_exists(0, $spelregels)) {
            $spelregels = $spelregels[0];
        } else {
            $spelregels = null;
        }
        return array('spelregels' => $spelregels, 'seizoen' => $seizoen);
    }

}
