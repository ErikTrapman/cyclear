<?php declare(strict_types=1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PloegFilterType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam', TextType::class, ['required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'ploeg_filter';
    }

    public function getName()
    {
        return 'ploeg_filter';
    }
}
