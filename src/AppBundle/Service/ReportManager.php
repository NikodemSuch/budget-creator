<?php

namespace AppBundle\Service;

use AppBundle\Enum\ReportDetail;
use AppBundle\Report\Report;
use AppBundle\Report\Year;
use AppBundle\Report\Month;
use AppBundle\Report\Day;
use AppBundle\Report\Delta;
use AppBundle\Repository\ReportHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ReportManager
{
    private $em;
    private $reportHelper;
    private $reportDetail;
    private $reportStartDate;
    private $reportEndDate;
    private $reportables;

    public function __construct(EntityManagerInterface $em, ReportHelper $reportHelper)
    {
        $this->em = $em;
        $this->reportHelper = $reportHelper;
    }

    public function addDeltasToInterval($interval, $currentDateImmutable)
    {
        foreach ($this->reportables as $reportable) {

            $delta = new Delta();
            $delta->setTitle("Balance for $reportable for " . $interval->getTitle());
            $delta->setCurrency($reportable->getCurrency());
            $delta->setInitialAmount($this->reportHelper->getBalanceByReportableOnInterval($reportable, $currentDateImmutable));

            if ($interval instanceof Year) {

                $delta->setFinalAmount($this->reportHelper->getBalanceByReportableOnInterval($reportable,
                    $currentDateImmutable->modify('1st January Next Year') > $this->reportEndDate ? $currentDateImmutable->modify('1st January Next Year') : $this->reportEndDate
                ));

            } elseif ($interval instanceof Month) {

                $delta->setFinalAmount($this->reportHelper->getBalanceByReportableOnInterval($reportable,
                    $currentDateImmutable->modify('first day of next month') > $this->reportEndDate ? $currentDateImmutable->modify('first day of next month') : $this->reportEndDate
                ));

            } elseif ($interval instanceof Day) {

                $delta->setFinalAmount($this->reportHelper->getBalanceByReportableOnInterval($reportable, $currentDateImmutable->modify('+1 day')));
            }

            $interval->addDelta($delta);
        }

        return $interval;
    }

    public function addDeltasToDay(Day $day, $currentDateImmutable)
    {
        foreach ($this->reportables as $reportable) {

            $deltas = [];

            $initialAmount = $this->reportHelper->getBalanceByReportableOnInterval($reportable, $currentDateImmutable);
            $transactions = $this->reportHelper->getTransactionsInDateRange(
                $reportable, $currentDateImmutable, $currentDateImmutable->modify('+1 day')
            );

            foreach ($transactions as $transaction) {

                $finalAmount = $initialAmount + $transaction->getAmount();
                $delta = new Delta();
                $delta->setTitle("Balance for transaction " . $transaction->getTitle());
                $delta->setCurrency($reportable->getCurrency());
                $delta->setInitialAmount($initialAmount);
                $delta->setFinalAmount($finalAmount);
                $initialAmount = $finalAmount;

                array_push($deltas, $delta);
            }

            $day->addTransactions($deltas, $reportable);
        }

        return $day;
    }

    public function createReport(Report $report)
    {
        $this->reportStartDate = $report->getStartDate();
        $this->reportEndDate = $report->getEndDate();
        $this->reportDetail = $report->getDetail();
        $this->reportables = $report->getReportables()->toArray();

        $currentDate = new \DateTime();
        $currentDate->setTimestamp($this->reportStartDate->getTimestamp());
        $yearsUntilEnd = $currentDate->diff($this->reportEndDate)->y;

        for ($y = 0; $y <= $yearsUntilEnd ; $y++) {

            if ($currentDate > $this->reportEndDate) {
                break;
            }

            $currentDateImmutable = \DateTimeImmutable::createFromMutable($currentDate);
            $year = new Year("Year " . $currentDate->format('Y'));
            $year = $this->addDeltasToInterval($year, $currentDateImmutable);

            if ($report->getDetail() == ReportDetail::YEAR()) {
                $report->addYear($year);
                $currentDate->modify('1st January Next Year');

                continue;
            }

            $monthsUntilNextYear = $currentDateImmutable->diff($currentDateImmutable->modify('last day of december this year'))->m;

            for ($m = 0; $m <= $monthsUntilNextYear ; $m++) {

                if ($currentDate > $this->reportEndDate) {
                    break;
                }

                $currentDateImmutable = \DateTimeImmutable::createFromMutable($currentDate);
                $month = new Month($currentDate->format('F Y'));
                $month = $this->addDeltasToInterval($month, $currentDateImmutable);

                if ($report->getDetail() == ReportDetail::MONTH()) {
                    $year->addMonth($month);
                    $currentDate->modify('first day of next month');

                    continue;
                }

                $daysUntilNextMonth = $currentDateImmutable->diff($currentDateImmutable->modify('last day of this month'))->days;

                for ($d = 0; $d <= $daysUntilNextMonth ; $d++) {

                    if ($currentDate > $this->reportEndDate) {
                        break;
                    }

                    $currentDateImmutable = \DateTimeImmutable::createFromMutable($currentDate);
                    $day = new Day($currentDate->format('Y-m-d'));
                    $day = $this->addDeltasToInterval($day, $currentDateImmutable);

                    if ($report->getDetail() == ReportDetail::TRANSACTION()) {
                        $day = $this->addDeltasToDay($day, $currentDateImmutable);
                    }

                    $currentDate->modify('+1 day');
                    $month->addDay($day);
                }

                $year->addMonth($month);
            }

            $report->addYear($year);
        }

        return $report;
    }

}
