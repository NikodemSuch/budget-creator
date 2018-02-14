<?php

namespace AppBundle\Form;

use AppBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $builder
            ->add('name', TextType::class)
            ->add('currency', TextType::class)
            ->add('owner', EntityType::class, [
                'class' => 'AppBundle:UserGroup',
                'choices' => $user->getUserGroups(),
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_account';
    }
}