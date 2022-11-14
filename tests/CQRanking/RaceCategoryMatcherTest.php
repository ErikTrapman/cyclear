<?php declare(strict_types=1);

namespace App\Tests\CQRanking;

use App\CQRanking\RaceCategoryMatcher;
use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RaceCategoryMatcherTest extends WebTestCase
{
    private function getEm(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider successDataProvider
     *
     * @param mixed $pattern
     * @param mixed $lookup
     */
    public function testUitslagtypeLookupAccordingToCategorySucceeds($pattern, $lookup): void
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

    /**
     * @return string[][]
     *
     * @psalm-return array{0: array{0: '2.HCs, 2.1s, GT2s', 1: '2.1s'}, 1: array{0: '2.HCs, 2.1s, GT2s', 1: '2.HCs'}, 2: array{0: '2.HCs, 2.1s, GT2s', 1: 'GT2s'}}
     */
    public function successDataProvider(): array
    {
        return [
            ['2.HCs, 2.1s, GT2s', '2.1s'],
            ['2.HCs, 2.1s, GT2s', '2.HCs'],
            ['2.HCs, 2.1s, GT2s', 'GT2s'],
        ];
    }

    /**
     * @dataProvider failDataProvider
     *
     * @param mixed $pattern
     * @param mixed $lookup
     */
    public function testUitslagtypeLookupAccordingToCategoryFails($pattern, $lookup): void
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

    /**
     * @return string[][]
     *
     * @psalm-return array{0: array{0: '2.HCs,2.1s,GT2s', 1: '2.1'}, 1: array{0: '2.HCs,2.1s,GT2s', 1: '2.HC'}, 2: array{0: '2.HCs,2.1s,GT2s', 1: 'GT2'}, 3: array{0: '2.HCs,2.1s,GT2s', 1: '2.2s'}, 4: array{0: '2.HCs,2.1s,GT2s', 1: '1.HCs'}}
     */
    public function failDataProvider(): array
    {
        return [
            ['2.HCs,2.1s,GT2s', '2.1'],
            ['2.HCs,2.1s,GT2s', '2.HC'],
            ['2.HCs,2.1s,GT2s', 'GT2'],
            ['2.HCs,2.1s,GT2s', '2.2s'],
            ['2.HCs,2.1s,GT2s', '1.HCs'],
        ];
    }

    /**
     * @dataProvider refStageDataProvider
     *
     * @param mixed $name
     * @param mixed $res
     */
    public function testNeedsRefStage($name, $res): void
    {
        $em = $this->getEm();
        $matcher = new RaceCategoryMatcher($em);
        $wedstrijd = new Wedstrijd();
        $wedstrijd->setNaam($name);
        $this->assertEquals($res, $matcher->needsRefStage($wedstrijd));
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return array{0: array{0: 'Vuelta a Espa?a, General classification', 1: true}, 1: array{0: 'General classification, Vuelta a Espa?a', 1: true}, 2: array{0: 'Vuelta a Espa?a, Stage 7 : Jodar - La Alpujarra', 1: false}, 3: array{0: 'Volta a Catalunya, General classification', 1: true}, 4: array{0: 'Dubai Tour, Stage 3 : Dubai - Hatta (205 km)', 1: false}}
     */
    public function refStageDataProvider(): array
    {
        return [
            ['Vuelta a Espa?a, General classification', true],
            ['General classification, Vuelta a Espa?a', true],
            ['Vuelta a Espa?a, Stage 7 : Jodar - La Alpujarra', false],
            ['Volta a Catalunya, General classification', true],
            ['Dubai Tour, Stage 3 : Dubai - Hatta (205 km)', false],
        ];
    }
}
