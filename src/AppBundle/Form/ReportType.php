<?php

namespace AppBundle\Form;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Report\Report;
use AppBundle\Enum\ReportDetail;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'label' => 'Report start date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now->sub(new \DateInterval('P1M')),
            ])
            ->add('endDate', DateType::Class, [
                'label' => 'Report end date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Report type:',
                'choices' => [
                    'Choose Report Type' => 'choose',
                    'Accounts' => 'accounts',
                    'Budgets' => 'budgets',
                ],
                'constraints' => new Choice(['accounts', 'budgets']),
                'mapped' => false,
            ])
            ->add('accounts', EntityType::class, [
                'label' => false,
                'attr' => ['class' => 'reportables report-accounts-container'],
                'class' => Account::class,
                'choices'  => $accounts,
                'expanded' => true,
                'multiple' => true,
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('budgets', EntityType::class, [
                'label' => false,
                'attr' => ['class' => 'reportables report-budgets-container'],
                'class' => Budget::class,
                'choices'  => $budgets,
                'expanded' => true,
                'multiple' => true,
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create new Report',
                'attr' => ['class' => 'form-button btn btn-primary btn-lg btn-block'],
            ])
            ->add('generatePdf', SubmitType::class, [
                'label' => 'Generate Pdf File',
                'attr' => ['class' => 'form-button btn btn-primary btn-lg btn-block'],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                if (array_key_exists('accounts', $data)) {

                    $data['reportables'] = $data['accounts'];
                    $form->add('reportables', EntityType::class, [
                        'label' => false,
                        'class' => Account::class,
                        'multiple' => true,
                    ]);

                } elseif (array_key_exists('budgets', $data)) {

                    $data['reportables'] = $data['budgets'];
                    $form->add('reportables', EntityType::class, [
                        'label' => false,
                        'class' => Budget::class,
                        'multiple' => true,
                    ]);
                }

                $event->setData($data);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('accounts');
        $resolver->setDefined('budgets');
        $resolver->setDefaults([
            'data_class' => Report::class,
            'constraints' => [
                new Callback([
                    'callback' => [$this, 'checkEndDate'],
                ]),
            ],
        ]);
    }

    public function checkEndDate($data, ExecutionContextInterface $context)
    {
        if ($data->getStartDate() >= $data->getEndDate()) {
            $context->buildViolation("Start date must be earlier than the end date.")->addViolation();
        }
    }

}
