<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
