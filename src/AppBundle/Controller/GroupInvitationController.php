<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GroupInvitation;
use AppBundle\Service\NotificationManager;
use AppBundle\Service\GroupInvitationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("group-invitation")
 */
class GroupInvitationController extends Controller
{
    private $notificationManager;
    private $groupInvitationManager;

    public function __construct(NotificationManager $notificationManager, GroupInvitationManager $groupInvitationManager)
    {
        $this->notificationManager = $notificationManager;
        $this->groupInvitationManager = $groupInvitationManager;
    }

    /**
     * @Route("/{id}", name="group-invitation_show")
     */
    public function showAction(Request $request, UserInterface $user, ?GroupInvitation $groupInvitation)
    {
        if ($groupInvitation == null) {
            $this->addFlash('info', 'Invitation is no longer valid.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('UserGroup/invitation.html.twig', [
            'expiration_date' => $this->groupInvitationManager->getExpirationDate($groupInvitation),
            'invitation_expired' => $this->groupInvitationManager->hasExpired($groupInvitation),
            'group_invitation' => $groupInvitation,
        ]);
    }

    /**
     * @Route("/{id}/accept", name="group-invitation_accept")
     * @IsGranted("invitation_respond", subject="groupInvitation")
     */
    public function acceptAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        if ($this->groupInvitationManager->hasExpired($groupInvitation)) {
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
     * @Route("/{id}/decline", name="group-invitation_decline")
     * @IsGranted("invitation_respond", subject="groupInvitation")
     */
    public function declineAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        if ($this->groupInvitationManager->hasExpired($groupInvitation)) {
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
