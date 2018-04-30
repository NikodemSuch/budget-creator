<?php

namespace AppBundle\Report;

class Delta
{
    private $title;
    private $initialAmount;
    private $finalAmount;

    public function __construct($title = null, $initialAmount = null, $finalAmount = null)
    {
        $this->title = $title;
        $this->initialAmount = $initialAmount;
        $this->finalAmount = $finalAmount;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getInitialAmount(): string
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(string $initialAmount)
    {
        $this->initialAmount = $initialAmount;
    }

    public function getFinalAmount(): string
    {
        return $this->finalAmount;
    }

    public function setFinalAmount(string $finalAmount)
    {
        $this->finalAmount = $finalAmount;
    }
}
