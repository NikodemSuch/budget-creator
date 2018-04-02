<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\GroupInvitation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupInvitationVoter extends Voter
{
    const SUPPORTED_ATTRIBUTE = 'invitation_respond';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, array(self::SUPPORTED_ATTRIBUTE))) {
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
            case self::SUPPORTED_ATTRIBUTE:
                return $this->canRespond($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function canRespond(GroupInvitation $subject, User $user): bool
    {
        return $user === $subject->getUser();
    }
}
