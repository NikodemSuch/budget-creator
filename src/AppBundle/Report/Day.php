<?php

namespace AppBundle\Report;

class Day extends AbstractInterval
{
    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function addInterval(array $deltas, $target)
    {
        $this->intervals[$target->getName()] = $deltas;
    }
}
