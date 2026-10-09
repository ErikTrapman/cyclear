<?php declare(strict_types=1);

namespace App\Tests\CQRanking;

use App\CQRanking\CQAutomaticResultsResolver;
use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\RecentRaces\RecentRacesParser;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\UitslagType;
use App\Repository\PloegRepository;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CQAutomaticResultsResolverTest extends WebTestCase
{
    public function testResolvingBetweenDates(): void
    {
        $client = static::createClient();
        $parser = $client->getContainer()->get(RecentRacesParser::class);

        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'recentraces-20151029.html');
        $races = $parser->getRecentRaces($content, new \DateTime(date('Y') . '-12-31'));

        $wedstrijdRepo = $this->createStub('App\Repository\WedstrijdRepository');

        $ploegRepo = $this->createStub(PloegRepository::class);

        $ploeg = new Ploeg();
        $ploegRepo->method('find')->willReturn($ploeg);

        $type = new UitslagType();
        $type->setMaxResults(1);
        $categoryMatcher = $this->createStub('App\CQRanking\RaceCategoryMatcher');
        $categoryMatcher->method('getUitslagTypeAccordingToCategory')
            ->willReturn($type);
        $categoryMatcher->method('needsRefStage')->willReturn(false);

        $uitslagen = [
            ['ploeg' => 1, 'rennerPunten' => 10, 'ploegPunten' => 10, 'renner' => 1, 'positie' => 1],
        ];
        $uitslagManager = $this->createStub('App\EntityManager\UitslagManager');
        $uitslagManager->method('prepareUitslagen')->willReturn($uitslagen);
        $logger = $this->createStub(Logger::class);

        $renner = new Renner();
        $transformer = $this->createStub('App\Form\DataTransformer\RennerNameToRennerIdTransformer');
        $transformer->method('reverseTransform')->willReturn($renner);
        $resolver = new CQAutomaticResultsResolver($categoryMatcher, $uitslagManager, new CrawlerManager(), $transformer, $logger, $wedstrijdRepo, $ploegRepo);
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
