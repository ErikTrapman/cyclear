<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\User;
use App\EntityManager\TransferManager;
use App\Repository\RennerRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadRennerData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Covers the endpoints of the command palette (public/js/palette.js) the way the palette calls them.
 */
class PaletteTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $em;

    private Seizoen $seizoen;

    private Ploeg $ploeg;

    private Renner $ownRider;

    private Renner $freeRider;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(false);
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
            LoadUserData::class,
            LoadRennerData::class,
        ]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->seizoen = $this->em->getRepository(Seizoen::class)->findOneBy([]);
        $this->seizoen->setCurrent(true);
        $this->seizoen->setStart(new \DateTime('-1 month'));
        $this->seizoen->setEnd(new \DateTime('+1 month'));
        $this->seizoen->setMaxTransfers(5);

        $this->ploeg = $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl1']);
        $this->ploeg->setSeizoen($this->seizoen);
        $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl2'])->setSeizoen($this->seizoen);
        $this->em->flush();

        $this->ownRider = $this->em->getRepository(Renner::class)->findOneBy(['naam' => 'RENNER Voornaam']);
        $this->freeRider = $this->em->getRepository(Renner::class)->findOneBy(['naam' => 'RENNER2 Voornaam']);
        $this->draft($this->ownRider, $this->ploeg);
    }

    private function draft(Renner $renner, Ploeg $ploeg): void
    {
        $transfer = new Transfer();
        $transfer->setDatum(new \DateTime('-1 week'));
        $transfer->setPloegNaar($ploeg);
        $transfer->setRenner($renner);
        $transfer->setSeizoen($this->seizoen);
        $transfer->setTransferType(Transfer::DRAFTTRANSFER);
        $this->em->persist($transfer);
        $this->em->flush();
        self::getContainer()->get(TransferManager::class)->doDraftTransfer($transfer);
        $this->em->flush();
    }

    private function findUser(string $username): User
    {
        return self::getContainer()->get(UserRepository::class)->loadUserByIdentifier($username);
    }

    /**
     * @return array<string, mixed> the palette configuration rendered into every page
     */
    private function paletteConfig(): array
    {
        $crawler = $this->client->request('GET', '/' . $this->seizoen->getSlug() . '/spelregels');
        $this->assertResponseIsSuccessful();
        return json_decode($crawler->filter('#palette-config')->text(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private function getJson(string $url): array
    {
        $this->client->request('GET', $url, server: ['HTTP_ACCEPT' => 'application/json']);
        return json_decode((string)$this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function postTransfer(array $config, array $payload, ?string $token = null): array
    {
        $this->client->request('POST', $config['team']['transferUrl'], server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $token ?? $config['team']['token'],
        ], content: json_encode($payload, JSON_THROW_ON_ERROR));
        return json_decode((string)$this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function teamOf(Renner $renner): ?Ploeg
    {
        $this->em->clear();
        return self::getContainer()->get(RennerRepository::class)->getPloeg($renner->getId(), $this->em->find(Seizoen::class, $this->seizoen->getId()));
    }

    public function testSearchShowsOwnerTeamAndAvailability(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $riders = $this->getJson($config['ridersUrl'] . '?q=renner')['riders'];
        $this->assertResponseIsSuccessful();
        $bySlug = array_column($riders, null, 'slug');
        $this->assertSame('pl1', $bySlug[$this->ownRider->getSlug()]['team']['abbreviation']);
        $this->assertTrue($bySlug[$this->ownRider->getSlug()]['mine']);
        $this->assertNull($bySlug[$this->freeRider->getSlug()]['team']);
        $this->assertFalse($bySlug[$this->freeRider->getSlug()]['mine']);

        $available = $this->getJson($config['ridersUrl'] . '?available=1&q=renner')['riders'];
        $this->assertSame([$this->freeRider->getSlug()], array_column($available, 'slug'));

        $this->assertSame([], $this->getJson($config['ridersUrl'] . '?q=r')['riders']);
    }

    public function testLoggedOutVisitorGetsNoTransferActions(): void
    {
        $config = $this->paletteConfig();

        $this->assertNull($config['team']);
        $this->assertContains('Archief', array_column($config['links'], 'label'));
    }

    public function testTeamRidersAreOnlyForTheOwner(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        // joins the team last, but comes first alphabetically
        $first = new Renner();
        $first->setNaam('AARTS Eerste');
        $this->em->persist($first);
        $this->em->flush();
        $this->draft($first, $this->ploeg);

        $team = $this->getJson($config['team']['ridersUrl']);
        $this->assertResponseIsSuccessful();
        $this->assertSame([$first->getSlug(), $this->ownRider->getSlug()], array_column($team['riders'], 'slug'));
        $this->assertSame(5, $team['transfersLeft']);

        $this->client->loginUser($this->findUser('andere'));
        $this->client->request('GET', $config['team']['ridersUrl']);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testTransferThroughPalette(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $result = $this->postTransfer($config, [
            'rennerIn' => $this->freeRider->getSlug(),
            'rennerUit' => $this->ownRider->getSlug(),
            'comment' => 'Via het palet',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSame('/' . $this->seizoen->getSlug() . '/ploeg/' . $this->ploeg->getId() . '/show', $result['url']);
        $this->assertSame($this->ploeg->getId(), $this->teamOf($this->freeRider)?->getId());
        $this->assertNull($this->teamOf($this->ownRider));
        $incoming = $this->em->getRepository(Transfer::class)->findOneBy(['renner' => $this->freeRider->getId(), 'transferType' => Transfer::USERTRANSFER]);
        $this->assertSame('Via het palet', $incoming->getUserComment());
    }

    public function testTransferNeedsValidToken(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $result = $this->postTransfer($config, [
            'rennerIn' => $this->freeRider->getSlug(),
            'rennerUit' => $this->ownRider->getSlug(),
        ], 'ongeldig');

        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(['Je sessie is verlopen, herlaad de pagina.'], $result['errors']);
        $this->assertNull($this->teamOf($this->freeRider));
    }

    public function testOutgoingRiderMustBeInTheTeam(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $result = $this->postTransfer($config, [
            'rennerIn' => $this->freeRider->getSlug(),
            'rennerUit' => $this->freeRider->getSlug(),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame(['RENNER2 Voornaam zit niet in je ploeg.'], $result['errors']);
    }

    public function testIncomingRiderMustBeAvailable(): void
    {
        $this->draft($this->freeRider, $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl2']));
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $result = $this->postTransfer($config, [
            'rennerIn' => $this->freeRider->getSlug(),
            'rennerUit' => $this->ownRider->getSlug(),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame(['RENNER2 Voornaam heeft al een ploeg.'], $result['errors']);
    }

    public function testTransferLimitIsEnforced(): void
    {
        $this->seizoen->setMaxTransfers(0);
        $this->em->flush();
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->paletteConfig();

        $result = $this->postTransfer($config, [
            'rennerIn' => $this->freeRider->getSlug(),
            'rennerUit' => $this->ownRider->getSlug(),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame(['Je zit op het maximaal aantal transfers van 0.'], $result['errors']);
        $this->assertSame($this->ploeg->getId(), $this->teamOf($this->ownRider)?->getId());
    }
}
