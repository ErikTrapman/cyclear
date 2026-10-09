<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Renner;
use App\Validator\Constraints\UserTransfer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null !== $options['renner_uit']) {
            $builder
                ->add('renner_in', RennerSelectorType::class, ['label' => 'Renner in', 'mapped' => 'rennerIn']);
        }
        if (null !== $options['renner_in']) {
            $ploegRenners = array_merge([0], $options['ploegRenners']);
            $builder
                ->add('renner_uit', EntityType::class, [
                    'mapped' => 'rennerUit',
                    'label' => 'Renner uit',
                    'class' => Renner::class,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $e) use ($ploegRenners) {
                        return $e->createQueryBuilder('r')
                            ->where('r IN ( :renners )')
                            ->setParameter(':renners', $ploegRenners)
                            ->orderBy('r.naam');
                    }, ]);
        }
        $builder->add('userComment', null, ['label' => 'Commentaar', 'attr' => ['placeholder' => 'Commentaar...']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'constraints' => [new UserTransfer()],
                'renner_in' => null,
                'renner_uit' => null,
                'ploeg' => null,
                'ploegRenners' => [],
            ]
        );
    }
}
