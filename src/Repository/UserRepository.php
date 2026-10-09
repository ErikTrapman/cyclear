<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->findOneBy(['usernameCanonical' => User::canonicalize($identifier)]);
    }

    public function findOneByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findOneBy(['emailCanonical' => User::canonicalize($usernameOrEmail)]);
            if (null !== $user) {
                return $user;
            }
        }
        return $this->loadUserByIdentifier($usernameOrEmail);
    }

    /**
     * Called on login when the stored hash uses the legacy sha512 hasher (or outdated settings of the current one).
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            return;
        }
        $user->setPassword($newHashedPassword);
        $user->setSalt(null);
        $this->getEntityManager()->flush();
    }
}
