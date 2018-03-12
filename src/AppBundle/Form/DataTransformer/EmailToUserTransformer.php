<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailToUserTransformer implements DataTransformerInterface
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($users)
    {
        $emails = new ArrayCollection();

        foreach ($users as $user) {
            $email = $user->getEmail();
            $emails->add($email);
        }

        return $emails;
    }

    public function reverseTransform($emails)
    {
        $users = new ArrayCollection();

        foreach ($emails as $email) {
            $user = $this->userRepository->loadUserByUsername($email);

            if ($user) {
                $users->add($user);
            }

            else {
                throw new TransformationFailedException(
                    "User {$email} does not exist!"
                );
            }
        }

        return $users;
    }
}
