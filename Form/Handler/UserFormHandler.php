<?php

namespace Cyclear\GameBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class UserFormHandler extends BaseHandler {

    private $em;
    
    
    private $aclprovider;

    public function __construct(Form $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, $em, $aclprovider) {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->aclprovider = $aclprovider;
    }

    protected function onSuccess(UserInterface $user, $confirmation) {
        parent::onSuccess($user, false);

        $ploeg = $this->form->get('ploeg')->getData();
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
    }

}