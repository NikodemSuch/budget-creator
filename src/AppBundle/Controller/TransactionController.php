<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Entity\Transaction;
use AppBundle\Form\TransactionType;
use AppBundle\Repository\TransactionRepository;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\BudgetRepository;
use AppBundle\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("transaction")
 */
class TransactionController extends Controller
{
    private $em;
    private $accountRepository;
    private $budgetRepository;
    private $transactionRepository;
    private $notificationManager;

    public function __construct(
        EntityManagerInterface $em,
        AccountRepository $accountRepository,
        BudgetRepository $budgetRepository,
        TransactionRepository $transactionRepository,
        NotificationManager $notificationManager)
    {
        $this->em = $em;
        $this->accountRepository = $accountRepository;
        $this->budgetRepository = $budgetRepository;
        $this->transactionRepository = $transactionRepository;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @param User $user
     * @Route("/", name="transaction_index")
     */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();

        $transactions = $this->transactionRepository->getByGroups($userGroups);

        return $this->render('Transaction/index.html.twig', [
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

        if ($request->query->get('account')) {
            $account = $this->accountRepository->find($request->query->get('account'));
            $transaction->setAccount($account);
        }

        if ($request->query->get('budget')) {
            $budget = $this->budgetRepository->find($request->query->get('budget'));
            $transaction->setBudget($budget);
        }

        $accounts = $this->accountRepository->findBy([
            'owner' => $userGroups
        ]);

        $budgets = $this->budgetRepository->findBy([
            'owner' => $userGroups
        ]);

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $budgetBalanceBefore = $this->transactionRepository->getBudgetBalance($transaction->getBudget());

            $transaction->setCreator($user);
            $this->em->persist($transaction);
            $this->em->flush();

            $budgetBalanceAfter = $this->transactionRepository->getBudgetBalance($transaction->getBudget());
            if ($budgetBalanceAfter < 0 && $budgetBalanceBefore >= 0) {
                $this->notificationManager->createNotification(
                    $transaction->getOwner(),
                    "Budget {$transaction->getBudget()->getName()} has been exceeded.",
                    'budget_show', ['id' => $transaction->getBudget()->getId()]);
            }

            return $this->redirectToRoute('transaction_show', ['id' => $transaction->getId()]);
        }

        return $this->render('Transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_show")
     * @IsGranted("view", subject="transaction")
     */
    public function showAction(Transaction $transaction)
    {
        return $this->render('Transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="transaction_edit")
     * @IsGranted("edit", subject="transaction")
     */
    public function editAction(Request $request, Transaction $transaction, UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->accountRepository->findBy([
            'owner' => $userGroups
        ]);

        $budgets = $this->budgetRepository->findBy([
            'owner' => $userGroups
        ]);

        $editForm = $this->createForm(TransactionType::class, $transaction, [
            'user' => $user,
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $budgetBalanceBefore = $this->transactionRepository->getBudgetBalance($transaction->getBudget());

            $this->em->flush();

            $budgetBalanceAfter = $this->transactionRepository->getBudgetBalance($transaction->getBudget());
            if ($budgetBalanceAfter < 0 && $budgetBalanceBefore >= 0) {
                $this->notificationManager->createNotification(
                    $transaction->getOwner(),
                    "Budget {$transaction->getBudget()->getName()} has been exceeded.",
                    'budget_show', ['id' => $transaction->getBudget()->getId()]);
            }

            return $this->redirectToRoute('transaction_show', [
                'id' => $transaction->getId()
            ]);
        }

        return $this->render('Transaction/edit.html.twig', [
            'transaction' => $transaction,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="transaction_delete")
     * @IsGranted("delete", subject="transaction")
     */
    public function deleteAction(Request $request, Transaction $transaction)
    {
        $this->em->remove($transaction);
        $this->em->flush();

        return $this->redirectToRoute('transaction_index');
    }
}
