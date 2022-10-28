<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityExtension extends \Twig_Extension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker, RequestStack $requestStack)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('isMyTeam', [$this, 'isMyTeam']),
            new \Twig_SimpleFunction('isMyRider', [$this, 'isMyRider']),
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
    public function isMyRider(Renner $renner)
    {
        $season = $this->getSeason();
        $requestTeam = $this->getLoggedInTeam();
        $riderTeam = $this->em->getRepository(Renner::class)->getPloeg($renner, $season);
        return $requestTeam === $riderTeam;
    }

    public function getName()
    {
        return 'cycleargame_security';
    }
}
