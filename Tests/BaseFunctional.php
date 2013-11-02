<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Tests;

class BaseFunctional extends \Liip\FunctionalTestBundle\Test\WebTestCase
{

    public function setUp()
    {
        
    }

    public function doLoadFixtures()
    {
        $fixtures = array(
            'Cyclear\GameBundle\Tests\Fixtures\LoadSeizoenData',
            'Cyclear\GameBundle\Tests\Fixtures\LoadRennerData',
            'Cyclear\GameBundle\Tests\Fixtures\LoadPloegData',
        );
        $this->loadFixtures($fixtures);
    }
}