<?php declare(strict_types=1);

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @see http://marvelley.com/2012/11/10/symfony2-managing-a-user-entity-role-with-a-form-subscriber/
 */
class IsAdminFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    /**
     * Called before form data is set
     *
     * @param \Symfony\Component\Form\FormEvent $event
     *
     * @return void
     */
    public function preSetData(\Symfony\Component\Form\FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $checked = $data->hasRole('ROLE_ADMIN');

        $form->add($this->factory->createNamed('is_admin', CheckboxType::class, $checked, [
            'mapped' => false,
            'required' => false,
            'auto_initialize' => false,
        ]));
    }

    /**
     * Called when the form is bound to a request
     *
     * @param \Symfony\Component\Form\FormEvent $event
     *
     * @return void
     */
    public function bind(\Symfony\Component\Form\FormEvent $event)
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
