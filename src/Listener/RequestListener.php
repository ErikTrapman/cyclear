<?php declare(strict_types=1);

namespace App\Listener;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    public function __construct(private EntityManagerInterface $em, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();
        if ('POST' === $request->getMethod()) {
            return;
        }

        if (null !== $request->get('seizoen')) {
            $seizoen = $this->em->getRepository(Seizoen::class)->findBySlug($request->get('seizoen'));
            if (empty($seizoen)) {
                throw new NotFoundHttpException('Unknown season `' . $request->get('seizoen') . '`');
            }
            $seizoen = $seizoen[0];
        } else {
            $seizoen = $this->em->getRepository(Seizoen::class)->getCurrent();
            if (null === $seizoen) {
                return;
            }
        }
        $request->attributes->set('seizoen', $seizoen);
        $request->attributes->set('current-seizoen', $this->em->getRepository(Seizoen::class)->getCurrent());
        if (null !== $token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();
            if ($user instanceof \App\Entity\User) {
                $request->attributes->set('seizoen-ploeg', $user->getPloegBySeizoen($seizoen));
            }
        }
    }
}
