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
    private $years;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->getStartDate() >= $this->getEndDate()) {
            $context->buildViolation("This value is not valid.")->addViolation();
        }
    }

    public function __construct()
    {
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

    public function getReportables(): ?ArrayCollection
    {
        return $this->reportables;
    }

    public function setReportables(ArrayCollection $reportables)
    {
        $this->reportables = $reportables;
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
