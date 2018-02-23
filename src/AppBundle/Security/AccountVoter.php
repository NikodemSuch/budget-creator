<?php

namespace AppBundle\Security;

use AppBundle\Entity\Account;

class AccountVoter extends AbstractOwnedVoter
{
    protected function checkSubjectType(Owned $subject): bool {

        return $subject instanceof Account;
    }
}
