<?php

namespace AppBundle\Form;

use AppBundle\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $accounts = $options['accounts'];
        $budgets = $options['budgets'];
        $builder
            ->add('title', TextType::class)
            ->add('amount', MoneyType::class, array(
                'currency' => false,
                'divisor' => 100,
            ))
            ->add('createdOn', DateTimeType::class, [
                 'label' => 'Date and Time:',
                 'label_attr' => ['class' => 'form-control-label'],
             ])
            ->add('category', EntityType::class, [
                'class' => 'AppBundle:Category',
                'choice_label' => 'name',
            ])
            ->add('account', EntityType::class, [
                'class' => 'AppBundle:Account',
                'choices' => $accounts,
            ])
            ->add('budget', EntityType::class, [
                'class' => 'AppBundle:Budget',
                'choices' => $budgets,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setRequired('accounts');
        $resolver->setRequired('budgets');
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_transaction';
    }
}
