<?php declare(strict_types=1);

namespace App\Listener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener]
class LastLoginListener
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }
        $user->setLastLogin(new \DateTime());
        $this->em->flush();
    }
}
