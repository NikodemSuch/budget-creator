<?php

namespace AppBundle\Report;

class Month extends AbstractInterval
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function addInterval(Day $interval)
    {
        array_push($this->intervals, $interval);
    }
}
