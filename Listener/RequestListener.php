<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernel;

class RequestListener
{
    private $em;

    /**
     *
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $security;

    public function __construct($em, $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $request = $event->getRequest();
        if (null !== $request->get('seizoen')) {
            $seizoen = $this->em->getRepository("CyclearGameBundle:Seizoen")->findBySlug($request->get('seizoen'));
            $seizoen = $seizoen[0];
        } else {
            $seizoen = $this->em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $request->attributes->set('seizoen', $seizoen->getSlug());
        $request->attributes->set('seizoen-object', $seizoen);
        $request->attributes->set('current-seizoen', $this->em->getRepository("CyclearGameBundle:Seizoen")->getCurrent());
        if (null !== $token = $this->security->getToken()) {
            $user = $token->getUser();
            if ($user instanceof \Cyclear\GameBundle\Entity\User) {
                $request->attributes->set('seizoen-ploeg', $user->getPloegBySeizoen($seizoen));
            }
        }
    }
}
