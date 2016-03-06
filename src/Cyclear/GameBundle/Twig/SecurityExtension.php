<?php
namespace Cyclear\GameBundle\Twig;

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Seizoen;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    public function __construct(EntityManager $em, AuthorizationCheckerInterface $authorizationChecker, RequestStack $requestStack)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isMyTeam', array($this, 'isMyTeam')),
            new \Twig_SimpleFunction('isMyRider', array($this, 'isMyRider'))
        );
    }

    /**
     * @param Ploeg $ploeg
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
     *
     * @param Renner $renner
     */
    public function isMyRider(Renner $renner)
    {
        $season = $this->getSeason();
        $requestTeam = $this->getLoggedInTeam();
        $riderTeam = $this->em->getRepository('CyclearGameBundle:Renner')->getPloeg($renner, $season);
        return $requestTeam === $riderTeam;
    }

    public function getName()
    {
        return 'cycleargame_security';
    }


}