<?php

namespace tests\AppBundle\Report;

use AppBundle\Report\Day;
use PHPUnit\Framework\TestCase;

class DayTest extends TestCase
{
    /**
     * @dataProvider getEndingDateProvider
     */
    public function testGetEndingDate($initialDate, $reportEndDate, $expectedDate)
    {
        $day = new Day();
        $resultDate = $day->getEndingDate($initialDate, $reportEndDate);

        $this->assertEquals($expectedDate, $resultDate);
    }

    public function getEndingDateProvider()
    {
        return [
            'return next day'  => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2010-01-02')
            ],
            'return next year'  => [
                new \DateTimeImmutable('2010-12-31'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2011-01-01')
            ],
            'return not report end date' => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2010-01-02')
            ]
        ];
    }
}
