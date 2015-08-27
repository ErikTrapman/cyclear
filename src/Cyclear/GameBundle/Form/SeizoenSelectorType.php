<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Form;

class SeizoenSelectorType extends \Symfony\Component\Form\AbstractType
{
    
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    public function __construct($em)
    {
        $this->em = $em;
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'CyclearGameBundle:Seizoen',
                'preferred_choices' => array($this->em->getRepository("CyclearGameBundle:Seizoen")->getCurrent()),
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s')->orderBy('s.id', 'DESC'); //->where('s.current = 1');
                }));
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'seizoen_selector';
    }
}