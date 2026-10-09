<?php declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Award;
use App\Entity\AwardType;
use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\User;
use App\Repository\AwardRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AwardControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $em;

    private Ploeg $ploeg;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(false);
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
            LoadUserData::class,
        ]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->ploeg = $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl1']);
        $this->ploeg->setSeizoen($this->em->getRepository(Seizoen::class)->findOneBy([]));
        $this->em->flush();

        $this->client->loginUser($this->findUser('beheer'));
    }

    private function findUser(string $username): User
    {
        return self::getContainer()->get(UserRepository::class)->loadUserByIdentifier($username);
    }

    /**
     * @return Award[]
     */
    private function findAwards(): array
    {
        $this->em->clear();
        return self::getContainer()->get(AwardRepository::class)->findAll();
    }

    public function testCreateAwardForPloeg(): void
    {
        $this->client->request('GET', '/admin/award/new');
        $this->client->submitForm('Create', ['award_form[ploeg]' => $this->ploeg->getId()]);

        $this->assertResponseRedirects('/admin/award/');
        $awards = $this->findAwards();
        $this->assertCount(1, $awards);
        $this->assertSame($this->ploeg->getId(), $awards[0]->getPloeg()->getId());
        $this->assertSame(AwardType::SeasonWinner, $awards[0]->getType());

        $this->client->request('GET', '/admin/award/');
        $this->assertSelectorTextContains('table', 'Ploeg 1');
    }

    public function testCreateAwardWithoutPloeg(): void
    {
        $this->client->request('GET', '/admin/award/new');
        $this->client->submitForm('Create', [
            'award_form[ownUser]' => $this->findUser('speler')->getId(),
            'award_form[ownSeason]' => 'Cyclear 2013',
            'award_form[ownTeam]' => 'CSC',
        ]);

        $this->assertResponseRedirects('/admin/award/');
        $awards = $this->findAwards();
        $this->assertCount(1, $awards);
        $this->assertNull($awards[0]->getPloeg());
        $this->assertSame('speler', $awards[0]->getUser()->getUsername());
        $this->assertSame('Cyclear 2013', $awards[0]->getSeason());
        $this->assertSame('CSC', $awards[0]->getTeam());
    }

    public function testPloegOrDetailsIsRequired(): void
    {
        $this->client->request('GET', '/admin/award/new');
        $this->client->submitForm('Create', ['award_form[ownSeason]' => 'Cyclear 2013']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Zonder ploeg zijn speler, seizoen en ploegnaam verplicht.');
        $this->assertCount(0, $this->findAwards());
    }

    public function testPloegAndDetailsAreExclusive(): void
    {
        $this->client->request('GET', '/admin/award/new');
        $this->client->submitForm('Create', [
            'award_form[ploeg]' => $this->ploeg->getId(),
            'award_form[ownTeam]' => 'CSC',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Kies een ploeg óf vul speler, seizoen en ploegnaam in, niet allebei.');
        $this->assertCount(0, $this->findAwards());
    }

    public function testDuplicateAwardForPloegIsRejected(): void
    {
        $this->em->persist(new Award($this->ploeg, AwardType::SeasonWinner));
        $this->em->flush();

        $this->client->request('GET', '/admin/award/new');
        $this->client->submitForm('Create', ['award_form[ploeg]' => $this->ploeg->getId()]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Deze ploeg heeft deze award al.');
        $this->assertCount(1, $this->findAwards());
    }

    public function testEditAndDeleteAward(): void
    {
        $award = new Award($this->ploeg, AwardType::SeasonWinner);
        $this->em->persist($award);
        $this->em->flush();
        $id = $award->getId();

        $this->client->request('GET', '/admin/award/' . $id . '/edit');
        $this->client->submitForm('Edit', [
            'award_form[ploeg]' => '',
            'award_form[ownUser]' => $this->findUser('andere')->getId(),
            'award_form[ownSeason]' => 'Cyclear 2012',
            'award_form[ownTeam]' => 'DUK',
        ]);
        $this->assertResponseRedirects('/admin/award/' . $id . '/edit');
        $awards = $this->findAwards();
        $this->assertNull($awards[0]->getPloeg());
        $this->assertSame('andere', $awards[0]->getUser()->getUsername());

        $this->client->request('GET', '/admin/award/' . $id . '/edit');
        $this->client->submitForm('Delete');
        $this->assertResponseRedirects('/admin/award/');
        $this->assertCount(0, $this->findAwards());
    }

    public function testRegularUserCannotOpenAwardAdmin(): void
    {
        $this->client->loginUser($this->findUser('speler'));
        $this->client->request('GET', '/admin/award/');

        $this->assertResponseStatusCodeSame(403);
    }
}
