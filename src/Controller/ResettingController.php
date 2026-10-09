<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\ResetPasswordType;
use App\Mailer\UserMailer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Password reset flow, taking over the URLs and behaviour of the former FOSUserBundle resetting controller.
 */
#[Route(path: '/reset-password')]
class ResettingController extends AbstractController
{
    /** A new reset mail is only sent if the previous request is older than this. */
    private const RETRY_TTL = 7200;
    /** A reset link stays valid for this long. */
    private const TOKEN_TTL = 86400;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/request', name: 'app_resetting_request', methods: ['GET'])]
    public function requestAction(): Response
    {
        return $this->render('resetting/request.html.twig');
    }

    #[Route(path: '/send-email', name: 'app_resetting_send_email', methods: ['POST'])]
    public function sendEmailAction(Request $request, UserMailer $mailer): Response
    {
        $username = (string)$request->request->get('username');
        $user = '' !== $username ? $this->userRepository->findOneByUsernameOrEmail($username) : null;

        if (null !== $user && !$user->isPasswordRequestNonExpired(self::RETRY_TTL)) {
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
            }
            $mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->em->flush();
        }

        // Always the same response, so this can't be used to find out which accounts exist.
        return $this->redirectToRoute('app_resetting_check_email');
    }

    #[Route(path: '/check-email', name: 'app_resetting_check_email', methods: ['GET'])]
    public function checkEmailAction(): Response
    {
        return $this->render('resetting/check_email.html.twig');
    }

    #[Route(path: '/reset/{token}', name: 'app_resetting_reset', methods: ['GET', 'POST'])]
    public function resetAction(
        Request $request,
        string $token,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
    ): Response {
        $user = $this->userRepository->findOneBy(['confirmationToken' => $token]);
        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }
        if (!$user->isPasswordRequestNonExpired(self::TOKEN_TTL)) {
            return $this->redirectToRoute('app_resetting_request');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSalt(null);
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setEnabled(true);
            $this->em->flush();

            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('_welcome');
        }

        return $this->render('resetting/reset.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }
}
