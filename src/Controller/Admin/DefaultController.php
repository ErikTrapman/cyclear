<?php declare(strict_types=1);

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="admin_index")
     * @Template()
     */
    public function indexAction(\Symfony\Component\HttpFoundation\Request $request): array
    {
        return [];
    }
}
