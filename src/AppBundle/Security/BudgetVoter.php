<?php

namespace AppBundle\Security;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Owned;

class BudgetVoter extends AbstractOwnedVoter
{
    protected function checkSubjectType(Owned $subject): bool {

        return $subject instanceof Budget;
    }
}
