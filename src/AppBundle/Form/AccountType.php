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

        if (array_key_exists('has_transactions', $options)) {
            $hasTransactions = $options['has_transactions'];
            $owner = $options['owner'];
        }

        $disabled = (isset($hasTransactions) && $hasTransactions);
        $owner = isset($owner) ? $owner : null;

        $builder
            ->add('name', TextType::class)
            ->add('currency', TextType::class)
            ->add('owner', EntityType::class, [
                'class' => 'AppBundle:UserGroup',
                'choices' => $user->getUserGroups(),
                'choice_attr' => function($val, $key, $index) use ($disabled, $owner) {
                    return $disabled && $val != $owner ? ['disabled' => 'disabled'] : [];
                },
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setDefined('has_transactions');
        $resolver->setDefined('owner');
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_account';
    }
}
