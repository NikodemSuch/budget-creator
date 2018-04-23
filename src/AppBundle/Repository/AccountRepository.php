<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function getCountByGroup($userGroup): int
    {
        return $this->createQueryBuilder('account')
            ->where('account.owner = :owner')
            ->setParameter('owner', $userGroup)
            ->select('count(account.owner) as accountsNumber')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
