<?php

namespace Cyclear\GameBundle\Tests\Fixtures;

use Cyclear\GameBundle\Entity\Ploeg;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPloegData implements FixtureInterface
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