<?php

namespace AppBundle\Form;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Report\Report;
use AppBundle\Enum\ReportDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reportType = $options['report_type'];
        $accounts = $options['accounts'];
        $budgets = $options['budgets'];

        $now = new \DateTimeImmutable();

        $builder
            ->add('title', TextType::class)
            ->add('detail', ChoiceType::class, [
                'choices' => array_combine(ReportDetail::toArray(), ReportDetail::toArray()),
                'data' => ReportDetail::DAY(),
            ])
            ->add('startDate', DateType::Class, [
                // 'label' => 'Report start date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now->sub(new \DateInterval('P1M')),
            ])
            ->add('endDate', DateType::Class, [
                // 'label' => 'Report end date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now,
            ]);

        if ($reportType == 'accounts') {

            $builder
                ->add('reportables', EntityType::class, [
                    'label' => 'Include accounts:',
                    'class' => Account::class,
                    'choices'  => $accounts,
                    // 'choice_attr' => function($val, $key, $index) {
                    //     return ['class' => 'form-checkbox-label'];
                    // },
                    'expanded' => true,
                    'multiple' => true,
                ])
                ->getForm();
        }

        elseif ($reportType == 'budgets') {

            $builder
                ->add('reportables', EntityType::class, [
                    'label' => 'Include budgets:',
                    'class' => Budget::class,
                    'choices'  => $budgets,
                    // 'choice_attr' => function($val, $key, $index) {
                    //     return ['class' => 'form-checkbox-label'];
                    // },
                    'expanded' => true,
                    'multiple' => true,
                ])
                ->getForm();
        }

        // ->add('reportables', ChoiceType::class, [
        //     'choices' => [
        //         'accounts' => true,
        //         'budgets' => false,
        //     ],
        // ])
        // ->add('accounts', EntityType::class, [
        //     // 'label' => 'Include budgets:',
        //     'class' => Account::class,
        //     'choices'  => $accounts,
        //     // 'choice_attr' => function($val, $key, $index) {
        //     //     return ['class' => 'form-checkbox-label'];
        //     // },
        //     'expanded' => true,
        //     'multiple' => true,
        //     'mapped' => false,
        // ])
        // ->add('budgets', EntityType::class, [
        //     // 'label' => 'Include budgets:',
        //     'class' => Budget::class,
        //     'choices'  => $budgets,
        //     // 'choice_attr' => function($val, $key, $index) {
        //     //     return ['class' => 'form-checkbox-label'];
        //     // },
        //     'expanded' => true,
        //     'multiple' => true,
        //     'mapped' => false,
        // ])
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('accounts');
        $resolver->setDefined('budgets');
        $resolver->setRequired('report_type');
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
