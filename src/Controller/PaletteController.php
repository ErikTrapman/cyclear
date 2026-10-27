<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\User;
use App\Repository\RennerRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Data for the command palette (public/js/palette.js).
 */
class PaletteController extends AbstractController
{
    private const int MAX_RESULTS = 8;

    public function __construct(
        private readonly RennerRepository $rennerRepository,
    ) {
    }

    #[Route(path: '/{seizoen}/palette/renners', name: 'palette_riders', methods: ['GET'])]
    public function ridersAction(
        #[MapEntity(mapping: ['seizoen' => 'slug'])] Seizoen $seizoen,
        #[MapQueryParameter] string $q = '',
        #[MapQueryParameter] bool $available = false,
    ): JsonResponse {
        if (mb_strlen(trim($q)) < 2) {
            return $this->json(['riders' => []]);
        }
        $user = $this->getUser();
        $myPloeg = $user instanceof User ? $user->getPloegBySeizoen($seizoen) : null;

        $riders = [];
        foreach ($this->rennerRepository->searchWithPunten($seizoen, $q, $available, self::MAX_RESULTS) as $row) {
            $renner = $row[0];
            $ploeg = $available ? null : $this->rennerRepository->getPloeg($renner, $seizoen);
            $riders[] = [
                'name' => $renner->getNaam(),
                'slug' => $renner->getSlug(),
                'country' => $renner->getCountry() ? $renner->getCountryIso() : null,
                'points' => (int)$row['punten'],
                'url' => $this->generateUrl('renner_show', ['seizoen' => $seizoen->getSlug(), 'renner' => $renner->getSlug()]),
                'team' => null === $ploeg ? null : [
                    'name' => $ploeg->getNaam(),
                    'abbreviation' => $ploeg->getAfkorting(),
                    'url' => $this->generateUrl('ploeg_show', ['seizoen' => $seizoen->getSlug(), 'id' => $ploeg->getId()]),
                ],
                'mine' => null !== $ploeg && $ploeg === $myPloeg,
            ];
        }

        return $this->json(['riders' => $riders]);
    }
}
