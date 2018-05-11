<?php

namespace AppBundle\Enum;

use MyCLabs\Enum\Enum;

class ReportDetail extends Enum
{
    const YEAR          = 'Year';
    const MONTH         = 'Month';
    const DAY           = 'Day';
    const TRANSACTION   = 'All transactions';
}
