<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeizoenSelectorType extends \Symfony\Component\Form\AbstractType
{

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => Seizoen::class,
                'preferred_choices' => array($this->em->getRepository(Seizoen::class)->getCurrent()),
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s')->orderBy('s.id', 'DESC'); //->where('s.current = 1');
                }));
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getName()
    {
        return 'seizoen_selector';
    }
}