<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Report\Report;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReportVoter extends Voter
{
    const CREATE  = 'create';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::CREATE] )) {
            return false;
        }

        if (!($subject instanceof Report)) {
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
            case self::CREATE:
                return $this->isDataValid($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function isDataValid(Report $subject, User $user): bool
    {
        $userGroups = $user->getUserGroups()->toArray();

        foreach ($subject->getReportables() as $reportable) {

            if (!in_array($reportable->getOwner(), $userGroups)) {
                return false;
            }
        }

        return true;
    }
}
