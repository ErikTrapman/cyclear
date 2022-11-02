<?php declare(strict_types=1);



namespace App\Tests;

class BaseFunctional extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    protected function setUp(): void
    {
    }

    public function doLoadFixtures()
    {
        $fixtures = [
            'App\Tests\Fixtures\LoadSeizoenData',
            'App\Tests\Fixtures\LoadRennerData',
            'App\Tests\Fixtures\LoadPloegData',
        ];
        $this->loadFixtures($fixtures);
    }
}
