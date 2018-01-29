<?php
namespace AppBundle\Type;

use MyCLabs\Enum\Enum;

class TransferType extends Enum
{
    const TRANSFER  = 'bank transfer';
    const PAYMENT   = 'payment';
    const PAYOFF    = 'withdrawal';
    const OTHER     = 'other';
}
