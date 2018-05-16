<?php

namespace AppBundle\Report;

abstract class AbstractInterval
{
    protected $name;
    protected $deltas;
    protected $intervals;

    public function __construct($name = null)
    {
        $this->name = $name;
        $this->deltas = [];
        $this->intervals = [];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return strtolower(str_replace([" ", "/", ".", ","], "-", $this->name));
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

    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function setIntervals(array $intervals)
    {
        $this->intervals = $intervals;
    }
}
