<?php

namespace AppBundle\Repository;

use AppBundle\Entity\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function loadByName($name)
    {
        return $this->createQueryBuilder('userGroup')
            ->where('userGroup.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByName($userGroupName)
    {
        return $this->createQueryBuilder('userGroup')
            ->where('userGroup.name = :name')
            ->setParameter('name', $userGroupName)
            ->getQuery()
            ->getResult();
    }

    public function getCountByName($userGroupName): int
    {
        return $this->createQueryBuilder('userGroup')
            ->where('userGroup.name = :name')
            ->setParameter('name', $userGroupName)
            ->select('count(userGroup.name) as userGroupsNumber')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
