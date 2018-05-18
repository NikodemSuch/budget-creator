<?php

namespace AppBundle\Report;

class Year extends AbstractInterval
{
    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function addInterval(Month $interval)
    {
        array_push($this->intervals, $interval);
    }
}
