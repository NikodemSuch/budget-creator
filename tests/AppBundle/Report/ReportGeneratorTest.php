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
    private $reportGenerator;

    protected function setUp()
    {
        $reportHelper = $this->createMock(ReportHelper::class);

        $this->reportHelper = $reportHelper;

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

        $this->reportHelper->expects($this->exactly(2*$this->getNumOfCalls()))
            ->method('getBalanceOnInterval')
            ->willReturn($mockMethodReturnData);

        // See if tested method uses getBalanceOnInterval methods
        $this->reportHelper->expects($this->exactly($this->getNumOfCalls()))
            ->method('createDelta')
            ->with(
                $this->callback(function($subject) use ($mockMethodReturnData) {
                    return $subject['initialAmount'] == $mockMethodReturnData &&
                    $subject['finalAmount'] == $mockMethodReturnData;
                })
            )
            ->willReturn($this->getDeltaData());


        $reportGenerator = new ReportGenerator($this->reportHelper, $this->getReportData());

        $resultInterval = $reportGenerator->addDeltasToInterval($interval, $currentDate);
        $expectedInterval = $this->getExpectedInterval($interval);

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
        $this->reportHelper->expects($this->exactly($this->getNumOfCalls()))
            ->method('getBalanceOnInterval')
            ->willReturn($mockMethodReturnData[0]);

        $this->reportHelper->expects($this->exactly($this->getNumOfCalls()))
            ->method('getTransactionsInDateRange')
            ->willReturn($mockMethodReturnData[1]);

        // See if tested method uses getBalanceOnInterval methods
        $this->reportHelper->expects($this->exactly($this->getNumOfCalls()*count($mockMethodReturnData[1])))
            ->method('createDelta')
            ->with(
                $this->callback(function($subject) use ($mockMethodReturnData) {
                    return $subject['initialAmount'] == $mockMethodReturnData[0] &&
                    $subject['finalAmount'] == $mockMethodReturnData[0];
                })
            )
            ->willReturn($this->getDeltaData());

        $reportGenerator = new ReportGenerator($this->reportHelper, $this->getReportData());

        $resultDay = $reportGenerator->addDeltasToDay(new Day($intervalName), $currentDate);
        $expectedDay = $this->getExpectedDay($intervalName, $mockMethodReturnData[1]);

        $this->assertEquals($expectedDay, $resultDay);
    }

    public function addDeltasToDayProvider()
    {
        return [
            'add delta to month'  => [
                "Day 1",
                new \DateTimeImmutable('2010-06-03'),
                ['521', []]
            ],
            'add delta to year' => [
                "Day 2",
                new \DateTimeImmutable('2018-02-08'),
                ['0', [new Transaction(), new Transaction()]]
            ]
        ];
    }

    public function getNumOfCalls()
    {
        return count($this->getReportData()->getReportables());
    }

    public function getReportData()
    {
        $report = new Report();
        $report->setReportables([new Account(), new Account()]);
        $report->setStartDate(new \DateTime('2010-01-01'));
        $report->setEndDate(new \DateTime('2020-01-01'));

        return $report;
    }

    public function getDeltaData()
    {
        $delta = new Delta();
        $delta->setTitle("Balance for x for y");
        $delta->setInitialAmount("30");
        $delta->setFinalAmount("80");

        return $delta;
    }

    public function getExpectedInterval(AbstractInterval $interval)
    {
        for ($i = 0; $i < $this->getNumOfCalls(); $i++) {
            $interval->addDelta($this->getDeltaData());
        }

        return $interval;
    }

    public function getExpectedDay(string $intervalName, array $transactions)
    {
        $day = new Day($intervalName);

        for ($i = 0; $i < $this->getNumOfCalls(); $i++) {

            $deltas = [];

            foreach ($transactions as $transaction) {
                $delta = $this->getDeltaData();
                array_push($deltas, $delta);
            }

            if (!empty($deltas)) {
                $day->addInterval($deltas, new Account());
            }
        }

        return $day;
    }
}
