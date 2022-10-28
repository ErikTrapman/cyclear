<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Admin;

use App\Form\RennerSelectorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends \Symfony\Component\Form\AbstractType
{
    public function getName()
    {
        return 'admin_contract';
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];
        $timeoptions = ['widget' => 'single_text', 'format' => 'd-M-yyyy k:m'];
        $builder
            //->add('ploeg')
            ->add('renner', RennerSelectorType::class)
            ->add('ploeg', null, ['label' => 'Ploeg naar', 'required' => true, 'query_builder' => function ($e) use ($seizoen) {
                return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
            }])
            ->add('start', null, $timeoptions)
            ->add('eind', null, $timeoptions)
            ->add('seizoen');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'seizoen' => null,
            ]);
    }
}
