<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
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

        return array();
    }
}
