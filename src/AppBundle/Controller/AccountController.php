<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
* @Route("account")
*/
class AccountController extends Controller
{
    private $em;
    private $accountRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
    * @Route("/", name="account_index")
    */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $accounts = $this->em->getRepository('AppBundle:Account')->findBy([
            'owner' => $userGroups
        ]);

        return $this->render('account/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    /**
    * @Route("/new", name="account_new")
    */
    public function newAction(Request $request, UserInterface $user)
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account,[
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($account);
            $this->em->flush();
            return $this->redirectToRoute('account_show', ['id' => $account->getId()]);
        }

        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/{id}/show", name="account_show")
    */
    public function showAction(Account $account)
    {
        $deleteForm = $this->createDeleteForm($account);

        return $this->render('account/show.html.twig', [
            'account' => $account,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
    * @Route("/{id}/edit", name="account_edit")
    */
    public function editAction(Request $request, Account $account, UserInterface $user)
    {
        $deleteForm = $this->createDeleteForm($account);
        $editForm = $this->createForm(AccountType::class, $account, [
            'user' => $user,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
    * @Route("/{id}/delete", name="account_delete")
    * @Method("DELETE")
    */
    public function deleteAction(Request $request, Account $account)
    {
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($account);
            $this->em->remove($account);
            $this->em->flush();
        }

        return $this->redirectToRoute('account_index');
    }

    /**
    * @param Account $account The account entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createDeleteForm(Account $account)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('account_delete', ['id' => $account->getId()]))
        ->setMethod('DELETE')
        ->getForm()
        ;
    }
}
