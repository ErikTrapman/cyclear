<?php declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\RennerSelectorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends \Symfony\Component\Form\AbstractType
{
    /**
     * @return void
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];
        $timeoptions = ['widget' => 'single_text', 'format' => 'd-M-yyyy k:m'];
        $builder
            // ->add('ploeg')
            ->add('renner', RennerSelectorType::class)
            ->add('ploeg', null, ['label' => 'Ploeg naar', 'required' => true, 'query_builder' => function ($e) use ($seizoen) {
                return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
            }])
            ->add('start', null, $timeoptions)
            ->add('eind', null, $timeoptions)
            ->add('seizoen');
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'seizoen' => null,
            ]);
    }
}
