<?php declare(strict_types=1);



namespace App\Tests\Fixtures;

use App\Entity\Renner;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadRennerData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $r1 = new Renner();
        $r1->setNaam('RENNER Voornaam');
        $manager->persist($r1);

        $r2 = new Renner();
        $r2->setNaam('RENNER2 Voornaam');
        $manager->persist($r2);

        $manager->flush();
    }
}
