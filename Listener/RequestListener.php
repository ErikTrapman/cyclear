<?php

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
            // TODO vervang dit door 'seizoen' zodat seizoen in alle controllers voorhanden is
            $request->attributes->set('seizoen-object', $seizoen[0]);
            if (null !== $token = $this->security->getToken()) {
                $user = $token->getUser();
                if ($user instanceof \Cyclear\GameBundle\Entity\User) {
                    $request->attributes->set('seizoen-ploeg', $user->getPloegBySeizoen($seizoen[0]));
                }
            }
        }
    }
}
