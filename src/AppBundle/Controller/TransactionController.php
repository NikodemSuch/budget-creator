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
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @Template("Transaction/index.html.twig")
     */
    public function indexAction(Request $request, UserInterface $user)
    {
        $page = $request->query->get('page') ?: 1;
        $resultsPerPage = $request->query->get('results') ?: 10;

        $userGroups = $user->getUserGroups()->toArray();
        $query = $this->transactionRepository->getByGroupsQuery($userGroups);
        $adapter = new DoctrineORMAdapter($query);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($resultsPerPage);
        $pagerfanta->setCurrentPage($page);

        return ['transaction_pager' => $pagerfanta];
    }

    /**
     * @param User $user
     * @Route("/new", name="transaction_new")
     * @Template("Transaction/new.html.twig")
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

        $accounts = $this->accountRepository->findBy(['owner' => $userGroups]);
        $budgets = $this->budgetRepository->findBy(['owner' => $userGroups]);

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

        return [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="transaction_show")
     * @IsGranted("view", subject="transaction")
     * @Template("Transaction/show.html.twig")
     */
    public function showAction(Transaction $transaction)
    {
        if ($transaction->isArchived()) {
            throw $this->createNotFoundException();
        }

        return ['transaction' => $transaction];
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="transaction_edit")
     * @IsGranted("edit", subject="transaction")
     * @Template("Transaction/edit.html.twig")
     */
    public function editAction(Request $request, Transaction $transaction, UserInterface $user)
    {
        if ($transaction->isArchived()) {
            throw $this->createNotFoundException();
        }

        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->accountRepository->findBy(['owner' => $userGroups]);
        $budgets = $this->budgetRepository->findBy(['owner' => $userGroups]);

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

            return $this->redirectToRoute('transaction_show', ['id' => $transaction->getId()]);
        }

        return [
            'transaction' => $transaction,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="transaction_delete")
     * @IsGranted("delete", subject="transaction")
     */
    public function deleteAction(Request $request, Transaction $transaction)
    {
        if ($transaction->isArchived()) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($transaction);
        $this->em->flush();

        return $this->redirectToRoute('transaction_index');
    }
}
