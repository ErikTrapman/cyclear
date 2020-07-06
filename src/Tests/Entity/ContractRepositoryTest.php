<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Entity;

class ContractRepositoryTest extends \App\Tests\BaseFunctional
{

    public function testGetLastContract()
    {
        $this->doLoadFixtures();


    }
}