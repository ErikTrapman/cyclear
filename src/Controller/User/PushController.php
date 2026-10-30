<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Dto\PushSubscriptionPayload;
use App\Entity\PushSubscription;
use App\Entity\User;
use App\Repository\PushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Turns push notifications on and off for a browser (public/js/push.js).
 */
#[IsGranted('ROLE_USER')]
class PushController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PushSubscriptionRepository $repository,
    ) {
    }

    #[Route(path: '/user/push/subscribe', name: 'user_push_subscribe', methods: ['POST'])]
    public function subscribeAction(Request $request, #[MapRequestPayload] PushSubscriptionPayload $payload, #[CurrentUser] User $user): Response
    {
        if (!$this->isCsrfTokenValid('push', (string)$request->headers->get('X-CSRF-Token'))) {
            return $this->json(['errors' => ['Je sessie is verlopen, herlaad de pagina.']], Response::HTTP_FORBIDDEN);
        }
        $subscription = $this->repository->findOneBy(['endpoint' => $payload->endpoint]);
        if (null === $subscription) {
            $this->em->persist(new PushSubscription($user, $payload->endpoint, $payload->p256dh, $payload->auth));
        } else {
            $subscription->update($user, $payload->p256dh, $payload->auth);
        }
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/user/push/unsubscribe', name: 'user_push_unsubscribe', methods: ['POST'])]
    public function unsubscribeAction(Request $request, #[MapRequestPayload] PushSubscriptionPayload $payload, #[CurrentUser] User $user): Response
    {
        if (!$this->isCsrfTokenValid('push', (string)$request->headers->get('X-CSRF-Token'))) {
            return $this->json(['errors' => ['Je sessie is verlopen, herlaad de pagina.']], Response::HTTP_FORBIDDEN);
        }
        $subscription = $this->repository->findOneBy(['endpoint' => $payload->endpoint, 'user' => $user]);
        if (null !== $subscription) {
            $this->em->remove($subscription);
            $this->em->flush();
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
