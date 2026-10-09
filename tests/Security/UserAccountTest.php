<?php declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\Ploeg;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\PloegVoter;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Fixtures\LoadUserData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Covers the login, password reset, admin user management and team ownership that FOSUserBundle and the ACL bundle used to provide.
 */
class UserAccountTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(false);
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
            LoadUserData::class,
        ]);
    }

    private function findUser(string $username): User
    {
        return self::getContainer()->get(UserRepository::class)->loadUserByIdentifier($username);
    }

    private function login(string $username, string $password): void
    {
        $this->client->request('GET', '/login-user/login');
        $this->client->submitForm('Inloggen', ['_username' => $username, '_password' => $password]);
    }

    public function testLoginWithLegacyHashRehashesPassword(): void
    {
        $this->login('Speler', LoadUserData::PASSWORD);

        $this->assertResponseRedirects('http://localhost/');
        $user = $this->findUser('speler');
        $this->assertNull($user->getSalt());
        $this->assertStringStartsWith('$', $user->getPassword(), 'Password should be rehashed with the auto hasher');
        $this->assertNotNull($user->getLastLogin());

        // The rehashed password keeps working.
        $this->client->request('GET', '/logout');
        $this->login('speler', LoadUserData::PASSWORD);
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->login('speler', 'fout');

        $this->assertResponseRedirects('http://localhost/login-user/login');
        $this->assertNull($this->findUser('speler')->getLastLogin());
    }

    public function testDisabledUserCannotLogin(): void
    {
        $this->login('uit', LoadUserData::PASSWORD);

        $this->assertResponseRedirects('http://localhost/login-user/login');
        $this->assertNull($this->findUser('uit')->getLastLogin());
    }

    public function testPasswordReset(): void
    {
        $this->client->request('GET', '/reset-password/request');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Reset wachtwoord', ['username' => 'Speler@Example.com']);

        $this->assertResponseRedirects('/reset-password/check-email');
        $this->assertEmailCount(1);
        $token = $this->findUser('speler')->getConfirmationToken();
        $this->assertNotNull($token);
        $this->assertEmailHtmlBodyContains($this->getMailerMessage(), '/reset-password/reset/' . $token);

        $this->client->request('GET', '/reset-password/reset/' . $token);
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Wijzig wachtwoord', [
            'reset_password[plainPassword][first]' => 'nieuw-wachtwoord',
            'reset_password[plainPassword][second]' => 'nieuw-wachtwoord',
        ]);
        $this->assertResponseRedirects('/');

        $user = $this->findUser('speler');
        $this->assertNull($user->getConfirmationToken());
        $this->assertNull($user->getPasswordRequestedAt());

        $this->client->request('GET', '/logout');
        $this->login('speler', 'nieuw-wachtwoord');
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testPasswordResetForUnknownUserSendsNothing(): void
    {
        $this->client->request('GET', '/reset-password/request');
        $this->client->submitForm('Reset wachtwoord', ['username' => 'onbekend@example.com']);

        $this->assertResponseRedirects('/reset-password/check-email');
        $this->assertEmailCount(0);
    }

    public function testResetWithUnknownTokenRedirectsToLogin(): void
    {
        $this->client->request('GET', '/reset-password/reset/onbekend');

        $this->assertResponseRedirects('/login-user/login');
    }

    public function testAdminCanCreateAndEditUser(): void
    {
        $this->login('beheer', LoadUserData::PASSWORD);

        $this->client->request('GET', '/admin/user/new-user');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'user[username]' => 'Nieuweling',
            'user[email]' => 'nieuw@example.com',
            'user[firstName]' => 'Nieuw',
        ]);
        $this->assertResponseRedirects();

        $user = $this->findUser('nieuweling');
        $this->assertTrue($user->isEnabled());
        $this->assertSame('nieuw@example.com', $user->getEmailCanonical());
        $this->assertFalse($user->hasRole('ROLE_ADMIN'));

        $this->client->request('GET', '/admin/user/' . $user->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Edit', [
            'user[enabled]' => false,
            'user[is_admin]' => true,
        ]);
        $this->assertResponseRedirects('/admin/user/' . $user->getId() . '/edit');

        $user = $this->findUser('nieuweling');
        $this->assertFalse($user->isEnabled());
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
    }

    public function testDuplicateUsernameIsRejected(): void
    {
        $this->login('beheer', LoadUserData::PASSWORD);

        $this->client->request('GET', '/admin/user/new-user');
        $this->client->submitForm('Create', [
            'user[username]' => 'SPELER',
            'user[email]' => 'nog-een@example.com',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#user_username_error1', 'Deze waarde wordt al gebruikt.');
    }

    public function testRegularUserCannotOpenAdmin(): void
    {
        $this->login('speler', LoadUserData::PASSWORD);
        $this->client->request('GET', '/admin/user/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOnlyTheOwnerIsGrantedOwnerOnPloeg(): void
    {
        $voter = self::getContainer()->get(PloegVoter::class);
        $ploegRepository = self::getContainer()->get('doctrine')->getRepository(Ploeg::class);
        $ownedPloeg = $ploegRepository->findOneBy(['afkorting' => 'pl1']);
        $unownedPloeg = $ploegRepository->findOneBy(['afkorting' => 'pl2']);
        $tokenFor = fn (string $username) => new UsernamePasswordToken($this->findUser($username), 'main', ['ROLE_USER']);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenFor('speler'), $ownedPloeg, [PloegVoter::OWNER]));
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($tokenFor('andere'), $ownedPloeg, [PloegVoter::OWNER]));
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($tokenFor('speler'), $unownedPloeg, [PloegVoter::OWNER]));
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($tokenFor('speler'), $ownedPloeg, ['EDIT']));
    }
}
