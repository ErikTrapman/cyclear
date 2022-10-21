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

use App\Entity\Wedstrijd;
use App\Form\Helper\PreBindValueTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UitslagCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wedstrijdManager = $options['wedstrijd_manager'];
        $uitslagManager = $options['uitslag_manager'];
        $crawlerManager = $options['crawler_manager'];
        $request = $options['request'];
        $seizoen = $options['seizoen'];
        $rennerManager = $options['renner_manager'];
        $builder
            ->add('url', MatchSelectorType::class, array('mapped' => false, 'required' => false, 'label' => 'CQ-wedstrijd'))
            ->add('url_manual', null, array('mapped' => false, 'required' => false, 'label' => 'URL', 'attr' => array('size' => 80)))
            ->add('referentiewedstrijd', EntityType::class, array('required' => false, 'mapped' => false, 'class' => Wedstrijd::class,
                'query_builder' => function (EntityRepository $r) {
                    return $r->createQueryBuilder('w')
                        ->where('w.generalClassification = 0')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(90);
                }))
            ->add('wedstrijd', WedstrijdType::class, array('default_date' => $options['default_date']));


        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use (
            $factory,
            $wedstrijdManager, $uitslagManager, $crawlerManager, $request, $seizoen
        ) {
            $form = $e->getForm();
            $data = $e->getData();
            if (null === $data) {
                return;
            }
            $helper = new PreBindValueTransformer();
            //var_dump( $clonedForm->get('wedstrijd'));die;
            //$clonedForm->get('wedstrijd')->getData()->getUitslagType();
            //$clonedForm->get('referentiewedstrijd')->getData();
            //$clonedForm->get('wedstrijd')->getData()->getDatum();
            $uitslagType = $helper->transformPostedValue($data['wedstrijd']['uitslagtype'], $form->get('wedstrijd')->get('uitslagtype'));
            $referentieWedstrijd = $helper->transformPostedValue($data['referentiewedstrijd'], $form->get('referentiewedstrijd'));
            $datum = $helper->transformPostedValue($data['wedstrijd']['datum'], $form->get('wedstrijd')->get('datum'));
            $form->add($factory->createNamed('uitslag', CollectionType::class, null, array('entry_type' => UitslagType::class,
                'allow_add' => true,
                'auto_initialize' => false,
                'by_reference' => false,
                'entry_options' => array(
                    'use_wedstrijd' => false,
                    'seizoen' => $seizoen))));
            if ($request->isXmlHttpRequest()) {
                $url = $data['url'] ? $data['url'] : $data['url_manual'];
                $crawler = $crawlerManager->getCrawler($url);
                $wedstrijd = $wedstrijdManager->createWedstrijdFromCrawler($crawler, $datum);
                $data['wedstrijd']['naam'] = $wedstrijd->getNaam();
                $data['wedstrijd']['uitslagtype'] = $uitslagType;
                $refDatum = (null !== $referentieWedstrijd) ? $referentieWedstrijd->getDatum() : null;
                $data['uitslag'] = $uitslagManager->prepareUitslagen($uitslagType, $crawler, $wedstrijd, $seizoen, $refDatum);
                $e->setData($data);
            }
        });


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $e) use ($request, $rennerManager) {
            $form = $e->getForm();
            $data = $e->getData();
            if (null === $data) {
                return;
            }
            $postData = $request->request->get($form->getName());
            if (array_key_exists('uitslag', $postData)) {
                $wedstrijd = $data['wedstrijd'];
                $uitslagen = $postData['uitslag'];
                foreach ($data['uitslag'] as $index => $uitslag) {
                    // we gebruiken het uitslagen-form hierboven zonder 'Wedstrijd'
                    $uitslag->setWedstrijd($wedstrijd);
                }
            }
        });
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagcreatetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'wedstrijd_manager' => null,
            'uitslag_manager' => null,
            'crawler_manager' => null,
            'renner_manager' => null,
            'request' => null,
            'seizoen' => null,
            'default_date' => null
        ));
    }
}
