<?php

namespace AppBundle\Report;

class Year
{
    private $title;
    private $deltas;
    private $months;

    public function __construct($title = null)
    {
        $this->title = $title;
        $this->deltas = [];
        $this->months = [];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getDeltas(): array
    {
        return $this->title;
    }

    public function setDeltas(array $title)
    {
        $this->title = $title;
    }

    public function addDelta(Delta $delta)
    {
        array_push($this->deltas, $delta);
    }

    public function getMonths(): array
    {
        return $this->months;
    }

    public function setMonths(array $months)
    {
        $this->months = $months;
    }

    public function addMonth(Month $month)
    {
        array_push($this->months, $month);
    }
}
