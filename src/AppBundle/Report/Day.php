<?php

namespace AppBundle\Report;

class Day
{
    private $title;
    private $deltas;
    private $transactions;

    public function __construct($title = null)
    {
        $this->title = $title;
        $this->deltas = [];
        $this->transactions = [];
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

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function setTransactions(array $transactions)
    {
        $this->transactions = $transactions;
    }

    public function addTransactions(array $deltas, $target)
    {
        $this->transactions[$target->getName()] = $deltas;
    }
}
