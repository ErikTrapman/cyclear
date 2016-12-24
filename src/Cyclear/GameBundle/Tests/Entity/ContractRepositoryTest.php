<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Tests\Entity;

class ContractRepositoryTest extends \Cyclear\GameBundle\Tests\BaseFunctional
{

    public function testGetLastContract()
    {
        $this->doLoadFixtures();


    }
}