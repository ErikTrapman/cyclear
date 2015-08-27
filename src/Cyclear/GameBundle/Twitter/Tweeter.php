<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Twitter;

use Symfony\Component\DependencyInjection\Container;
use TijsVerkoyen\Twitter\Twitter;

class Tweeter
{
    /**
     *
     * @var Twitter 
     */
    private $helper;

    /**
     *
     * @var Container
     */
    private $container;

    public function __construct(Twitter $helper, Container $container)
    {
        $this->helper = $helper;
        $this->container = $container;
    }

    public function sendTweet($msg)
    {
        if ($this->container->getParameter('enable_twitter')) {
            $this->helper->statusesUpdate($msg);
        }
    }
}