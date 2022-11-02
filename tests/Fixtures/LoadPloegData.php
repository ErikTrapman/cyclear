<?php declare(strict_types=1);



namespace App\Tests\Fixtures;

use App\Entity\Ploeg;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadPloegData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $p1 = new Ploeg();
        $p1->setNaam('Ploeg 1');
        $p1->setAfkorting('pl1');
        $manager->persist($p1);

        $p2 = new Ploeg();
        $p2->setNaam('Ploeg 2');
        $p2->setAfkorting('pl2');
        $manager->persist($p2);

        $manager->flush();
    }
}
