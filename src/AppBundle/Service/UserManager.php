<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $em;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
      $this->em = $em;
      $this->passwordEncoder = $passwordEncoder;
    }

    public function create(string $username, string $email, string $plainPassword)
    {
      $user = new User();
      $user->setUsername($username);
      $user->setEmail($email);
      $user->setPlainPassword($plainPassword);

      $userGroup = new UserGroup();
      $userGroup->setName($user->getUsername());
      $userGroup->setIsDefaultGroup(true);
      $userGroup->setUsers([$user]);

      $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
      $user->setPassword($password);
      $user->setUserGroups([$userGroup]);

      $this->em->persist($user);
      $this->em->persist($userGroup);
      $this->em->flush();
    }
}
