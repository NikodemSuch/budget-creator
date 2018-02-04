<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getAccountBalance($accountId)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.account = :account')
            ->setParameter('account', $accountId)
            ->select('SUM(transaction.amount) as accountBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getBudgetBalance($budgetId)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.budget = :budget')
            ->setParameter('budget', $budgetId)
            ->select('SUM(transaction.amount) as budgetBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
