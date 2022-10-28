<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SeizoenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier', TextType::class)
            ->add('current', null, ['required' => false])
            ->add('closed', null, ['required' => false])
            ->add('start')
            ->add('end')
            ->add('maxPointsPerRider');
    }

    public function getName()
    {
        return 'cyclear_gamebundle_seizoentype';
    }
}
