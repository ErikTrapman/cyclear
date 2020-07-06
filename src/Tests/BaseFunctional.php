<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests;

class BaseFunctional extends \Liip\FunctionalTestBundle\Test\WebTestCase
{

    public function setUp()
    {

    }

    public function doLoadFixtures()
    {
        $fixtures = array(
            'App\Tests\Fixtures\LoadSeizoenData',
            'App\Tests\Fixtures\LoadRennerData',
            'App\Tests\Fixtures\LoadPloegData',
        );
        $this->loadFixtures($fixtures);
    }
}