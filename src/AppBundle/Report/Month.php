<?php

namespace AppBundle\Report;

class Month extends AbstractInterval
{
    public function addInterval(Day $interval)
    {
        array_push($this->intervals, $interval);
    }
}
