<?php declare(strict_types=1);

namespace App\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DefaultController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * @Route("/", name="admin_index")
     * @Template()
     */
    public function indexAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        return [];
    }
}
