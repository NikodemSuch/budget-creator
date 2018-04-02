<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GroupInvitation;
use AppBundle\Service\NotificationManager;
use AppBundle\Service\GroupInvitationManager;
use AppBundle\Repository\GroupInvitationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("GroupInvitation")
 */
class GroupInvitationController extends Controller
{
    private $notificationManager;
    private $groupInvitationManager;
    private $groupInvitationRepository;

    public function __construct(NotificationManager $notificationManager, GroupInvitationManager $groupInvitationManager, GroupInvitationRepository $groupInvitationRepository)
    {
        $this->notificationManager = $notificationManager;
        $this->groupInvitationManager = $groupInvitationManager;
        $this->groupInvitationRepository = $groupInvitationRepository;
    }

    /**
     * @Route("/{id}", name="group_invitation_show")
     */
    public function showAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        return $this->render('usergroup/invitation.html.twig', [
            'invitation_active' => !$user->getUserGroups()->contains($groupInvitation->getUserGroup()) && $groupInvitation->isActive(),
            'group_invitation' => $groupInvitation,
        ]);
    }

    /**
     * @Route("/{id}/accept", name="group_invitation_accept")
     * @IsGranted("invitation_respond", subject="groupInvitation")
     */
    public function acceptAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        // Mark Invitation notification as read
        $this->notificationManager->setUnreadStatus($groupInvitation->getNotification()->getId(), $user, false);

        if ($groupInvitation->hasExpired()) {
            $this->addFlash('warning', 'Invitation has expired!');

            return $this->redirectToRoute('homepage');
        }

        elseif ($user->getUserGroups()->contains($groupInvitation->getUserGroup()) || !$groupInvitation->isActive()) {
            $this->addFlash('warning', 'You have already responded to this invitation!');

            return $this->redirectToRoute('homepage');
        }

        else {
            $this->groupInvitationManager->acceptInvitation($groupInvitation);
            $this->addFlash('success', 'Invitation accepted!');

            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/{id}/decline", name="group_invitation_decline")
     * @IsGranted("invitation_respond", subject="groupInvitation")
     */
    public function declineAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        // Mark Invitation notification as read
        $this->notificationManager->setUnreadStatus($groupInvitation->getNotification()->getId(), $user, false);

        if ($groupInvitation->hasExpired()) {
            $this->addFlash('warning', 'Invitation has expired!');

            return $this->redirectToRoute('homepage');
        }

        elseif ($user->getUserGroups()->contains($groupInvitation->getUserGroup()) || !$groupInvitation->isActive()) {
            $this->addFlash('warning', 'You have already responded to this invitation!');

            return $this->redirectToRoute('homepage');
        }

        else {
            $this->groupInvitationManager->declineInvitation($groupInvitation);
            $this->addFlash('success', 'Invitation declined!');

            return $this->redirectToRoute('homepage');
        }
    }
}
