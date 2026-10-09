<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Column mapping is identical to the former FOSUserBundle mapped superclass, so no schema change is needed.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['usernameCanonical'], errorPath: 'username')]
#[UniqueEntity(fields: ['emailCanonical'], errorPath: 'email')]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface, EquatableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    private ?string $username = null;

    #[ORM\Column(name: 'username_canonical', length: 180, unique: true)]
    private ?string $usernameCanonical = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(name: 'email_canonical', length: 180, unique: true)]
    private ?string $emailCanonical = null;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = false;

    #[ORM\Column(nullable: true)]
    private ?string $salt = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    private ?\DateTime $lastLogin = null;

    #[ORM\Column(name: 'confirmation_token', length: 180, unique: true, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    private ?\DateTime $passwordRequestedAt = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: Ploeg::class, mappedBy: 'user')]
    private $ploeg;

    #[ORM\Column(nullable: true)]
    private ?string $firstName = null;

    public function __construct()
    {
        $this->ploeg = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->username;
    }

    /**
     * Same layout as the former FOSUserBundle model, so sessions serialized before the migration keep working.
     */
    public function __serialize(): array
    {
        return [
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical
        ] = $data;
    }

    public static function canonicalize(string $value): string
    {
        return mb_strtolower($value);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
        $this->usernameCanonical = null !== $username ? self::canonicalize($username) : null;
    }

    public function getUsernameCanonical(): ?string
    {
        return $this->usernameCanonical;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
        $this->emailCanonical = null !== $email ? self::canonicalize($email) : null;
    }

    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): void
    {
        $this->salt = $salt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return null !== $this->passwordRequestedAt && $this->passwordRequestedAt->getTimestamp() + $ttl > time();
    }

    public function getRoles(): array
    {
        return array_values(array_unique([...$this->roles, self::ROLE_DEFAULT]));
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if (self::ROLE_DEFAULT !== $role && !in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        $this->roles = array_values(array_diff($this->roles, [strtoupper($role)]));
    }

    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self
            && $this->password === $user->getPassword()
            && $this->salt === $user->getSalt()
            && $this->username === $user->getUsername();
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    public function setPloeg($ploeg): void
    {
        $this->ploeg = $ploeg;
    }

    public function getPloegBySeizoen($seizoen)
    {
        foreach ($this->getPloeg() as $ploeg) {
            if ($ploeg->getSeizoen() === $seizoen) {
                return $ploeg;
            }
        }
        return null;
    }

    public function setFirstName(?string $value = null): void
    {
        $this->firstName = $value;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
}
