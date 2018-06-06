<?php

namespace AppBundle\Service;

use AppBundle\Entity\Reportable;
use AppBundle\Repository\TransactionRepository;

class ReportHelper
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getTransactionsInDateRange(Reportable $reportable, $start, $end)
    {
        $reportableProperty = $reportable->getPropertyName();

        return $this->transactionRepository->createQueryBuilder('transaction')
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

        return $this->transactionRepository->createQueryBuilder('transaction')
            ->andWhere('transaction.createdOn < :date')
            ->andWhere("transaction.$reportableProperty = :reportable")
            ->setParameter('date', $date)
            ->setParameter('reportable', $reportable)
            ->select('SUM(transaction.amount) as reportableBalance')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
