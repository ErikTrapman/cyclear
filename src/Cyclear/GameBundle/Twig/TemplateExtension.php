<?php
namespace Cyclear\GameBundle\Twig;


use Doctrine\ORM\EntityManager;

class TemplateExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getCurrentSeason', array($this, 'currentSeason'))
        );
    }

    public function currentSeason()
    {
        return $this->em->getRepository('CyclearGameBundle:Seizoen')->findOneBy(['current' => true]);
    }

}