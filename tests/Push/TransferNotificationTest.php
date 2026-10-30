<?php declare(strict_types=1);

namespace App\Tests\Push;

use App\Entity\Ploeg;
use App\Entity\PushSubscription;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\User;
use App\EntityManager\TransferManager;
use App\Message\TransferNotification;
use App\MessageHandler\TransferNotificationHandler;
use App\Repository\PushSubscriptionRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadRennerData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * A transfer made in the game reaches the browsers of the other managers.
 */
class TransferNotificationTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $em;

    private Seizoen $seizoen;

    private Renner $ownRider;

    private Renner $freeRider;

    protected function setUp(): void
    {
        $this->client = static::createClient();
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
        $ploeg = $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl1']);
        $ploeg->setSeizoen($this->seizoen);
        $this->em->flush();

        $this->ownRider = $this->em->getRepository(Renner::class)->findOneBy(['naam' => 'RENNER Voornaam']);
        $this->freeRider = $this->em->getRepository(Renner::class)->findOneBy(['naam' => 'RENNER2 Voornaam']);
        $transfer = new Transfer();
        $transfer->setDatum(new \DateTime('-1 week'));
        $transfer->setPloegNaar($ploeg);
        $transfer->setRenner($this->ownRider);
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

    private function subscribe(string $username, TestBrowser $browser): void
    {
        $this->em->persist(new PushSubscription($this->findUser($username), $browser->endpoint, $browser->p256dh, $browser->auth));
        $this->em->flush();
    }

    private function makeTransfer(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $crawler = $this->client->request('GET', '/' . $this->seizoen->getSlug() . '/spelregels');
        $team = json_decode($crawler->filter('#palette-config')->text(), true, 512, JSON_THROW_ON_ERROR)['team'];
        $this->client->request('POST', $team['transferUrl'], server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $team['token'],
        ], content: json_encode(['rennerIn' => $this->freeRider->getSlug(), 'rennerUit' => $this->ownRider->getSlug(), 'comment' => 'Klassiekerspecialist'], JSON_THROW_ON_ERROR));
        $this->assertResponseIsSuccessful();
    }

    /**
     * What the Messenger worker does with the queued notifications.
     */
    private function consume(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $handler = self::getContainer()->get(TransferNotificationHandler::class);
        foreach ($transport->getSent() as $envelope) {
            $handler($envelope->getMessage());
        }
    }

    public function testOtherManagersAreNotified(): void
    {
        $other = new TestBrowser('https://updates.push.services.mozilla.com/wpush/v2/andere');
        $own = new TestBrowser('https://fcm.googleapis.com/fcm/send/speler');
        $this->subscribe('andere', $other);
        $this->subscribe('speler', $own);

        $this->makeTransfer();

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        // Only the transfer the manager made; the draft in setUp is left out
        $this->assertCount(1, $sent);
        $this->assertInstanceOf(TransferNotification::class, $sent[0]->getMessage());

        $this->consume();
        $pushed = self::getContainer()->get(PushServiceMock::class)->sent;
        $this->assertSame([$other->endpoint], array_keys($pushed));
        $this->assertSame([
            'title' => 'Ploeg 1 haalt RENNER2 Voornaam binnen',
            'body' => 'RENNER Voornaam vertrekt. "Klassiekerspecialist"',
            'url' => '/' . $this->seizoen->getSlug() . '/transfer',
        ], $other->decrypt($pushed[$other->endpoint]));
    }

    public function testRevokedSubscriptionIsRemoved(): void
    {
        $this->subscribe('andere', new TestBrowser('https://fcm.googleapis.com/fcm/send/expired'));

        $this->makeTransfer();
        $this->consume();

        $this->em->clear();
        $this->assertSame([], self::getContainer()->get(PushSubscriptionRepository::class)->findAll());
    }

    public function testRevertedTransferIsNotAnnounced(): void
    {
        $this->subscribe('andere', new TestBrowser('https://fcm.googleapis.com/fcm/send/andere'));

        self::getContainer()->get(TransferNotificationHandler::class)(new TransferNotification(999999));

        $this->assertSame([], self::getContainer()->get(PushServiceMock::class)->sent);
    }
}
