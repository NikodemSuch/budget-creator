<?php

namespace tests\AppBundle\Services;

use AppBundle\Entity\Account;
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
        $this->reportHelper = $this->createMock(ReportHelper::class);

        parent::setUp();
    }

    /**
     * @dataProvider addDeltasToIntervalProvider
     */
    public function testAddDeltasToInterval(Report $reportData, AbstractInterval $interval)
    {
        $currentDate = new \DateTimeImmutable('2010-06-03');
        $reportGenerator = new ReportGenerator($this->reportHelper, $reportData);

        $resultInterval = $reportGenerator->addDeltasToInterval($interval, $currentDate);
        $expectedInterval = $this->getExpectedInterval($interval, $reportData, $currentDate);

        $this->assertEquals($expectedInterval, $resultInterval);
    }

    public function addDeltasToIntervalProvider()
    {
        return [
            'add delta to day'  => [
                $this->getReportData(),
                new Day()
            ],
            'add delta to month'  => [
                $this->getReportData(),
                new Month()
            ],
            'add delta to year' => [
                $this->getReportData(),
                new Year()
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
        Report $reportData,
        \DateTimeImmutable $currentDate)
    {
        foreach ($reportData->getReportables() as $reportable) {
            $delta = new Delta();
            $delta->setTitle("Balance for $reportable for " . $interval->getName());
            $delta->setCurrency($reportable->getCurrency());
            $delta->setInitialAmount($this->reportHelper->getBalanceOnInterval($reportable, $currentDate));
            $delta->setFinalAmount(
                $this->reportHelper->getBalanceOnInterval(
                    $reportable,
                    $interval->getEndingDate($currentDate, $reportData->getEndDate())
                )
            );

            $interval->addDelta($delta);
        }

        return $interval;
    }
}
