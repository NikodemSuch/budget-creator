<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GroupInvitation;
use AppBundle\Repository\GroupInvitationRepository;
use AppBundle\Service\GroupInvitationManager;
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
    private $groupInvitationManager;
    private $groupInvitationRepository;

    public function __construct(GroupInvitationManager $groupInvitationManager, GroupInvitationRepository $groupInvitationRepository)
    {
        $this->groupInvitationManager = $groupInvitationManager;
        $this->groupInvitationRepository = $groupInvitationRepository;
    }

    /**
     * @Route("/{id}", name="group_invitation_show")
     */
    public function showAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        if (!$user->getUserGroups()->contains($groupInvitation->getUserGroup()) && $groupInvitation->isActive()) {

            return $this->render('usergroup/invitation.html.twig', [
                'group_invitation' => $groupInvitation,
            ]);
        }

        else {
            $this->addFlash('warning', 'Invitation is no more active!');

            return $this->render('usergroup/invitation.html.twig');
        }
    }

    /**
     * @Route("/{id}/accept", name="group_invitation_accept")
     * @IsGranted("accept", subject="groupInvitation")
     */
    public function acceptAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        if (!$user->getUserGroups()->contains($groupInvitation->getUserGroup()) && $groupInvitation->isActive()) {
            $this->groupInvitationManager->acceptInvitation($groupInvitation);
            $this->addFlash('success', 'Invitation accepted!');

            return $this->redirectToRoute('homepage');
        }

        else {
            $this->addFlash('warning', 'Invitation already accepted!');

            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/{id}/decline", name="group_invitation_decline")
     * @IsGranted("accept", subject="groupInvitation")
     */
    public function declineAction(Request $request, UserInterface $user, GroupInvitation $groupInvitation)
    {
        if (!$user->getUserGroups()->contains($groupInvitation->getUserGroup()) && $groupInvitation->isActive()) {
            $this->groupInvitationManager->declineInvitation($groupInvitation);
            $this->addFlash('success', 'Invitation declined!');

            return $this->redirectToRoute('homepage');
        }

        else {
            $this->addFlash('warning', 'Invitation already declined!');

            return $this->redirectToRoute('homepage');
        }
    }
}
