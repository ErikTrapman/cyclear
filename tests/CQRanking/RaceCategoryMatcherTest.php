<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQRanking;


use App\CQRanking\RaceCategoryMatcher;
use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RaceCategoryMatcherTest extends WebTestCase
{

    private function getEm()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider successDataProvider
     */
    public function testUitslagtypeLookupAccordingToCategorySucceeds($pattern, $lookup)
    {
        $t = new UitslagType();
        $t->setAutomaticResolvingCategories($pattern);

        $em = $this->getEm();
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('getRepository')->with(UitslagType::class)->willReturn($repo);
        $repo->expects($this->once())->method('findAll')->willReturn([$t]);

        $matcher = new RaceCategoryMatcher($em);
        $res = $matcher->getUitslagTypeAccordingToCategory($lookup);
        $this->assertSame($t, $res);
    }

    public function successDataProvider()
    {
        return [
            ['2.HCs, 2.1s, GT2s', '2.1s'],
            ['2.HCs, 2.1s, GT2s', '2.HCs'],
            ['2.HCs, 2.1s, GT2s', 'GT2s']
        ];
    }

    /**
     * @dataProvider failDataProvider
     */
    public function testUitslagtypeLookupAccordingToCategoryFails($pattern, $lookup)
    {
        $t = new UitslagType();
        $t->setAutomaticResolvingCategories($pattern);

        $em = $this->getEm();
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('getRepository')->with(UitslagType::class)->willReturn($repo);
        $repo->expects($this->once())->method('findAll')->willReturn([$t]);

        $matcher = new RaceCategoryMatcher($em);
        $this->assertNull($matcher->getUitslagTypeAccordingToCategory($lookup));
    }

    public function failDataProvider()
    {
        return [
            ['2.HCs,2.1s,GT2s', '2.1'],
            ['2.HCs,2.1s,GT2s', '2.HC'],
            ['2.HCs,2.1s,GT2s', 'GT2'],
            ['2.HCs,2.1s,GT2s', '2.2s'],
            ['2.HCs,2.1s,GT2s', '1.HCs']
        ];
    }

    /**
     * @dataProvider refStageDataProvider
     */
    public function testNeedsRefStage($name, $res)
    {
        $em = $this->getEm();
        $matcher = new RaceCategoryMatcher($em);
        $wedstrijd = new Wedstrijd();
        $wedstrijd->setNaam($name);
        $this->assertEquals($res, $matcher->needsRefStage($wedstrijd));
    }

    public function refStageDataProvider()
    {
        return [
            ['Vuelta a Espa?a, General classification', true],
            ['General classification, Vuelta a Espa?a', true],
            ['Vuelta a Espa?a, Stage 7 : Jodar - La Alpujarra', false],
            ['Volta a Catalunya, General classification', true],
            ['Dubai Tour, Stage 3 : Dubai - Hatta (205 km)', false]
        ];
    }

}
