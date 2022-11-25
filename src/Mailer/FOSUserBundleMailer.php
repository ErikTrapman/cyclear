<?php declare(strict_types=1);

namespace App\Mailer;

use FOS\UserBundle\Mailer\MailerInterface as FOSMailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FOSUserBundleMailer implements FOSMailerInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
    ) {
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        throw new \LogicException('Not implemented');
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@cyclear.nl', 'Cyclear'))
            ->to(new Address($user->getEmail()))
            ->subject('Wachtwoord vergeten')
            ->htmlTemplate('mail/reset.html.twig')
            ->context([
                'user' => $user,
                'url' => $url,
            ]);

        $this->mailer->send($email);
    }
}
