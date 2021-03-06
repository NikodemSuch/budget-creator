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

    public function getByGroups($userGroups)
    {
        return $this->createQueryBuilder('transaction')
            ->innerJoin('transaction.account', 'account')
            ->innerJoin('transaction.budget', 'budget')
            ->where('account.owner IN (:userGroup)')
            ->andWhere('account.archived = :archived OR budget.archived = :archived')
            ->setParameter('archived', false)
            ->setParameter('userGroup', $userGroups)
            ->orderBy('transaction.createdOn', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getByGroupsQuery($userGroups)
    {
        return $this->createQueryBuilder('transaction')
            ->innerJoin('transaction.account', 'account')
            ->where('account.owner IN (:userGroup)')
            ->setParameter('userGroup', $userGroups)
            ->orderBy('transaction.createdOn', 'DESC')
            ->getQuery();
    }

    public function getByAccountQuery($account)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.account = :account')
            ->setParameter('account', $account)
            ->orderBy('transaction.createdOn', 'DESC')
            ->getQuery();
    }

    public function getByBudgetQuery($budget)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('transaction.createdOn', 'DESC')
            ->getQuery();
    }

    public function getCountByCategory($category): int
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.category = :category')
            ->setParameter('category', $category)
            ->select('count(transaction.category) as transactionsNumber')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAccountBalance($account)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.account = :account')
            ->setParameter('account', $account)
            ->select('SUM(transaction.amount) as accountBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getBudgetBalance($budget)
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.budget = :budget')
            ->setParameter('budget', $budget)
            ->select('SUM(transaction.amount) as budgetBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
