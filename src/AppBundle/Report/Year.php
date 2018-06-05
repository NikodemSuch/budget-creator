<?php

namespace AppBundle\Report;

class Year extends AbstractInterval
{
    public function addInterval(Month $interval)
    {
        array_push($this->intervals, $interval);
    }

    public function getEndingDate(
        \DateTimeImmutable $currentDateImmutable,
        \DateTimeImmutable $reportEndDate): \DateTimeImmutable
    {
        return $currentDateImmutable->modify('1st January Next Year') < $reportEndDate ?
            $currentDateImmutable->modify('1st January Next Year') : $reportEndDate;
    }
}
