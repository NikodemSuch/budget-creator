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
    private $notificationManager;
    private $expirationTime;

    public function __construct(EntityManagerInterface $em, GroupInvitationRepository $groupInvitationRepository, NotificationManager $notificationManager)
    {
        $this->em = $em;
        $this->groupInvitationRepository = $groupInvitationRepository;
        $this->notificationManager = $notificationManager;
    }

    public function setExpirationTime($configInvitation)
    {
        $this->expirationTime = \DateInterval::createFromDateString($configInvitation);
    }

    public function getExpirationDate(GroupInvitation $groupInvitation): \DateTimeImmutable
    {
        return $groupInvitation->getCreatedOn()->add($this->expirationTime);
    }

    public function hasExpired(GroupInvitation $groupInvitation): bool
    {
        $now = new \DateTimeImmutable();

        return $now > $this->getExpirationDate($groupInvitation);
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
                // This needs to be here, because we need id of groupInvitation entity, and it's setting after flush().
                $this->sendInvitationNotification($groupInvitation);
            }
        }
    }

    public function sendInvitationNotification($groupInvitation)
    {
        $notification = $this->notificationManager->createNotification(
                    $groupInvitation->getUser()->getDefaultGroup(),
                    "Invitation to group {$groupInvitation->getUserGroup()->getName()}",
                    'group-invitation_show', ['id' => $groupInvitation->getId()], true);

        $groupInvitation->setNotification($notification);
        $this->em->persist($groupInvitation);
        $this->em->flush();
    }

    public function acceptInvitation($groupInvitation)
    {
        $user = $groupInvitation->getUser();
        $userGroup = $groupInvitation->getUserGroup();

        $user->addUserGroup($userGroup);
        $groupInvitation->setActive(false);

        $this->notificationManager->createNotification(
                $groupInvitation->getUserGroup()->getOwner()->getDefaultGroup(),
                "{$groupInvitation->getUser()} has accepted your invitation to {$groupInvitation->getUserGroup()} group.",
                'user-group_show', ['id' => $groupInvitation->getUserGroup()->getId()]);

        // Mark Invitation notification as read
        $this->notificationManager->setUnreadStatus($groupInvitation->getNotification()->getId(), $user, false);
        $this->em->persist($groupInvitation);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function declineInvitation($groupInvitation)
    {
        $groupInvitation->setActive(false);

        $this->notificationManager->createNotification(
            $groupInvitation->getUserGroup()->getOwner()->getDefaultGroup(),
            "{$groupInvitation->getUser()} has declined your invitation to {$groupInvitation->getUserGroup()} group.",
            'user-group_show', ['id' => $groupInvitation->getUserGroup()->getId()]);

        // Mark Invitation notification as read
        $this->notificationManager->setUnreadStatus($groupInvitation->getNotification()->getId(), $groupInvitation->getUser(), false);
        $this->em->persist($groupInvitation);
        $this->em->flush();
    }

    public function deactivateExpiredInvitations()
    {
        $invitations = $this->groupInvitationRepository->findAll();

        foreach ($invitations as $invitation) {
            if ($this->hasExpired($invitation)) {
                $invitation->setActive(false);
                $this->notificationManager->setUnreadStatus($invitation->getNotification()->getId(), $invitation->getUser(), false);
                $this->em->persist($invitation);
            }
        }

        $this->em->flush();
    }

    public function deleteExpiredInvitations()
    {
        $invitations = $this->groupInvitationRepository->findAll();

        foreach ($invitations as $invitation) {
            if ($this->hasExpired($invitation)) {
                $this->em->delete($invitation);
            }
        }

        $this->em->flush();
    }
}
