<?php

namespace AppBundle\Service;

use AppBundle\Enum\ReportDetail;
use AppBundle\Report\Report;
use AppBundle\Report\Year;
use AppBundle\Report\Month;
use AppBundle\Report\Day;
use AppBundle\Report\Delta;
use AppBundle\Repository\ReportHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportManager
{
    private $reportHelper;
    private $reportDetail;
    private $reportStartDate;
    private $reportEndDate;
    private $reportables;

    public function __construct(ReportHelper $reportHelper)
    {
        $this->reportHelper = $reportHelper;
    }

    public function addDeltasToInterval($interval, $currentDateImmutable)
    {
        foreach ($this->reportables as $reportable) {

            $delta = new Delta();
            $delta->setTitle("Balance for $reportable for " . $interval->getName());
            $delta->setCurrency($reportable->getCurrency());
            $delta->setInitialAmount($this->reportHelper->getBalanceOnInterval($reportable, $currentDateImmutable));

            if ($interval instanceof Year) {

                $delta->setFinalAmount($this->reportHelper->getBalanceOnInterval($reportable,
                    $currentDateImmutable->modify('1st January Next Year') < $this->reportEndDate ? $currentDateImmutable->modify('1st January Next Year') : $this->reportEndDate
                ));

            } elseif ($interval instanceof Month) {

                $delta->setFinalAmount($this->reportHelper->getBalanceOnInterval($reportable,
                    $currentDateImmutable->modify('first day of next month') < $this->reportEndDate ? $currentDateImmutable->modify('first day of next month') : $this->reportEndDate
                ));

            } elseif ($interval instanceof Day) {

                $delta->setFinalAmount($this->reportHelper->getBalanceOnInterval($reportable, $currentDateImmutable->modify('+1 day')));
            }

            $interval->addDelta($delta);
        }

        return $interval;
    }

    public function addDeltasToDay(Day $day, $currentDateImmutable)
    {
        foreach ($this->reportables as $reportable) {

            $deltas = [];

            $initialAmount = $this->reportHelper->getBalanceOnInterval($reportable, $currentDateImmutable);
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

            $day->addInterval($deltas, $reportable);
        }

        return $day;
    }

    public function createReport(Report $report, $locale)
    {
        $this->reportStartDate = $report->getStartDate();
        $this->reportEndDate = $report->getEndDate();
        $this->reportDetail = $report->getDetail();
        $this->reportables = $report->getReportables()->toArray();

        $currentDate = new \DateTime();
        $currentDate->setTimestamp($this->reportStartDate->getTimestamp());
        $yearsUntilEnd = ($this->reportStartDate->diff($this->reportEndDate)->y)+1;

        for ($y = 0; $y <= $yearsUntilEnd ; $y++) {

            if ($currentDate > $this->reportEndDate) {
                break;
            }

            $currentDateImmutable = \DateTimeImmutable::createFromMutable($currentDate);
            $year = new Year("Year " . $currentDate->format('Y'));
            $year = $this->addDeltasToInterval($year, $currentDateImmutable);

            if ($report->getDetail() == ReportDetail::YEAR()) {
                $report->addInterval($year);
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
                    $year->addInterval($month);
                    $currentDate->modify('first day of next month');

                    continue;
                }

                $daysUntilNextMonth = $currentDateImmutable->diff($currentDateImmutable->modify('last day of this month'))->days;

                for ($d = 0; $d <= $daysUntilNextMonth ; $d++) {

                    if ($currentDate > $this->reportEndDate) {
                        break;
                    }

                    $dateTimeFormatter = new \IntlDateFormatter($locale , \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
                    $currentDateImmutable = \DateTimeImmutable::createFromMutable($currentDate);
                    $day = new Day($dateTimeFormatter->format($currentDate));
                    $day = $this->addDeltasToInterval($day, $currentDateImmutable);

                    if ($report->getDetail() == ReportDetail::TRANSACTION()) {
                        $day = $this->addDeltasToDay($day, $currentDateImmutable);
                    }

                    $currentDate->modify('+1 day');
                    $month->addInterval($day);
                }

                $year->addInterval($month);
            }

            $report->addInterval($year);
        }

        return $report;
    }

}
