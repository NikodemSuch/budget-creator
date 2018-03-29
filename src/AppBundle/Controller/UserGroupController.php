<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserGroup;
use AppBundle\Form\UserGroupType;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserGroupRepository;
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
 * @Route("usergroup")
 */
class UserGroupController extends Controller
{
    private $em;
    private $userRepository;
    private $userGroupRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, UserGroupRepository $userGroupRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="usergroup_index")
     */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups();

        return $this->render('usergroup/index.html.twig', [
            'user_groups' => $userGroups,
        ]);
    }

    /**
     * @param User $user
     * @Route("/new", name="usergroup_new")
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

            $this->em->persist($userGroup);
            $this->em->flush();

            return $this->redirectToRoute('usergroup_show', [
                'id' => $userGroup->getId()
            ]);
        }

        return $this->render('usergroup/new.html.twig', [
            'user_group' => $userGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}", name="usergroup_show")
     * @IsGranted("view", subject="userGroup")
     */
    public function showAction(UserInterface $user, UserGroup $userGroup)
    {
        $deleteForm = $this->createDeleteForm($userGroup);

        return $this->render('usergroup/show.html.twig', [
            'user_group' => $userGroup,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="usergroup_edit")
     * @IsGranted("edit", subject="userGroup")
     */
    public function editAction(Request $request, UserGroup $userGroup, UserInterface $user)
    {
        $editForm = $this->createForm(UserGroupType::class, $userGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->em->persist($userGroup);
            $this->em->flush();

            return $this->redirectToRoute('usergroup_show', [
                'id' => $userGroup->getId()
            ]);
        }

        return $this->render('usergroup/edit.html.twig', [
            'user_group' => $userGroup,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="usergroup_delete")
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

        return $this->redirectToRoute('usergroup_index');
    }

    /**
     * @param UserGroup $userGroup
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(UserGroup $userGroup)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('usergroup_delete', ['id' => $userGroup->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}