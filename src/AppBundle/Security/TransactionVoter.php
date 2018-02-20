<?php

namespace AppBundle\Security;

use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransactionVoter extends Voter
{
    const VIEW    = 'view';
    const EDIT    = 'edit';
    const DELETE  = 'delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Transaction) {
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

        /** @var Transaction $transaction */
        $transaction = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($transaction, $user);
            case self::EDIT:
                return $this->canEdit($transaction, $user);
            case self::DELETE:
                return $this->canDelete($transaction, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Transaction $transaction, User $user)
    {
        if ($this->canDelete($transaction, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Transaction $transaction, User $user)
    {
        if ($this->canDelete($transaction, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Transaction $transaction, User $user)
    {
        return $user->getUserGroups()->contains($transaction->getAccount()->getOwner());
    }
}
