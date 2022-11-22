<?php declare(strict_types=1);

namespace App\Form\Admin\Transfer;

use App\Entity\Transfer;
use App\Form\RennerSelectorType;
use App\Form\SeizoenSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];

        switch ($options['transfertype']) {
            case Transfer::DRAFTTRANSFER:
                $builder
                    ->add('renner', RennerSelectorType::class)
                    ->add('ploegNaar', null, ['label' => 'Ploeg naar', 'required' => true, 'query_builder' => function ($e) use ($seizoen) {
                        return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('p.afkorting');
                    }]);
                break;
            case Transfer::ADMINTRANSFER:
                $builder
                    ->add('renner', RennerSelectorType::class, ['mapped' => false])
                    ->add('renner2', RennerSelectorType::class, ['mapped' => false, 'label' => 'Renner']);
                break;
        }

        $builder
            ->add('datum', DateType::class, ['format' => 'dd-MM-y'])
            ->add('seizoen', SeizoenSelectorType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'admin' => true,
                'seizoen' => null,
                'transfertype' => Transfer::DRAFTTRANSFER, ]
        );
    }
}
