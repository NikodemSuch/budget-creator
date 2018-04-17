<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserGroup;
use AppBundle\Form\DataTransformer\EmailToUserTransformer;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserGroupType extends AbstractType
{
    private $transformer;

    public function __construct(EmailToUserTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('users', CollectionType::class, [
                'label' => 'Members:',
                'label_attr' => ['class' => 'form-control-label'],
                'data' => $builder->getData()->getUsers(),
                // Without next line, there's an internal error - not our fault.
                'data_class' => Collection::class,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_data' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['class' => 'userProperty'],
                ],
            ])
           ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
               $formData = $event->getData();
               // Key 'users' doesn't exist when we create group and we won't add any users to it or we're editing it and we delete all users.
               if (!array_key_exists('users', $formData)) {
                   $formData['users'] = array();
               }

               // Because we disable first field in edit template ($owner should be placed right there) we don't get its value in $event->getData().
               if ($event->getForm()->getData()->getOwner()) {
                   $ownerEmail = $event->getForm()->getData()->getOwner()->getEmail();

                   if (!in_array($ownerEmail, $formData['users'])) {
                       array_unshift($formData['users'], $ownerEmail);
                   }
               }

               // Remove duplicates, then fix array keys.
               $formData['users'] = array_unique($formData['users']);
               $formData['users'] = array_values($formData['users']);
               $event->setData($formData);
           })
        ;

        $builder->get('users')
            ->addViewTransformer($this->transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserGroup::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_userGroup';
    }
}
