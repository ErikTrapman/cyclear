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

use App\Entity\Ploeg;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadPloegData extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $p1 = new Ploeg();
        $p1->setNaam("Ploeg 1");
        $p1->setAfkorting("pl1");
        $manager->persist($p1);

        $p2 = new Ploeg();
        $p2->setNaam("Ploeg 2");
        $p2->setAfkorting("pl2");
        $manager->persist($p2);

        $manager->flush();
    }

}
