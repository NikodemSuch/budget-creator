<?php

namespace AppBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DisableOwnerFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        // Conditionally disable owner field - condition is located in form options, we get it this way:
        $attributes = array_values($event->getForm()->getConfig()->getAttributes())[0];
        $hasTransactions = $attributes['has_transactions'] ?? false;

        if ($hasTransactions) {
            $this->disableField($event->getForm()->get('owner'));
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        // Because we conditionally disable editing owner field we don't get its value in $event->getData().
        $attributes = array_values($event->getForm()->getConfig()->getAttributes())[0];
        $hasTransactions = $attributes['has_transactions'] ?? false;

        if ($hasTransactions) {
            $owner = $event->getForm()->getData()->getOwner();

            $formData = $event->getData();
            $formData['owner'] = $owner;

            $event->setData($formData);
        }
    }

    private function disableField(FormInterface $field)
    {
        $parent = $field->getParent();
        $options = $field->getConfig()->getOptions();
        $name = $field->getName();
        $type = get_class($field->getConfig()->getType()->getInnerType());
        $parent->remove($name);
        $parent->add($name, $type, array_merge($options, ['disabled' => true]));
    }
}
