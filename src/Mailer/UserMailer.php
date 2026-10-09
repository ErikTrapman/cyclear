<?php declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class UserMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
    ) {
    }

    public function sendResettingEmailMessage(User $user): void
    {
        $url = $this->router->generate('app_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new TemplatedEmail())
            ->from(new Address('veggatron+cyclear@gmail.com', 'Cyclear'))
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
