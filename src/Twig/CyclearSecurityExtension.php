<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CyclearSecurityExtension extends AbstractExtension
{
    public function __construct(private EntityManagerInterface $em, private RequestStack $requestStack)
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('isMyTeam', [$this, 'isMyTeam']),
            new TwigFunction('isMyRider', [$this, 'isMyRider']),
        ];
    }

    /**
     * @return bool
     */
    public function isMyTeam(Ploeg $ploeg)
    {
        // no team in request?
        if (null === $requestTeam = $this->getLoggedInTeam()) {
            return false;
        }
        return $requestTeam === $ploeg;
    }

    /**
     * @return Seizoen|null
     */
    private function getSeason()
    {
        return $this->requestStack->getMasterRequest()->attributes->get('seizoen');
    }

    /**
     * @return Ploeg|null
     */
    private function getLoggedInTeam()
    {
        return $this->requestStack->getMasterRequest()->attributes->get('seizoen-ploeg');
    }

    /**
     * {% set ingelogdPloeg = app.request.attributes.get('seizoen-ploeg') %}
     *
     * {% set rennerPloeg = ( rennerPloeg is defined ) ? rennerPloeg : null %}
     *
     * {% if rennerPloeg and is_granted("ROLE_USER") and is_granted('OWNER', ploeg) and rennerPloeg == ingelogdPloeg %}
     */
    public function isMyRider(Renner $renner): bool
    {
        $season = $this->getSeason();
        $requestTeam = $this->getLoggedInTeam();
        $riderTeam = $this->em->getRepository(Renner::class)->getPloeg($renner, $season);
        return $requestTeam === $riderTeam;
    }

    public function getName(): string
    {
        return 'cycleargame_security';
    }
}
