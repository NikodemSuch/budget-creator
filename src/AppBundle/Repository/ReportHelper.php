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

    public function getTransactionsInDateRange(Reportable $reportable, $start, $end)
    {
        $reportableProperty = $reportable->getPropertyName();

        return $this->createQueryBuilder('transaction')
            ->andWhere('transaction.createdOn > :start')
            ->andWhere('transaction.createdOn < :end')
            ->andWhere("transaction.$reportableProperty = :reportable")
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('reportable', $reportable)
            ->getQuery()
            ->getResult();

    }

    public function getBalanceOnInterval(Reportable $reportable, $date)
    {
        $reportableProperty = $reportable->getPropertyName();

        return $this->createQueryBuilder('transaction')
            ->andWhere('transaction.createdOn < :date')
            ->andWhere("transaction.$reportableProperty = :reportable")
            ->setParameter('date', $date)
            ->setParameter('reportable', $reportable)
            ->select('SUM(transaction.amount) as reportableBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
