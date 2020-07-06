<?php
/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     *
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $security;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct($em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
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
                throw new NotFoundHttpException("Unknown season `" . $request->get('seizoen') . "`");
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
