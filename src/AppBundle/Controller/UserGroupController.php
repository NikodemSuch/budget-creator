<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Usergroup controller.
 *
 * @Route("usergroup")
 */
class UserGroupController extends Controller
{
    /**
     * Lists all userGroup entities.
     *
     * @Route("/", name="usergroup_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userGroups = $em->getRepository('AppBundle:UserGroup')->findAll();

        return $this->render('usergroup/index.html.twig', array(
            'userGroups' => $userGroups,
        ));
    }

    /**
     * Creates a new userGroup entity.
     *
     * @Route("/new", name="usergroup_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $userGroup = new Usergroup();
        $form = $this->createForm('AppBundle\Form\UserGroupType', $userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userGroup);
            $em->flush();

            return $this->redirectToRoute('usergroup_show', array('id' => $userGroup->getId()));
        }

        return $this->render('usergroup/new.html.twig', array(
            'userGroup' => $userGroup,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a userGroup entity.
     *
     * @Route("/{id}", name="usergroup_show")
     * @Method("GET")
     */
    public function showAction(UserGroup $userGroup)
    {
        $deleteForm = $this->createDeleteForm($userGroup);

        return $this->render('usergroup/show.html.twig', array(
            'userGroup' => $userGroup,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing userGroup entity.
     *
     * @Route("/{id}/edit", name="usergroup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, UserGroup $userGroup)
    {
        $deleteForm = $this->createDeleteForm($userGroup);
        $editForm = $this->createForm('AppBundle\Form\UserGroupType', $userGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('usergroup_edit', array('id' => $userGroup->getId()));
        }

        return $this->render('usergroup/edit.html.twig', array(
            'userGroup' => $userGroup,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a userGroup entity.
     *
     * @Route("/{id}", name="usergroup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, UserGroup $userGroup)
    {
        $form = $this->createDeleteForm($userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userGroup);
            $em->flush();
        }

        return $this->redirectToRoute('usergroup_index');
    }

    /**
     * Creates a form to delete a userGroup entity.
     *
     * @param UserGroup $userGroup The userGroup entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(UserGroup $userGroup)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('usergroup_delete', array('id' => $userGroup->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
