<?php

namespace AppBundle\Report;

class Day extends AbstractInterval
{
    public function addInterval(array $deltas, $target)
    {
        $this->intervals[$target->getName()] = $deltas;
    }

    public function getEndingDate(
        \DateTimeImmutable $currentDateImmutable,
        \DateTimeImmutable $reportEndDate): \DateTimeImmutable
    {
        return $currentDateImmutable->modify('+1 day');
    }
}
