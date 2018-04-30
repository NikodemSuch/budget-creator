<?php

namespace AppBundle\Report;

use AppBundle\Enum\ReportDetail;

class Report
{
    private $title;
    private $createdOn;
    private $startDate;
    private $endDate;
    private $detail;
    private $budgets;
    private $years;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
        $this->years = [];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = EntityHelper::SetCreatedOn($createdOn);
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

     public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    public function getDetail(): ReportDetail
    {
        return $this->detail;
    }

    public function setDetail(ReportDetail $detail)
    {
        $this->detail = $detail;
    }

    public function getBudgets(): array
    {
        return $this->budgets;
    }

    public function setBudgets(array $budgets)
    {
        $this->budgets = $budgets;
    }

    public function getYears(): array
    {
        return $this->years;
    }

    public function setYears(array $years)
    {
        $this->years = $years;
    }

    public function addYear(Year $year)
    {
        array_push($this->years, $year);
    }
}
