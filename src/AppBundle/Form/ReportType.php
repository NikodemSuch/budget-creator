<?php

namespace AppBundle\Form;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
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
        $budgets = $options['budgets'];
        $now = new \DateTimeImmutable();

        $builder
            ->add('title', TextType::class)
            ->add('details', ChoiceType::class, [
                'choices' => [
                    'Year' => ReportDetail::YEAR(),
                    'Month' => ReportDetail::MONTH(),
                    'Day' => ReportDetail::DAY(),
                    'Show all transactions' => ReportDetail::TRANSACTION(),
                ],
                'data' => ReportDetail::DAY(),

            ])
            ->add('start', DateType::Class, [
                'label' => 'Report start date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now->sub(new \DateInterval('P1M')),
            ])
            ->add('end', DateType::Class, [
                'label' => 'Report end date:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $now,
            ])
            ->add('budgets', EntityType::class, [
                'label' => 'Include budgets:',
                'label_attr' => ['class' => 'form-control-label'],
                'class' => Budget::class,
                'choices'  => $budgets,
                'expanded' => true,
                'multiple' => true,
            ])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('budgets');
    }
}
