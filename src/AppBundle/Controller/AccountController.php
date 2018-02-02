<?php

namespace AppBundle\Controller;

use AppBundle\Form\AccountType;
use AppBundle\Entity\Account;
use AppBundle\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class AccountController extends Controller
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
      $this->em = $em;
    }

    /**
     * @Route("/getUserGroups", name="user_groups")
     */
    public function getUserGroups(UserInterface $user)
    {
      $userGroups = $user->getUserGroups();
      $groups = $userGroups->toArray();
      // $userGroups = $this->em->getRepository(User::class)->getUserGroups($user->getId());
      // $userEntity = $this->em->getRepository(User::class)->find($user->getId());
      // $userGroups = $userEntity->getUserGroups();


      var_dump($user->getId());
      var_dump($user->getUsername());

      foreach ($groups as $group) {
          echo $group->getId(), '<br>';
      }

      return $this->render('account/base.html.twig', array(
          'userGroups' => $userGroups,
      ));

      // $userGroups = $this->em->getRepository(User::class)->getUserGroups($user->getId());
      //
      // var_dump($user->getId());
      // var_dump($user->getUsername());
      // // echo $userGroups[0]->getId();
      // // echo sizeof($userGroups);
      // $groups = $userGroups[0]->getUserGroups;
      //
      // // foreach ($userGroups as $userGroup) {
      // //     echo $userGroup->getUserGroups(), '<br>';
      // // }
      // foreach ($groups as $group) {
      //     echo $group, '<br>';
      // }
      //
      // return $this->render('account/base.html.twig', array(
      //     'userGroups' => $userGroups,
      // ));
    }

    /**
     * @Route("/account", name="account_index")
     * @Method("GET")
     */
    public function indexAction(UserInterface $user)
    {
        $accounts = $this->em->getRepository(Account::class)->findBy(
          array('owner_id' => 'Keyboard'),
          array('price' => 'ASC')
        );

        return $this->render('account/index.html.twig', array(
            'accounts' => $accounts,
        ));
    }

    /**
     * @Route("/account/new", name="account_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($account);
            $this->em->flush();

            return $this->redirectToRoute('account_show', array('id' => $account->getId()));
        }

        return $this->render('account/new.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a account entity.
     *
     * @Route("/account/{id}", name="account_show")
     * @Method("GET")
     */
    public function showAction(Account $account)
    {
        $deleteForm = $this->createDeleteForm($account);

        return $this->render('account/show.html.twig', array(
            'account' => $account,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing account entity.
     *
     * @Route("/{id}/edit", name="account_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Account $account)
    {
        $deleteForm = $this->createDeleteForm($account);
        $editForm = $this->createForm('AppBundle\Form\AccountType', $account);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('account_edit', array('id' => $account->getId()));
        }

        return $this->render('account/edit.html.twig', array(
            'account' => $account,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a account entity.
     *
     * @Route("/{id}", name="account_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Account $account)
    {
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     * Creates a form to delete a account entity.
     *
     * @param Account $account The account entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Account $account)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('account_delete', array('id' => $account->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
