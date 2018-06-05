<?php

namespace tests\AppBundle\Report;

use AppBundle\Report\Year;
use PHPUnit\Framework\TestCase;

class YearTest extends TestCase
{
    /**
     * @dataProvider getEndingDateProvider
     */
    public function testGetEndingDate($initialDate, $reportEndDate, $expectedDate)
    {
        $year = new Year();
        $resultDate = $year->getEndingDate($initialDate, $reportEndDate);

        $this->assertEquals($expectedDate, $resultDate);
    }

    public function getEndingDateProvider()
    {
        return [
            'return year later'  => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2011-01-01')
            ],
            'return next year'  => [
                new \DateTimeImmutable('2010-06-01'),
                new \DateTimeImmutable('2020-01-01'),
                new \DateTimeImmutable('2011-01-01')
            ],
            'return report end date' => [
                new \DateTimeImmutable('2010-01-01'),
                new \DateTimeImmutable('2010-06-01'),
                new \DateTimeImmutable('2010-06-01')
            ]
        ];
    }
}
