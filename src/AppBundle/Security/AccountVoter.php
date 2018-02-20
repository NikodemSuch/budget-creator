<?php

namespace AppBundle\Security;

use AppBundle\Entity\Account;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountVoter extends Voter
{
    const VIEW    = 'view';
    const EDIT    = 'edit';
    const DELETE  = 'delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Account) {
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

        /** @var Account $account */
        $account = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($account, $user);
            case self::EDIT:
                return $this->canEdit($account, $user);
            case self::DELETE:
                return $this->canDelete($account, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Account $account, User $user)
    {
        if ($this->canDelete($account, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Account $account, User $user)
    {
        if ($this->canDelete($account, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Account $account, User $user)
    {
        return $user->getUserGroups()->contains($account->getOwner());
    }
}
