<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Seizoen;
use App\Tests\Fixtures\LoadSeizoenData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * The title ribbon on top of each page takes the leader's jersey color while a grand tour is on.
 */
class TitleRibbonTest extends WebTestCase
{
    use ClockSensitiveTrait;

    public static function months(): iterable
    {
        yield 'giro' => ['2026-05-15', 'grand-tour-giro'];
        yield 'tour' => ['2026-07-15', 'grand-tour-tour'];
        yield 'vuelta' => ['2026-09-15', 'grand-tour-vuelta'];
        yield 'no grand tour' => ['2026-10-15', null];
    }

    #[DataProvider('months')]
    public function testRibbonFollowsTheGrandTourOfTheMonth(string $date, ?string $bodyClass): void
    {
        $client = static::createClient();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([LoadSeizoenData::class]);
        $seizoen = self::getContainer()->get('doctrine')->getRepository(Seizoen::class)->findOneBy([]);
        self::mockTime($date);

        $crawler = $client->request('GET', '/' . $seizoen->getSlug() . '/spelregels');

        $this->assertResponseIsSuccessful();
        $this->assertSame($bodyClass, $crawler->filter('body')->attr('class'));
    }
}
