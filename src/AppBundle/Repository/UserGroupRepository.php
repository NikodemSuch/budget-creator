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
