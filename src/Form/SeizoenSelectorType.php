<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeizoenSelectorType extends \Symfony\Component\Form\AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => Seizoen::class,
                'preferred_choices' => [$this->em->getRepository(Seizoen::class)->getCurrent()],
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s')->orderBy('s.id', 'DESC'); // ->where('s.current = 1');
                }, ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getName(): string
    {
        return 'seizoen_selector';
    }
}
