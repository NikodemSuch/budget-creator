<?php

namespace AppBundle\Report;

class Year extends AbstractInterval
{
    public function addInterval(Month $interval)
    {
        array_push($this->intervals, $interval);
    }
}
