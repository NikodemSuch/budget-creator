<?php

namespace AppBundle\Security;

use AppBundle\Entity\Budget;

class BudgetVoter extends AbstractOwnedVoter
{
    protected function checkSubjectType(Owned $subject): bool {

        return $subject instanceof Budget;
    }
}
