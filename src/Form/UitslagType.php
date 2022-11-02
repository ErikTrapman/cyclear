<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Ploeg;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UitslagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];
        // array( 'allow_add' => true, 'type' => $w)
        $builder
            ->add('positie', HiddenType::class)
            ->add('ploegPunten')
            ->add('positie')
            ->add('rennerPunten');
        if ($options['use_wedstrijd']) {
            $builder->add('wedstrijd', EntityType::class, ['class' => 'App\Entity\Wedstrijd', 'query_builder' => function (EntityRepository $e) use ($seizoen) {
                return $e->createQueryBuilder('w')->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('w.id', 'DESC');
            }]);
        }
        $builder
            ->add('ploeg', EntityType::class, ['required' => false,
                'class' => Ploeg::class,
                'query_builder' => function (EntityRepository $e) use ($seizoen) {
                    return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('p.afkorting');
                },])
            ->add('renner', RennerSelectorType::class);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uitslag',
            'registry' => null,
            'seizoen' => null,
            'use_wedstrijd' => true,
        ]);
    }
}
