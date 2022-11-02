<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Spelregels;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $spelregels = $this->getDoctrine()->getRepository(Spelregels::class)->createQueryBuilder('s')
            ->where('s.seizoen = :seizoen')->orderBy('s.id', 'DESC')->setMaxResults(1)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult();
        if (array_key_exists(0, $spelregels)) {
            $spelregels = $spelregels[0];
        } else {
            $spelregels = null;
        }
        return ['spelregels' => $spelregels, 'seizoen' => $seizoen];
    }
}
