<?php

namespace tests\AppBundle\Report;

use AppBundle\Report\Month;
use PHPUnit\Framework\TestCase;

class MonthTest extends TestCase
{
    /**
     * @dataProvider endingDateProvider
     */
    public function testGetEndingDate($initialDate, $reportEndDate, $expectedDate)
    {
        $month = new Month();
        $resultDate = $month->getEndingDate($initialDate, $reportEndDate);

        $this->assertEquals($expectedDate, $resultDate);
    }

    public function endingDateProvider()
    {
        return [
            'return month later'  => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2010-02-01')
            ],
            'return next month'  => [
                new \DateTimeImmutable('2010-01-15'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2010-02-01')
            ],
            'return report end date' => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2010-01-31'),
                new \DateTimeImmutable('2010-01-31')
            ]
        ];
    }
}
