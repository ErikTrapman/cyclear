<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\PushSubscription;
use App\Entity\Seizoen;
use App\Entity\User;
use App\Repository\PushSubscriptionRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use App\Tests\Push\TestBrowser;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Covers turning push notifications on and off the way public/js/push.js does.
 */
class PushTest extends WebTestCase
{
    private const string ENDPOINT = 'https://fcm.googleapis.com/fcm/send/abc123';

    private KernelBrowser $client;

    private Seizoen $seizoen;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(false);
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
            LoadUserData::class,
        ]);
        $this->seizoen = self::getContainer()->get('doctrine')->getRepository(Seizoen::class)->findOneBy([]);
    }

    private function findUser(string $username): User
    {
        return self::getContainer()->get(UserRepository::class)->loadUserByIdentifier($username);
    }

    /**
     * @return array<string, mixed>|null the push configuration rendered into every page
     */
    private function pushConfig(): ?array
    {
        $crawler = $this->client->request('GET', '/' . $this->seizoen->getSlug() . '/spelregels');
        $this->assertResponseIsSuccessful();
        $config = $crawler->filter('#push-config');
        return 0 === $config->count() ? null : json_decode($config->text(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return PushSubscription[]
     */
    private function subscriptions(): array
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->clear();
        return self::getContainer()->get(PushSubscriptionRepository::class)->findAll();
    }

    private function post(string $url, string $token, TestBrowser $browser): void
    {
        $this->client->request('POST', $url, server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $token,
        ], content: json_encode(['endpoint' => $browser->endpoint, 'p256dh' => $browser->p256dh, 'auth' => $browser->auth], JSON_THROW_ON_ERROR));
    }

    public function testLoggedOutVisitorGetsNoToggle(): void
    {
        $this->assertNull($this->pushConfig());
        $this->assertSelectorNotExists('[data-push-toggle]');
    }

    public function testSubscribeAndUnsubscribe(): void
    {
        $speler = $this->findUser('speler');
        $this->client->loginUser($speler);
        $config = $this->pushConfig();
        $this->assertSelectorExists('[data-push-toggle]');
        $this->assertSame($_SERVER['VAPID_PUBLIC_KEY'], $config['publicKey']);
        $this->assertSame($speler->getId(), $config['user']);

        $browser = new TestBrowser(self::ENDPOINT);
        $this->post($config['subscribeUrl'], $config['token'], $browser);
        $this->assertResponseStatusCodeSame(204);
        // Posted again on a later page load
        $this->post($config['subscribeUrl'], $config['token'], $browser);
        $this->assertResponseStatusCodeSame(204);

        $subscriptions = $this->subscriptions();
        $this->assertCount(1, $subscriptions);
        $this->assertSame(self::ENDPOINT, $subscriptions[0]->getEndpoint());
        $this->assertSame($browser->p256dh, $subscriptions[0]->getP256dh());
        $this->assertSame('speler', $subscriptions[0]->getUser()->getUsername());

        $this->post($config['unsubscribeUrl'], $config['token'], $browser);
        $this->assertResponseStatusCodeSame(204);
        $this->assertSame([], $this->subscriptions());
    }

    public function testBrowserMovesToTheUserLoggedInOnIt(): void
    {
        $browser = new TestBrowser(self::ENDPOINT);
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->pushConfig();
        $this->post($config['subscribeUrl'], $config['token'], $browser);

        $this->client->loginUser($this->findUser('andere'));
        $config = $this->pushConfig();
        // Someone else can't turn off this browser
        $this->post($config['unsubscribeUrl'], $config['token'], $browser);
        $this->assertCount(1, $this->subscriptions());

        $this->post($config['subscribeUrl'], $config['token'], $browser);
        $subscriptions = $this->subscriptions();
        $this->assertCount(1, $subscriptions);
        $this->assertSame('andere', $subscriptions[0]->getUser()->getUsername());
    }

    public function testOnlyKnownPushServices(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->pushConfig();

        $this->post($config['subscribeUrl'], $config['token'], new TestBrowser('http://169.254.169.254/latest/meta-data'));

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame([], $this->subscriptions());
    }

    public function testNeedsValidToken(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $config = $this->pushConfig();

        $this->post($config['subscribeUrl'], 'ongeldig', new TestBrowser(self::ENDPOINT));

        $this->assertResponseStatusCodeSame(403);
        $this->assertSame([], $this->subscriptions());
    }

    public function testNeedsLogin(): void
    {
        $this->post('/user/push/subscribe', 'ongeldig', new TestBrowser(self::ENDPOINT));

        $this->assertResponseRedirects(self::getContainer()->get('router')->generate('app_login', referenceType: UrlGeneratorInterface::ABSOLUTE_URL));
        $this->assertSame([], $this->subscriptions());
    }
}
