<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Entity\Seizoen;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 *
 * @Route("/user/{seizoen}")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     * @Template("default/User:index.html.twig")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function indexAction(Seizoen $seizoen)
    {
        return array('seizoen' => $seizoen);
    }
}
