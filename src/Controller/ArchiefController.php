<?php declare(strict_types=1);

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/archief")
 */
class ArchiefController extends AbstractController
{
    /**
     * @Route("/", name="archief_index")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Seizoen $seizoen)
    {
        $em = $this->getDoctrine()->getManager();

        $current = $em->getRepository(Seizoen::class)->getCurrent();
        if (null !== $current) {
            $seizoenen = $em->getRepository(Seizoen::class)->createQueryBuilder('s')
                ->where('s != :current')->andWhere('s.closed = 1')
                ->setParameters(['current' => $current]);
            $res = $seizoenen->getQuery()->getResult();
        } else {
            $res = [];
        }

        return ['seizoenen' => $res];
    }
}
