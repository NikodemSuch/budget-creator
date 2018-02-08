<?php

namespace AppBundle\Enum;

use MyCLabs\Enum\Enum;

class TransferType extends Enum
{
    const TRANSFER    = 'bank transfer';
    const DEPOSIT     = 'deposit';
    const WITHDRAWAL  = 'withdrawal';
    const OTHER       = 'other';
}
