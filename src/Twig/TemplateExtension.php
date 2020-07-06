<?php
namespace App\Twig;


use App\Entity\Seizoen;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class TemplateExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
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
        return $this->em->getRepository(Seizoen::class)->findOneBy(['current' => true]);
    }

}