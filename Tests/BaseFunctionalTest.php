<?php

namespace Cyclear\GameBundle\Tests;

class BaseFunctionalTest extends \Liip\FunctionalTestBundle\Test\WebTestCase
{

    public function setUp()
    {
        
    }

    public function doLoadFixtures()
    {
        $fixtures = array(
            'Cyclear\GameBundle\Tests\Fixtures\LoadSeizoenData',
            'Cyclear\GameBundle\Tests\Fixtures\LoadRennerData',
            'Cyclear\GameBundle\Tests\Fixtures\LoadPloegData',
        );
        $this->loadFixtures($fixtures);
    }
}