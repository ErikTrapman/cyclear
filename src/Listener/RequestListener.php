<?php declare(strict_types=1);

namespace App\Listener;

use App\Entity\User;
use App\Repository\SeizoenRepository;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SeizoenRepository $seizoenRepository,
    ) {
    }

    /**
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernel::MAIN_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();
        if ('POST' === $request->getMethod()) {
            return;
        }

        if (null !== $request->get('seizoen')) {
            if (!$seizoen = $this->seizoenRepository->findOneBy(['slug' => $request->get('seizoen')])) {
                throw new \UnexpectedValueException('Cannot locate Seizoen for url ' . $request->getUri());
            }
        } else {
            $seizoen = $this->seizoenRepository->getCurrent();
            if (null === $seizoen) {
                return;
            }
        }
        $request->attributes->set('seizoen', $seizoen);
        $request->attributes->set('current-seizoen', $this->seizoenRepository->getCurrent());
        if (null !== $token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();
            if ($user instanceof User) {
                $request->attributes->set('seizoen-ploeg', $user->getPloegBySeizoen($seizoen));
            }
        }
    }
}
