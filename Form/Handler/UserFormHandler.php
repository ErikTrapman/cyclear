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

    public function __construct(Form $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, $em) {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->em = $em;
    }

    protected function onSuccess(UserInterface $user, $confirmation) {
        parent::onSuccess($user, false);

        $ploeg = $this->form->get('ploeg')->getData();
        if ($ploeg !== null) {
            $ploeg->setUser($user);
        }
        $this->em->persist($ploeg);
        $this->em->flush();
    }

}