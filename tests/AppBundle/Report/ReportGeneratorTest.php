<?php

namespace tests\AppBundle\Services;

use AppBundle\Entity\Account;
use AppBundle\Entity\Transaction;
use AppBundle\Report\AbstractInterval;
use AppBundle\Report\Day;
use AppBundle\Report\Month;
use AppBundle\Report\Year;
use AppBundle\Report\Delta;
use AppBundle\Report\Report;
use AppBundle\Report\ReportGenerator;
use AppBundle\Service\ReportHelper;
use PHPUnit\Framework\TestCase;

class ReportGeneratorTest extends TestCase
{
    private $reportHelper;

    protected function setUp()
    {
        $mock = $this->createMock(ReportHelper::class);

        $this->reportHelper = $mock;

        parent::setUp();
    }

    /**
     * @dataProvider addDeltasToIntervalProvider
     */
    public function testAddDeltasToInterval(
        AbstractInterval $interval,
        \DateTimeImmutable $currentDate,
        string $mockMethodReturnData)
    {
        $this->reportHelper->expects($this->exactly(4))
            ->method('getBalanceOnInterval')
            ->willReturn($mockMethodReturnData);

        $reportGenerator = new ReportGenerator($this->reportHelper, $this->getReportData());

        $resultInterval = $reportGenerator->addDeltasToInterval($interval, $currentDate);
        $expectedInterval = $this->getExpectedInterval($interval, $currentDate, $mockMethodReturnData);

        $this->assertEquals($expectedInterval, $resultInterval);
    }

    public function addDeltasToIntervalProvider()
    {
        return [
            'add delta to day'  => [
                new Day("Day 1"),
                new \DateTimeImmutable('2010-06-04'),
                '521'
            ],
            'add delta to month'  => [
                new Month("Month 1"),
                new \DateTimeImmutable('2016-04-17'),
                '0'
            ],
            'add delta to year' => [
                new Year("Year 1"),
                new \DateTimeImmutable('2013-12-31'),
                '64181024'
            ]
        ];
    }

    /**
     * @dataProvider addDeltasToDayProvider
     */
    public function testaddDeltasToDay(
        string $intervalName,
        \DateTimeImmutable $currentDate,
        array $mockMethodReturnData)
    {
        $this->reportHelper->expects($this->exactly(2))
            ->method('getBalanceOnInterval')
            ->willReturn($mockMethodReturnData[0]);

        $this->reportHelper->expects($this->exactly(2))
            ->method('getTransactionsInDateRange')
            ->willReturn($mockMethodReturnData[1]);

        $reportGenerator = new ReportGenerator($this->reportHelper, $this->getReportData());

        $resultDay = $reportGenerator->addDeltasToDay(new Day($intervalName), $currentDate);
        $expectedDay = $this->getExpectedDay($intervalName, $currentDate, $mockMethodReturnData);

        $this->assertEquals($expectedDay, $resultDay);
    }

    public function addDeltasToDayProvider()
    {
        return [
            'add delta to month'  => [
                "Day 1",
                new \DateTimeImmutable('2010-06-03'),
                ['521', array()]
            ],
            'add delta to year' => [
                "Day 2",
                new \DateTimeImmutable('2018-02-08'),
                ['0', array(new Transaction(), new Transaction())]
            ]
        ];
    }

    public function getReportData()
    {
        $report = new Report();
        $report->setReportables([new Account(), new Account()]);
        $report->setStartDate(new \DateTime('2010-01-01'));
        $report->setEndDate(new \DateTime('2020-01-01'));

        return $report;
    }

    public function getExpectedInterval(
        AbstractInterval $interval,
        \DateTimeImmutable $currentDate,
        ?string $mockMethodReturnData)
    {
        foreach ($this->getReportData()->getReportables() as $reportable) {
            $delta = new Delta();
            $delta->setTitle("Balance for $reportable for " . $interval->getName());
            $delta->setInitialAmount($mockMethodReturnData);
            $delta->setFinalAmount($mockMethodReturnData);

            $interval->addDelta($delta);
        }

        return $interval;
    }

    public function getExpectedDay(
        string $intervalName,
        \DateTimeImmutable $currentDate,
        array $mockMethodReturnData)
    {
        $day = new Day($intervalName);

        foreach ($this->getReportData()->getReportables() as $reportable) {

            $deltas = [];

            $initialAmount = $mockMethodReturnData[0];
            $transactions = $mockMethodReturnData[1];

            foreach ($transactions as $transaction) {

                $finalAmount = $initialAmount + $transaction->getAmount();
                $delta = new Delta();
                $delta->setTitle("Balance for transaction " . $transaction->getTitle());
                $delta->setInitialAmount($initialAmount);
                $delta->setFinalAmount($finalAmount);
                $initialAmount = $finalAmount;

                array_push($deltas, $delta);
            }

            empty($deltas) ?: $day->addInterval($deltas, $reportable);
        }

        return $day;
    }
}
