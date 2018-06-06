<?php

namespace AppBundle\Report;

use AppBundle\Enum\ReportDetail;
use AppBundle\Utils\EntityHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Report
{
    private $title;
    private $createdOn;
    private $startDate;
    private $endDate;
    private $detail;
    private $reportables;
    private $intervals;

    public function __construct()
    {
        $this->intervals = [];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = EntityHelper::SetCreatedOn($createdOn);
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = EntityHelper::SetCreatedOn($startDate);
    }

     public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = EntityHelper::SetCreatedOn($endDate);
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(String $detail)
    {
        $this->detail = $detail;
    }

    public function getReportables()
    {
        return $this->reportables;
    }

    public function setReportables($reportables)
    {
        $this->reportables = $reportables;
    }

    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function setIntervals(array $intervals)
    {
        $this->intervals = $intervals;
    }

    public function addInterval(Year $interval)
    {
        array_push($this->intervals, $interval);
    }
}
