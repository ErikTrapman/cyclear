<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQ;


use App\CQ\CQAutomaticResultsResolver;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CQAutomaticResultsResolverTest extends WebTestCase
{

    public function testResolvingBetweenDates()
    {
        $client = static::createClient();
        $parser = $client->getContainer()->get('eriktrapman_cqparser.recentracesparser');

        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'recentraces-20151029.html');
        $races = $parser->getRecentRaces($content, new \DateTime(date('Y') . '-12-31'));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $wedstrijdRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $em->expects($this->at(0))->method('getRepository')->with(Wedstrijd::class)->willReturn($wedstrijdRepo);
        $wedstrijdRepo->method('findOneByExternalIdentifier')->willReturn(null);

        $ploegRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $em->expects($this->at(1))->method('getRepository')->with(Ploeg::class)->willReturn($ploegRepo);
        $ploeg = new Ploeg();
        $ploegRepo->method('find')->willReturn($ploeg);

        $type = new UitslagType();
        $type->setMaxResults(1);
        $categoryMatcher = $this->getMockBuilder('App\CQ\RaceCategoryMatcher')->disableOriginalConstructor()->getMock();
        $categoryMatcher->method('getUitslagTypeAccordingToCategory')
            ->willReturn($type);
        $categoryMatcher->method('needsRefStage')->willReturn(false);

        $uitslagen = [
            ['ploeg' => 1, 'rennerPunten' => 10, 'ploegPunten' => 10, 'renner' => 1, 'positie' => 1]
        ];
        $uitslagManager = $this->getMockBuilder('App\EntityManager\UitslagManager')->disableOriginalConstructor()->getMock();
        $uitslagManager->method('prepareUitslagen')->willReturn($uitslagen);
        $crawlerManager = $this->getMockBuilder('ErikTrapman\Bundle\CQRankingParserBundle\Parser\Crawler\CrawlerManager')->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();

        $renner = new Renner();
        $transformer = $this->getMockBuilder('App\Form\DataTransformer\RennerNameToRennerIdTransformer')->disableOriginalConstructor()->getMock();
        $transformer->method('reverseTransform')->willReturn($renner);
        $resolver = new CQAutomaticResultsResolver($em, $categoryMatcher, $uitslagManager, $crawlerManager,
            $transformer, $logger);

        $seizoen = new Seizoen();
        // cqranking parses recent races as per this year (see http://cqranking.com/men/asp/gen/RacesRecent.asp?changed=0)
        // as there is no indication of what year the race has been held in.
        $start = new \DateTime(date('Y') . '-09-27');
        $end = new \DateTime(date('Y') . '-10-04');
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        $res = $resolver->resolve($races, $seizoen, $start, $end, 999);
        $this->assertCount(32, $res);

    }

}