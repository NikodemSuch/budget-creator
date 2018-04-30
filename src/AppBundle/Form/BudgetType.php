<?php

namespace AppBundle\Form;

use AppBundle\Entity\Budget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $disabled = $options['has_transactions'] ?? false;
        $owner = $options['owner'] ?? null;

        $builder
            ->add('name', TextType::class)
            ->add('currency', TextType::class)
            ->add('owner', EntityType::class, [
                'class' => 'AppBundle:UserGroup',
                'choices' => $user->getUserGroups(),
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA,  function (FormEvent $event) {
                // Conditionally disable owner field - condition is located in form options, we get it this way:
                $attributes = array_values($event->getForm()->getConfig()->getAttributes())[0];
                $hasTransactions = $attributes['has_transactions'] ?? false;

                if ($hasTransactions) {
                    $this->disableField($event->getForm()->get('owner'));
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                // Because we conditionally disable editing owner field we don't get its value in $event->getData().
                $attributes = array_values($event->getForm()->getConfig()->getAttributes())[0];
                $hasTransactions = $attributes['has_transactions'] ?? false;

                if ($hasTransactions) {
                    $owner = $event->getForm()->getData()->getOwner();

                    $formData = $event->getData();
                    $formData['owner'] = $owner;

                    $event->setData($formData);
                }
           })
        ;
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setDefined('has_transactions');
        $resolver->setDefined('owner');
        $resolver->setDefaults([
            'data_class' => Budget::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_budget';
    }
}
