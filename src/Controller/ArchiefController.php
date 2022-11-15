<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Repository\SeizoenRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/archief")
 */
class ArchiefController extends AbstractController
{
    public function __construct(
        private readonly SeizoenRepository $seizoenRepository
    ) {
    }

    /**
     * @Route ("/", name="archief_index")
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template ()
     */
    public function indexAction(Seizoen $seizoen): array
    {
        return ['seizoenen' => $this->seizoenRepository->getArchived()];
    }
}
