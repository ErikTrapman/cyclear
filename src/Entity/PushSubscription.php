<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A browser that receives push notifications. The endpoint is the URL of the browser's push service, p256dh and auth
 * are the keys the payload is encrypted with.
 */
#[ORM\Entity(repositoryClass: \App\Repository\PushSubscriptionRepository::class)]
#[ORM\Table(name: 'push_subscription')]
class PushSubscription
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Column(length: 500, unique: true)]
        private string $endpoint,
        #[ORM\Column(length: 100)]
        private string $p256dh,
        #[ORM\Column(length: 50)]
        private string $auth,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getP256dh(): string
    {
        return $this->p256dh;
    }

    public function getAuth(): string
    {
        return $this->auth;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * A browser keeps its endpoint when another user logs in on it, and may renew its keys.
     */
    public function update(User $user, string $p256dh, string $auth): void
    {
        $this->user = $user;
        $this->p256dh = $p256dh;
        $this->auth = $auth;
    }
}
