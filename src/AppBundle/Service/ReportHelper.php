<?php

namespace AppBundle\Service;

use AppBundle\Entity\Reportable;
use AppBundle\Report\Delta;
use AppBundle\Repository\TransactionRepository;

class ReportHelper
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getTransactionsInDateRange(
        Reportable $reportable,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end)
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

    public function getBalanceOnInterval(Reportable $reportable, \DateTimeImmutable $date)
    {
        $reportableProperty = $reportable->getPropertyName();

        $balance = $this->transactionRepository->createQueryBuilder('transaction')
            ->andWhere('transaction.createdOn < :date')
            ->andWhere("transaction.$reportableProperty = :reportable")
            ->setParameter('date', $date)
            ->setParameter('reportable', $reportable)
            ->select('SUM(transaction.amount) as reportableBalance')
            ->getQuery()
            ->getSingleScalarResult();

        return $balance ?? 0;
    }

    public function createDelta(array $deltaData)
    {
        $delta = new Delta();
        $delta->setTitle($deltaData['title']);
        $delta->setCurrency($deltaData['currency']);
        $delta->setInitialAmount($deltaData['initialAmount']);
        $delta->setFinalAmount($deltaData['finalAmount']);

        return $delta;
    }
}
