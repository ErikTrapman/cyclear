<?php

namespace Cyclear\GameBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;

/**
 * Description of UserCreateListener
 *
 * @author Erik
 */
class UserCreateListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

    public function __construct(UserManagerInterface $userManager, MailerInterface $mailer, $em, $aclprovider)
    {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->aclprovider = $aclprovider;
        // $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($user, $request));
    }

    public static function getSubscribedEvents()
    {
        return array(
            \FOS\UserBundle\FOSUserEvents::REGISTRATION_INITIALIZE => array('onInit', -1),
            \FOS\UserBundle\FOSUserEvents::REGISTRATION_COMPLETED => 'onCreateSuccess',
        );
    }

    public function onInit(\FOS\UserBundle\Event\UserEvent $event)
    {
        $event->getUser()->setEnabled(false);
        echo '-a';
        die;
    }

    public function onCreateSuccess(FOS\UserBundle\Event\FilterUserResponseEvent $event)
    {
        var_dump($event);
        die;

        $form = $event->getForm();

        $ploeg = $form->get('ploeg')->getData();
        if ($ploeg !== null) {
            $ploeg->setUser($user);
        }
        $this->em->persist($ploeg);
        $this->em->flush();

        // creating the ACL
        $objectIdentity = \Symfony\Component\Security\Acl\Domain\ObjectIdentity::fromDomainObject($ploeg);
        $acl = $this->aclprovider->createAcl($objectIdentity);

        // retrieving the security identity of the currently logged-in user
        $securityIdentity = \Symfony\Component\Security\Acl\Domain\UserSecurityIdentity::fromAccount($user);

        // grant owner access
        $acl->insertObjectAce($securityIdentity, \Symfony\Component\Security\Acl\Permission\MaskBuilder::MASK_OWNER);
        $this->aclprovider->updateAcl($acl);


        //$event->setResponse(new RedirectResponse($url));
    }
}