<?php declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * A current season with teams pl1 and pl2, game data helpers, and requests that start
 * with an empty entity manager so they load (and count) everything like a real request.
 */
abstract class GameWebTestCase extends WebTestCase
{
    use BuildsGameData;

    protected KernelBrowser $client;

    protected EntityManagerInterface $em;

    protected string $slug;

    protected Seizoen $seizoen;

    protected Ploeg $pl1;

    protected Ploeg $pl2;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
        ]);
        $this->reload();
        $this->seizoen->setCurrent(true);
        $this->pl1->setSeizoen($this->seizoen);
        $this->pl2->setSeizoen($this->seizoen);
        $this->slug = $this->seizoen->getSlug();
        $this->flushAndClear();
    }

    /**
     * Fetches the season and teams again from the container's current entity manager, to write test data with.
     */
    protected function reload(): void
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->seizoen = $this->em->getRepository(Seizoen::class)->findOneBy([]);
        $this->pl1 = $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl1']);
        $this->pl2 = $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl2']);
    }

    /**
     * The request shares the test's entity manager; starting it empty makes it load
     * (and count) everything the way a real request does.
     */
    protected function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }

    protected function get(string $path): Crawler
    {
        $crawler = $this->client->request('GET', '/' . $this->slug . $path);
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    /**
     * The queries a request runs. The profiler counts every query on the connection since the kernel booted,
     * so a test first makes one request; every later request runs on a freshly booted kernel.
     */
    protected function queryCount(string $path): int
    {
        $this->client->enableProfiler();
        $this->get($path);

        return $this->client->getProfile()->getCollector('db')->getQueryCount();
    }

    /**
     * The cell texts of the one row in $table that has a cell reading $text.
     *
     * @return string[]
     */
    protected function cellsOfRowWith(Crawler $table, string $text): array
    {
        $rows = $table->filter('tr')->reduce(fn (Crawler $row) => $row->filter('td')->count() > 0
            && in_array($text, $row->filter('td')->each(fn (Crawler $td) => trim($td->text())), true));
        $this->assertCount(1, $rows, "one row with $text");

        return $rows->filter('td')->each(fn (Crawler $td) => trim($td->text()));
    }

    protected function gameDataManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function gameDataSeason(): Seizoen
    {
        return $this->seizoen;
    }
}
