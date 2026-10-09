<?php declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Ploeg;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\MessageDigestPasswordHasher;

class LoadUserData extends Fixture implements DependentFixtureInterface
{
    public const PASSWORD = 'geheim123';

    public function load(ObjectManager $manager): void
    {
        // Passwords are hashed like FOSUserBundle did, to cover the migration to the 'auto' hasher.
        $legacyHasher = new MessageDigestPasswordHasher('sha512', true, 10);

        $owner = $this->createUser('speler', 'speler@example.com', true, 'saltsalt');
        $owner->setPassword($legacyHasher->hash(self::PASSWORD, 'saltsalt'));
        $manager->persist($owner);

        $other = $this->createUser('andere', 'andere@example.com', true, 'pepper');
        $other->setPassword($legacyHasher->hash(self::PASSWORD, 'pepper'));
        $manager->persist($other);

        $disabled = $this->createUser('uit', 'uit@example.com', false, 'salty');
        $disabled->setPassword($legacyHasher->hash(self::PASSWORD, 'salty'));
        $manager->persist($disabled);

        $admin = $this->createUser('beheer', 'beheer@example.com', true, 'nacl');
        $admin->setPassword($legacyHasher->hash(self::PASSWORD, 'nacl'));
        $admin->addRole('ROLE_ADMIN');
        $manager->persist($admin);

        $ploeg = $manager->getRepository(Ploeg::class)->findOneBy(['afkorting' => 'pl1']);
        $ploeg->setUser($owner);

        $manager->flush();
    }

    private function createUser(string $username, string $email, bool $enabled, string $salt): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEnabled($enabled);
        $user->setSalt($salt);
        return $user;
    }

    public function getDependencies(): array
    {
        return [LoadPloegData::class];
    }
}
