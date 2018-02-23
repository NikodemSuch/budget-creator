<?php

namespace AppBundle\Security;

use AppBundle\Entity\Transaction;

class TransactionVoter extends AbstractOwnedVoter
{
    protected function checkSubjectType(Owned $subject): bool {

        return $subject instanceof Transaction;
    }
}
