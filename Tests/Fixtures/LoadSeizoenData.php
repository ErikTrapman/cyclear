<?php

namespace Cyclear\GameBundle\Tests\Fixtures;

class LoadSeizoenData implements \Doctrine\Common\DataFixtures\FixtureInterface
{

    public function load(\Doctrine\Common\Persistence\ObjectManager $manager)
    {
        $s = new \Cyclear\GameBundle\Entity\Seizoen();
        $s->setIdentifier("Seizoen 1");
        $manager->persist($s);

        $manager->flush();
    }
}