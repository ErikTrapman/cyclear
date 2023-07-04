<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\SeizoenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/archief')]
class ArchiefController extends AbstractController
{
    public function __construct(
        private readonly SeizoenRepository $seizoenRepository
    ) {
    }

    #[Route(path: '/', name: 'archief_index')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('archief/index.html.twig', ['seizoenen' => $this->seizoenRepository->getArchived()]);
    }
}
