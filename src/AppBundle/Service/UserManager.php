<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Enum\UserRole;
use AppBundle\Repository\UserRepository;
use AppBundle\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $em;
    private $passwordEncoder;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }

    public function createUser(string $username, string $email, string $plainPassword)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);
        $this->persistUserWithCredentials($user);
    }

    public function persistUserWithCredentials(User $user)
    {
        $userGroup = new UserGroup();
        $userGroup->setName($user->getUsername());
        $userGroup->setIsDefaultGroup(true);
        $userGroup->setOwner($user);
        $user->addUserGroup($userGroup);

        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function changePassword(string $username, string $newPlainPassword)
    {
        $user = $this->userRepository->loadUserByUsername($username);

        if (!$user) {
            throw new UserNotFoundException();
        }

        $newPassword = $this->passwordEncoder->encodePassword($user, $newPlainPassword);
        $user->setPassword($newPassword);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function changeRole(string $username, UserRole $newRole)
    {
        $user = $this->userRepository->loadUserByUsername($username);

        if (!$user) {
            throw new UserNotFoundException();
        }

        $user->setRole($newRole);

        $this->em->persist($user);
        $this->em->flush();
    }
}
