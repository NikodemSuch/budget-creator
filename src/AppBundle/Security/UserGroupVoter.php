<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserGroupVoter extends Voter
{
    const VIEW    = 'view';
    const EDIT    = 'edit';
    const DELETE  = 'delete';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!($subject instanceof UserGroup)) {
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

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->isOwnedBy($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function canView(UserGroup $subject, User $user): bool
    {
        if ($subject->getIsDefaultGroup()) {
            return false;
        }

        return $subject->getUsers()->contains($user);
    }

    private function isOwnedBy(UserGroup $subject, User $user): bool
    {
        if ($subject->getIsDefaultGroup()) {
            return false;
        }

        return $user === $subject->getOwner();
    }

}
