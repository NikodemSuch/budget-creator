<?php

namespace AppBundle\Report;

class Month extends AbstractInterval
{
    public function addInterval(Day $interval)
    {
        array_push($this->intervals, $interval);
    }

    public function getEndingDate(
        \DateTimeImmutable $currentDateImmutable,
        \DateTimeImmutable $reportEndDate): \DateTimeImmutable
    {
        return $currentDateImmutable->modify('first day of next month') < $reportEndDate ?
            $currentDateImmutable->modify('first day of next month') : $reportEndDate;
    }
}
