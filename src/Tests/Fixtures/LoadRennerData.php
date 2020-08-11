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

use App\Entity\Renner;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;


class LoadRennerData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $r1 = new Renner();
        $r1->setNaam("RENNER Voornaam");
        $manager->persist($r1);

        $r2 = new Renner();
        $r2->setNaam("RENNER2 Voornaam");
        $manager->persist($r2);

        $manager->flush();
    }
}
