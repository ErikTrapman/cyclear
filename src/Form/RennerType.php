<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RennerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('cqranking_id', null, ['required' => true, 'label' => 'CQ-id'])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('c')->orderBy('c.name');
                },])
            ->add('twitter', null, ['required' => false]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_rennertype';
    }
}
