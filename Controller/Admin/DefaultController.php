<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/admin")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="admin_index")
     * @Template("CyclearGameBundle:Default/Admin:index.html.twig")
     */
    public function indexAction(\Symfony\Component\HttpFoundation\Request $request)
    {

        return array();
    }
}
