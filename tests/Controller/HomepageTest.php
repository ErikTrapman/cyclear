<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Repository\UserRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The standings at the top of the homepage.
 */
class HomepageTest extends WebTestCase
{
    private KernelBrowser $client;

    private Seizoen $seizoen;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
            LoadUserData::class,
        ]);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $this->seizoen = $em->getRepository(Seizoen::class)->findOneBy([]);
        $this->seizoen->setCurrent(true);
        foreach (['pl3', 'pl4'] as $afkorting) {
            $ploeg = new Ploeg();
            $ploeg->setNaam('Ploeg ' . $afkorting);
            $ploeg->setAfkorting($afkorting);
            $em->persist($ploeg);
        }
        $em->flush();
        // without results all teams have 0 points and are ordered by abbreviation: pl1, pl2, pl3, pl4
        foreach ($em->getRepository(Ploeg::class)->findAll() as $ploeg) {
            $ploeg->setSeizoen($this->seizoen);
        }
        $em->flush();
    }

    private function standingsRows(): Crawler
    {
        $crawler = $this->client->request('GET', '/' . $this->seizoen->getSlug() . '/');
        $this->assertResponseIsSuccessful();

        return $crawler->filter('#stand table tr')->reduce(fn (Crawler $row) => $row->filter('td')->count() > 0);
    }

    public function testTopThreeGetATrophy(): void
    {
        $rows = $this->standingsRows();

        $this->assertCount(4, $rows);
        $this->assertCount(1, $rows->eq(0)->filter('.fa-trophy.standings-trophy-1'));
        $this->assertCount(1, $rows->eq(1)->filter('.fa-trophy.standings-trophy-2'));
        $this->assertCount(1, $rows->eq(2)->filter('.fa-trophy.standings-trophy-3'));
        $this->assertCount(0, $rows->eq(3)->filter('.fa-trophy'));
    }

    public function testOwnTeamIsMarked(): void
    {
        // warm the standings cache first, so the marked row comes from unserialized teams
        $this->standingsRows();
        $this->client->loginUser(self::getContainer()->get(UserRepository::class)->loadUserByIdentifier('speler'));

        $rows = $this->standingsRows();

        $marked = $rows->filter('tr.my-team');
        $this->assertCount(1, $marked);
        $this->assertStringContainsString('pl1', $marked->text());
    }

    public function testNoTeamIsMarkedForVisitors(): void
    {
        $this->assertCount(0, $this->standingsRows()->filter('tr.my-team'));
    }
}
