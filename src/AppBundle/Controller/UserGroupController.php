<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserGroup;
use AppBundle\Form\UserGroupType;
use AppBundle\Service\NotificationManager;
use AppBundle\Service\GroupInvitationManager;
use AppBundle\Repository\GroupInvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("user-group")
 */
class UserGroupController extends Controller
{
    private $em;
    private $notificationManager;
    private $groupInvitationManager;
    private $groupInvitationRepository;

    public function __construct(
        EntityManagerInterface $em,
        NotificationManager $notificationManager,
        GroupInvitationManager $groupInvitationManager,
        GroupInvitationRepository $groupInvitationRepository)
    {
        $this->em = $em;
        $this->notificationManager = $notificationManager;
        $this->groupInvitationManager = $groupInvitationManager;
        $this->groupInvitationRepository = $groupInvitationRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="user-group_index")
     */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups();

        return $this->render('UserGroup/index.html.twig', [
            'user_groups' => $userGroups,
        ]);
    }

    /**
     * @param User $user
     * @Route("/new", name="user-group_new")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $userGroup = new UserGroup();
        $form = $this->createForm(UserGroupType::class, $userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userGroup->setIsDefaultGroup(false);
            $userGroup->setOwner($user);

            if (!$userGroup->getUsers()->contains($user)) {
                $userGroup->addUser($user);
            }

            $this->groupInvitationManager->sendInvitations($userGroup, $form->get('users')->getData()->toArray());
            $this->em->persist($userGroup);
            $this->em->flush();

            return $this->redirectToRoute('user-group_show', [
                'id' => $userGroup->getId()
            ]);
        }

        return $this->render('UserGroup/new.html.twig', [
            'user_group' => $userGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}", name="user-group_show")
     * @IsGranted("view", subject="userGroup")
     */
    public function showAction(UserInterface $user, ?UserGroup $userGroup)
    {
        if ($userGroup == null) {
            $this->addFlash('info', 'User Group no longer exists.');

            return $this->redirectToRoute('homepage');
        }

        if ($userGroup->getOwner() == $user) {

            $invitations = $this->groupInvitationRepository->findBy([
                'userGroup' => $userGroup,
                'active' => true,
            ]);
        }

        else {
            $invitations = [];
        }

        return $this->render('UserGroup/show.html.twig', [
            'user_group' => $userGroup,
            'invitations' => $invitations,
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="user-group_edit")
     * @IsGranted("edit", subject="userGroup")
     */
    public function editAction(Request $request, UserGroup $userGroup, UserInterface $user)
    {
        $editForm = $this->createForm(UserGroupType::class, $userGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->groupInvitationManager->sendInvitations($userGroup, $editForm->get('users')->getData()->toArray());

            $formUsers = $editForm->get('users')->getData()->toArray();
            $groupUsers = $userGroup->getUsers()->toArray();
            $usersToDelete = array_diff($groupUsers, $formUsers);

            foreach ($usersToDelete as $userToDelete) {
                $userGroup->removeUser($userToDelete);
                $this->notificationManager->createNotification(
                    $userToDelete->getDefaultGroup(),
                    "You were deleted from {$userGroup->getName()} group.");
            }

            $this->em->persist($userGroup);
            $this->em->flush();

            return $this->redirectToRoute('user-group_show', [
                'id' => $userGroup->getId()
            ]);
        }

        return $this->render('UserGroup/edit.html.twig', [
            'user_group' => $userGroup,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="user-group_delete")
     * @IsGranted("delete", subject="userGroup")
     */
    public function deleteAction(Request $request, UserGroup $userGroup)
    {
        $this->em->remove($userGroup);
        $this->em->flush();

        return $this->redirectToRoute('user-group_index');
    }
}
