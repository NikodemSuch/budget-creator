<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserGroup;
use AppBundle\Form\UserGroupType;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserGroupRepository;
use AppBundle\Service\GroupInvitationManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    private $userRepository;
    private $userGroupRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository,
        GroupInvitationManager $groupInvitationManager)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->groupInvitationManager = $groupInvitationManager;
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
    public function showAction(UserInterface $user, UserGroup $userGroup)
    {
        $deleteForm = $this->createDeleteForm($userGroup);

        return $this->render('UserGroup/show.html.twig', [
            'user_group' => $userGroup,
            'delete_form' => $deleteForm->createView(),
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
     * @Method("DELETE")
     * @IsGranted("delete", subject="userGroup")
     */
    public function deleteAction(Request $request, UserGroup $userGroup)
    {
        $form = $this->createDeleteForm($userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($userGroup);
            $this->em->flush();
        }

        return $this->redirectToRoute('user-group_index');
    }

    /**
     * @param UserGroup $userGroup
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(UserGroup $userGroup)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user-group_delete', ['id' => $userGroup->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
