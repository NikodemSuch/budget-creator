<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\GroupInvitation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupInvitationVoter extends Voter
{
    const ACCEPT = 'accept';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, array(self::ACCEPT))) {
            return false;
        }

        if (!($subject instanceof GroupInvitation)) {
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
            case self::ACCEPT:
                return $this->isOwnedBy($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function isOwnedBy(GroupInvitation $subject, User $user): bool
    {
        return $user === $subject->getUser();
    }

}