<?php

namespace AppBundle\Report;

class Month
{
    private $title;
    private $deltas;
    private $days;

    public function __construct($title = null)
    {
        $this->title = $title;
        $this->deltas = [];
        $this->days = [];
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
        return $this->deltas;
    }

    public function setDeltas(array $deltas)
    {
        $this->deltas = $deltas;
    }

    public function addDelta(Delta $delta)
    {
        array_push($this->deltas, $delta);
    }

    public function getDays(): array
    {
        return $this->days;
    }

    public function setDays(array $days)
    {
        $this->days = $days;
    }

    public function addDay(Day $day)
    {
        array_push($this->days, $day);
    }
}
