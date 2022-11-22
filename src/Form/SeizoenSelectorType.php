<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Seizoen;
use App\Repository\SeizoenRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeizoenSelectorType extends \Symfony\Component\Form\AbstractType
{
    public function __construct(private readonly SeizoenRepository $seizoenRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => Seizoen::class,
                'preferred_choices' => [$this->seizoenRepository->getCurrent()],
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s')->orderBy('s.id', 'DESC');
                },
            ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
