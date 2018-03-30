<?php

namespace AppBundle\Service;

use AppBundle\Entity\UserGroup;
use AppBundle\Entity\GroupInvitation;
use AppBundle\Repository\GroupInvitationRepository;
use AppBundle\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class GroupInvitationManager
{
    private $em;
    private $groupInvitationRepository;

    public function __construct(EntityManagerInterface $em, GroupInvitationRepository $groupInvitationRepository, NotificationManager $notificationManager)
    {
        $this->em = $em;
        $this->groupInvitationRepository = $groupInvitationRepository;
        $this->notificationManager = $notificationManager;
    }

    public function sendInvitations(UserGroup $userGroup, $wantedMembers)
    {
        $currentMembers = $userGroup->getUsers()->toArray();
        $usersToInvite = array_diff($wantedMembers, $currentMembers);

        foreach ($usersToInvite as $user) {

            $userAlreadyInGroup = $user->getUserGroups()->contains($userGroup);
            $invitationAlreadySent = $this->groupInvitationRepository->findBy([
                'user' => $user,
                'userGroup' => $userGroup,
                'active' => true,
            ]);

            if (!$invitationAlreadySent && !$userAlreadyInGroup) {
                $groupInvitation = new GroupInvitation($user, $userGroup);

                $this->em->persist($groupInvitation);
                $this->em->flush();
                $this->sendInvitationNotification($groupInvitation);
            }
        }
    }

    public function sendInvitationNotification($groupInvitation)
    {
        $this->notificationManager->createNotification(
                    $groupInvitation->getUser()->getDefaultGroup(),
                    "Invitation to group {$groupInvitation->getUserGroup()->getName()}",
                    'group_invitation_show', ['id' => $groupInvitation->getId()], false);
    }

    public function acceptInvitation($groupInvitation)
    {
        $user = $groupInvitation->getUser();
        $userGroup = $groupInvitation->getUserGroup();

        $user->addUserGroup($userGroup);
        $groupInvitation->setActive(false);

        $this->em->persist($groupInvitation);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function declineInvitation($groupInvitation)
    {
        $groupInvitation->setActive(false);

        $this->em->persist($groupInvitation);
        $this->em->flush();
    }

}
