<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Entity\Reportable;
use AppBundle\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ReportHelper extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getTransactionsInDateRange(Reportable $reportable, $start,  $end)
    {
        $qb = $this->createQueryBuilder('transaction');
        $qb->andWhere('transaction.createdOn > :start')
           ->andWhere('transaction.createdOn < :end')
           ->setParameter('start', $start)
           ->setParameter('end', $end);

        if ($reportable instanceof Account) {

            $qb->andWhere('transaction.account = :account')
               ->setParameter('account', $reportable);

        } elseif ($reportable instanceof Budget) {

            $qb->andWhere('transaction.budget = :budget')
               ->setParameter('budget', $reportable);
        }

        return $qb->getQuery()->getResult();
    }

    public function getBalanceByReportableOnInterval(Reportable $reportable, $date)
    {
        $qb = $this->createQueryBuilder('transaction');

        $qb->andWhere('transaction.createdOn < :date')
           ->setParameter('date', $date);

        if ($reportable instanceof Account) {

            $qb->andWhere('transaction.account = :account')
               ->setParameter('account', $reportable)
               ->select('SUM(transaction.amount) as accountBalance');

        } elseif ($reportable instanceof Budget) {

            $qb->andWhere('transaction.budget = :budget')
               ->setParameter('budget', $reportable)
               ->select('SUM(transaction.amount) as budgetBalance');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
