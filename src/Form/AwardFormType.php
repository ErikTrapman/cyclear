<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Award;
use App\Entity\AwardType;
use App\Entity\Ploeg;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AwardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => AwardType::class,
                'choice_label' => static fn (AwardType $type): string => $type->getIcon() . ' ' . $type->getLabel(),
            ])
            ->add('ploeg', EntityType::class, [
                'class' => Ploeg::class,
                'required' => false,
                'placeholder' => '-- geen ploeg (seizoen niet in de database) --',
                'choice_label' => 'naam',
                'group_by' => static fn (Ploeg $ploeg): string => $ploeg->getSeizoen()->getIdentifier(),
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('p')
                    ->innerJoin('p.seizoen', 's')
                    ->orderBy('s.id', 'DESC')
                    ->addOrderBy('p.naam', 'ASC'),
            ])
            ->add('ownUser', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'label' => 'Speler (alleen zonder ploeg)',
                'choice_label' => static fn (User $user): string => $user->getFirstName() ?: (string) $user->getUsername(),
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('u')->orderBy('u.firstName', 'ASC'),
            ])
            ->add('ownSeason', TextType::class, [
                'required' => false,
                'label' => 'Seizoen (alleen zonder ploeg)',
                'attr' => ['placeholder' => 'Cyclear 2013'],
            ])
            ->add('ownTeam', TextType::class, [
                'required' => false,
                'label' => 'Ploegnaam (alleen zonder ploeg)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Award::class,
            'empty_data' => static fn (): Award => new Award(null, AwardType::SeasonWinner),
        ]);
    }
}
