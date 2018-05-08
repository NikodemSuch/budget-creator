<?php

namespace AppBundle\Form;

use AppBundle\Entity\Budget;
use AppBundle\Form\EventListener\DisableOwnerFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->addEventSubscriber(new DisableOwnerFieldSubscriber())
        ;
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
