<?php declare(strict_types=1);

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class PloegType extends AbstractType
{
    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('afkorting')
            ->add('seizoen', SeizoenSelectorType::class)
            ->add('user', null, [
                'required' => true, 'constraints' => [new NotNull()],
                'query_builder' => function (EntityRepository $e) {
                    return $e->createQueryBuilder('u')->orderBy('u.email', 'ASC');
                },
            ]);
    }

    public function getName(): string
    {
        return 'cyclear_gamebundle_ploegtype';
    }
}
