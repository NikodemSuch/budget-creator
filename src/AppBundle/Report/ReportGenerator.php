<?php

namespace AppBundle\Report;

use AppBundle\Enum\ReportDetail;
use AppBundle\Service\ReportHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportGenerator
{
    private $reportHelper;
    private $reportData;

    public function __construct(ReportHelper $reportHelper, Report $reportData)
    {
        $this->reportHelper = $reportHelper;
        $this->reportData = $reportData;
    }

    public function addDeltasToInterval(AbstractInterval $interval, \DateTimeImmutable $currentDateImmutable)
    {
        foreach ($this->reportData->getReportables() as $reportable) {

            // $delta = new TransactionDelta(
            //     $reportable,
            //     $interval,
            //     $initialAmount,
            //     $finalAmount,
            // );

            $deltaData['title'] = "Balance for $reportable for " . $interval->getName();
            $deltaData['currency'] = $reportable->getCurrency();
            $deltaData['initialAmount'] = $this->reportHelper->getBalanceOnInterval($reportable, $currentDateImmutable);
            $deltaData['finalAmount'] = $this->reportHelper->getBalanceOnInterval(
                $reportable,
                $interval->getEndingDate($currentDateImmutable, $this->reportData->getEndDate())
            );

            $delta = $this->reportHelper->createDelta($deltaData);

            $interval->addDelta($delta);
        }

        return $interval;
    }

    public function addDeltasToDay(Day $day, \DateTimeImmutable $currentDateImmutable)
    {
        foreach ($this->reportData->getReportables() as $reportable) {

            $deltas = [];

            $initialAmount = $this->reportHelper->getBalanceOnInterval($reportable, $currentDateImmutable);
            $transactions = $this->reportHelper->getTransactionsInDateRange(
                $reportable, $currentDateImmutable, $currentDateImmutable->modify('+1 day')
            );

            foreach ($transactions as $transaction) {

                $deltaData['title'] = "Balance for transaction " . $transaction->getTitle();
                $deltaData['currency'] = $reportable->getCurrency();
                $deltaData['initialAmount'] = $initialAmount;
                $deltaData['finalAmount'] = $initialAmount + $transaction->getAmount();

                $delta = $this->reportHelper->createDelta($deltaData);

                $initialAmount = $deltaData['finalAmount'];

                array_push($deltas, $delta);
            }

            if (!empty($deltas)) {
                $day->addInterval($deltas, $reportable);
            }
        }

        return $day;
    }

    public function createReport($locale)
    {
        $report = $this->reportData;
        $currentDate = new \DateTime();
        $currentDate->setTimestamp($this->reportData->getStartDate()->getTimestamp());
        $yearsUntilEnd = ($this->reportData->getStartDate()->diff($this->reportData->getEndDate())->y)+1;

        for ($y = 0; $y <= $yearsUntilEnd ; $y++) {

            if ($currentDate > $this->reportData->getEndDate()) {
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

                if ($currentDate > $this->reportData->getEndDate()) {
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

                    if ($currentDate > $this->reportData->getEndDate()) {
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
