<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateExtension extends AbstractExtension
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
            new TwigFunction('getCurrentSeason', [$this, 'currentSeason']),
        ];
    }

    public function currentSeason(): Seizoen|null
    {
        return $this->em->getRepository(Seizoen::class)->findOneBy(['current' => true]);
    }
}
