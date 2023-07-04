<?php declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin')]
class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'admin_index')]
    public function indexAction(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('admin/default/index.html.twig');
    }
}
