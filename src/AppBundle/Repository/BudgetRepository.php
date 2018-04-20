<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    public function getCountByGroup($userGroup): int
    {
        return $this->createQueryBuilder('budget')
            ->where('budget.owner = :owner')
            ->setParameter('owner', $userGroup)
            ->select('count(budget.owner) as budgetsNumber')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
