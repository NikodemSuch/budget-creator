<?php

namespace AppBundle\Factory;

use AppBundle\Report\Report;
use AppBundle\Report\ReportGenerator;
use AppBundle\Service\ReportHelper;

class ReportGeneratorFactory
{
    private $reportHelper;

    public function __construct(ReportHelper $reportHelper)
    {
        $this->reportHelper = $reportHelper;
    }

    public function createInstance(Report $report)
    {
        return new ReportGenerator($this->reportHelper, $report);
    }

}
