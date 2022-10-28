<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Admin\Transfer;

use App\Entity\Transfer;
use App\Form\RennerSelectorType;
use App\Form\SeizoenSelectorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TransferEditType extends \Symfony\Component\Form\AbstractType
{
    public function getName()
    {
        return 'cyclear_gamebundle_transferedittype';
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $choices = [
            Transfer::DRAFTTRANSFER => 'draft transfer',
            Transfer::ADMINTRANSFER => 'admin transfer',
            Transfer::USERTRANSFER => 'user transfer', ];
        $builder
            ->add('renner', RennerSelectorType::class, ['attr' => ['read_only' => true]])
            ->add('transferType', ChoiceType::class, ['choices' => array_flip($choices)])
            ->add('datum', DateTimeType::class)
            ->add('seizoen', SeizoenSelectorType::class);
    }
}
