<?php

namespace AppBundle\Report;

class Delta
{
    private $title;
    private $initialAmount;
    private $finalAmount;
    private $currency;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getInitialAmount(): ?string
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(?string $initialAmount)
    {
        $this->initialAmount = $initialAmount;
    }

    public function getFinalAmount(): ?string
    {
        return $this->finalAmount;
    }

    public function setFinalAmount(?string $finalAmount)
    {
        $this->finalAmount = $finalAmount;
    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
