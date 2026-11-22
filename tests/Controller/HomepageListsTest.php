<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The latest transfers and race results on the homepage, and what they cost in queries.
 */
class HomepageListsTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $em;

    private string $slug;

    private Seizoen $seizoen;

    private Ploeg $pl1;

    private Ploeg $pl2;

    private int $riders = 0;

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

    public function testRaceResultsMarkTransferredRidersOnly(): void
    {
        $this->reload();
        $drafted = $this->rider('Drafted Rider');
        $transferred = $this->rider('Transferred Rider');
        $this->transfer($drafted, $this->pl1, Transfer::DRAFTTRANSFER);
        // drafted by the other team, so only a transfer for pl1
        $this->transfer($transferred, $this->pl2, Transfer::DRAFTTRANSFER);
        $this->transfer($transferred, $this->pl1, Transfer::USERTRANSFER);
        $this->race('Race 1', [[$drafted, $this->pl1], [$transferred, $this->pl1]]);
        $this->flushAndClear();

        $rows = $this->homepage()->filter('#uitslagen + .container tr');
        $marked = $rows->reduce(fn (Crawler $row) => $row->filter('.fa-euro')->count() > 0);

        $this->assertStringContainsString('Drafted Rider', implode(' ', $rows->each(fn (Crawler $row) => $row->text())));
        $this->assertCount(1, $marked);
        $this->assertStringContainsString('Transferred Rider', $marked->text());
        $this->assertStringNotContainsString('Drafted Rider', $marked->text());
    }

    public function testLatestTransfersShowRiderInAndOut(): void
    {
        $this->reload();
        $in = $this->rider('Rider In');
        $out = $this->rider('Rider Out');
        $this->transfer($in, $this->pl2, Transfer::USERTRANSFER, $out);
        $this->flushAndClear();

        $rows = $this->homepage()->filter('#transfers + .container tr')
            ->reduce(fn (Crawler $row) => str_contains($row->text(), 'Rider In'));

        $this->assertCount(1, $rows);
        $cells = $rows->filter('td');
        $this->assertSame('pl2', trim($cells->eq(0)->text()));
        $this->assertStringContainsString('Rider In', $cells->eq(1)->text());
        $this->assertStringContainsString('Rider Out', $cells->eq(2)->text());
    }

    public function testQueryCountDoesNotGrowWithRacesResultsOrTransfers(): void
    {
        // the profiler counts every query on the connection since the kernel booted, fixtures included;
        // after this first request each one runs on a freshly booted kernel and counts only its own
        $this->homepage();
        $this->addRacesAndTransfers(1);
        $queriesForOne = $this->homepageQueryCount();

        $this->addRacesAndTransfers(4);
        $queriesForFive = $this->homepageQueryCount();

        $this->assertSame($queriesForOne, $queriesForFive);
    }

    private function addRacesAndTransfers(int $count): void
    {
        $this->reload();
        for ($i = 0; $i < $count; ++$i) {
            $drafted = $this->rider();
            $transferred = $this->rider();
            $this->transfer($drafted, $this->pl1, Transfer::DRAFTTRANSFER);
            $this->transfer($transferred, $this->pl2, Transfer::USERTRANSFER, $this->rider());
            $this->race('Race ' . $this->riders, [[$drafted, $this->pl1], [$transferred, $this->pl2], [$this->rider(), null]]);
        }
        $this->flushAndClear();
    }

    /**
     * Fetches the season and teams again from the container's current entity manager, to write test data with.
     */
    private function reload(): void
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
    private function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }

    private function homepageQueryCount(): int
    {
        $this->client->enableProfiler();
        $this->homepage();

        return $this->client->getProfile()->getCollector('db')->getQueryCount();
    }

    private function homepage(): Crawler
    {
        $crawler = $this->client->request('GET', '/' . $this->slug . '/');
        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    private function rider(?string $naam = null): Renner
    {
        $renner = new Renner();
        $renner->setNaam($naam ?? 'Renner ' . ++$this->riders);
        $this->em->persist($renner);

        return $renner;
    }

    private function transfer(Renner $renner, Ploeg $ploeg, int $type, ?Renner $out = null): void
    {
        $transfer = $this->newTransfer($renner, $ploeg, null, $type);
        if (null !== $out) {
            $inversion = $this->newTransfer($out, null, $ploeg, $type);
            $transfer->setInversionTransfer($inversion);
            $inversion->setInversionTransfer($transfer);
        }
    }

    private function newTransfer(Renner $renner, ?Ploeg $naar, ?Ploeg $van, int $type): Transfer
    {
        $transfer = new Transfer();
        $transfer->setRenner($renner);
        $transfer->setPloegNaar($naar);
        $transfer->setPloegVan($van);
        $transfer->setTransferType($type);
        $transfer->setSeizoen($this->seizoen);
        $transfer->setDatum(new \DateTime());
        $this->em->persist($transfer);

        return $transfer;
    }

    /**
     * @param array<array{Renner, ?Ploeg}> $results
     */
    private function race(string $naam, array $results): void
    {
        $wedstrijd = new Wedstrijd();
        $wedstrijd->setNaam($naam);
        $wedstrijd->setDatum(new \DateTime());
        $wedstrijd->setSeizoen($this->seizoen);
        $this->em->persist($wedstrijd);
        foreach ($results as $positie => [$renner, $ploeg]) {
            $uitslag = new Uitslag();
            $uitslag->setWedstrijd($wedstrijd);
            $uitslag->setRenner($renner);
            $uitslag->setPloeg($ploeg);
            $uitslag->setPositie($positie + 1);
            $uitslag->setRennerPunten(10);
            $uitslag->setPloegPunten(null === $ploeg ? 0 : 10);
            $wedstrijd->addUitslag($uitslag);
            $this->em->persist($uitslag);
        }
    }
}
