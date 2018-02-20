<?php

namespace AppBundle\Security;

use AppBundle\Entity\Budget;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BudgetVoter extends Voter
{
    const VIEW    = 'view';
    const EDIT    = 'edit';
    const DELETE  = 'delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Budget) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Budget $budget */
        $budget = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($budget, $user);
            case self::EDIT:
                return $this->canEdit($budget, $user);
            case self::DELETE:
                return $this->canDelete($budget, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Budget $budget, User $user)
    {
        if ($this->canDelete($budget, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Budget $budget, User $user)
    {
        if ($this->canDelete($budget, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Budget $budget, User $user)
    {
        return $user->getUserGroups()->contains($budget->getOwner());
    }
}
