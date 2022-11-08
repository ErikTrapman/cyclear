<?php declare(strict_types=1);

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
        return [
            new \Twig_SimpleFunction('getCurrentSeason', [$this, 'currentSeason']),
        ];
    }

    public function currentSeason(): Seizoen|null
    {
        return $this->em->getRepository(Seizoen::class)->findOneBy(['current' => true]);
    }
}
