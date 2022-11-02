<?php declare(strict_types=1);



namespace App\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadSeizoenData extends Fixture
{
    public function load(\Doctrine\Persistence\ObjectManager $manager)
    {
        $s = new \App\Entity\Seizoen();
        $s->setIdentifier('Seizoen 1');
        $manager->persist($s);

        $manager->flush();
    }
}
