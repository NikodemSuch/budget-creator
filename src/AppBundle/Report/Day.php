<?php

namespace AppBundle\Report;

class Day extends AbstractInterval
{
    public function addInterval(array $deltas, $target)
    {
        $this->intervals[$target->getName()] = $deltas;
    }
}
