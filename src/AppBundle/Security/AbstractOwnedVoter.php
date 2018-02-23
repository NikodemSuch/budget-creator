<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Security\Owned;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractOwnedVoter extends Voter
{
    const VIEW    = 'view';
    const EDIT    = 'edit';
    const DELETE  = 'delete';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!($subject instanceof Owned)) {
            return false;
        }

        return $this->checkSubjectType($subject);
    }

    protected abstract function checkSubjectType(Owned $subject): bool;

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return $this->isOwnedBy($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    protected function isOwnedBy(Owned $subject, User $user)
    {
        return $user->getUserGroups()->contains($subject->getOwner($subject));
    }
}
