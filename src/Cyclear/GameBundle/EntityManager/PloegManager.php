<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\EntityManager;

class PloegManager
{

    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getPuntenTotaal($ploeg, $renner, $seizoen)
    {


    }
}
