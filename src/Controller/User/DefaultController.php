<?php declare(strict_types=1);

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
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
        return ['seizoen' => $seizoen];
    }
}
