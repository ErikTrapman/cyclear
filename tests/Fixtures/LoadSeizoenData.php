<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadSeizoenData extends Fixture
{

    public function load(\Doctrine\Persistence\ObjectManager $manager)
    {
        $s = new \App\Entity\Seizoen();
        $s->setIdentifier("Seizoen 1");
        $manager->persist($s);

        $manager->flush();
    }
}
