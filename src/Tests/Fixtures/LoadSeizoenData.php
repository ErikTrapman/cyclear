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

class LoadSeizoenData implements \Doctrine\Common\DataFixtures\FixtureInterface
{

    public function load(\Doctrine\Common\Persistence\ObjectManager $manager)
    {
        $s = new \App\Entity\Seizoen();
        $s->setIdentifier("Seizoen 1");
        $manager->persist($s);

        $manager->flush();
    }
}