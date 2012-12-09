<?php

namespace Cyclear\GameBundle\Form\EventListener;

use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

/**
 * @see http://marvelley.com/2012/11/10/symfony2-managing-a-user-entity-role-with-a-form-subscriber/
 */
class IsAdminFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::BIND => 'bind'
        );
    }

    /**
     * Called before form data is set
     *
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $checked = $data->hasRole('ROLE_ADMIN');

        $form->add($this->factory->createNamed('is_admin', 'checkbox', $checked, array(
                'property_path' => false,
                'required' => false
            )));
    }

    /**
     * Called when the form is bound to a request
     *
     * @param DataEvent $event
     */
    public function bind(DataEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $value = $form->get('is_admin')->getData();

        if ($value) {
            $data->addRole('ROLE_ADMIN');
        } else {
            $data->removeRole('ROLE_ADMIN');
        }
    }
}