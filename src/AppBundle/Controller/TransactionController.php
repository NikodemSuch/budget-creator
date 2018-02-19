<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Entity\Transaction;
use AppBundle\Form\TransactionType;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("transaction")
 */
class TransactionController extends Controller
{
    private $em;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $em, TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="transaction_index")
     */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();

        $transactions = $this->transactionRepository->findBy([
            'creator' => $userGroups,
        ]);

        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * @param User $user
     * @Route("/new", name="transaction_new")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $transaction = new Transaction();
        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->em->getRepository('AppBundle:Account')->findBy([
            'owner' => $userGroups
        ]);

        $budgets = $this->em->getRepository('AppBundle:Budget')->findBy([
            'owner' => $userGroups
        ]);

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setCreator($user);
            $this->em->persist($transaction);
            $this->em->flush();

            return $this->redirectToRoute('transaction_show', ['id' => $transaction->getId()]);
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_show")
     */
    public function showAction(Transaction $transaction)
    {
        $deleteForm = $this->createDeleteForm($transaction);

        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="transaction_edit")
     */
    public function editAction(Request $request, Transaction $transaction, UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->em->getRepository('AppBundle:Account')->findBy([
            'owner' => $userGroups
        ]);

        $budgets = $this->em->getRepository('AppBundle:Budget')->findBy([
            'owner' => $userGroups
        ]);

        $editForm = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="transaction_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Transaction $transaction)
    {
        $form = $this->createDeleteForm($transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($transaction);
            $this->em->flush();
        }

        return $this->redirectToRoute('transaction_index');
    }

    /**
     * @param Transaction $transaction
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(Transaction $transaction)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('transaction_delete', ['id' => $transaction->getId()]))
        ->setMethod('DELETE')
        ->getForm()
        ;
    }
}
