<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Form\BudgetType;
use AppBundle\Repository\BudgetRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("budget")
 */
class BudgetController extends Controller
{
    private $em;
    private $budgetRepository;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $em, BudgetRepository $budgetRepository, TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->budgetRepository = $budgetRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="budget_index")
     */
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $budgets = $this->budgetRepository->findBy([
            'owner' => $userGroups,
            'archived' => false,
        ]);
        $budgetsBalances = array();

        foreach ($budgets as $budget) {
            $budgetBalance = $this->transactionRepository->getBudgetBalance($budget);
            array_push($budgetsBalances, $budgetBalance);
        }

        $budgetsData = array_map(null, $budgets, $budgetsBalances);

        return $this->render('Budget/index.html.twig', [
            'budgets_data' => $budgetsData,
        ]);
    }

    /**
     * @param User $user
     * @Route("/new", name="budget_new")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $budget = new Budget();
        $form = $this->createForm(BudgetType::class, $budget, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($budget);
            $this->em->flush();

            $this->addFlash('success', 'Budget created!');

            return $this->redirectToRoute('budget_show', [
                'id' => $budget->getId()
            ]);
        }

        return $this->render('Budget/new.html.twig', [
            'budget' => $budget,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="budget_show")
     * @IsGranted("view", subject="budget")
     */
    public function showAction(Request $request, UserInterface $user, Budget $budget = null)
    {
        if ($budget->isArchived() || $budget == null) {
            return $this->render('Budget/show.html.twig', [
                'budget_exists' => false,
            ]);
        }

        $page = $request->query->get('page') ?: 1;
        $resultsPerPage = $request->query->get('results') ?: 10;

        $userGroups = $user->getUserGroups()->toArray();
        $query = $this->transactionRepository->getByGroupsQuery($userGroups);
        $adapter = new DoctrineORMAdapter($query);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($resultsPerPage);
        $pagerfanta->setCurrentPage($page);

        return $this->render('Budget/show.html.twig', [
            'transaction_pager' => $pagerfanta,
            'budget' => $budget,
            'budget_balance' => $this->transactionRepository->getBudgetBalance($budget),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="budget_edit")
     * @IsGranted("edit", subject="budget")
     */
    public function editAction(Request $request, Budget $budget, UserInterface $user)
    {
        if ($budget->isArchived()) {
            throw $this->createNotFoundException();
        }

        $owner = $budget->getOwner();
        $hasTransactions = (bool) $budget->getTransactions()->count();

        $editForm = $this->createForm(BudgetType::class, $budget, [
            'user' => $user,
            'owner' => $owner,
            'has_transactions' => $hasTransactions,
        ]);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            if ($hasTransactions && $budget->getOwner() != $owner) {
                throw new BadRequestHttpException();
            }

            $this->em->flush();

            $this->addFlash('success', 'Budget updated!');

            return $this->redirectToRoute('budget_show', [
                'id' => $budget->getId()
            ]);
        }

        return $this->render('Budget/edit.html.twig', [
            'budget' => $budget,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="budget_delete")
     * @IsGranted("delete", subject="budget")
     */
    public function deleteAction(Budget $budget)
    {
        if ($budget->isArchived()) {
            throw $this->createNotFoundException();
        }

        $budget->setArchived(true);
        $this->em->persist($budget);
        $this->em->flush();

        $this->addFlash('success', 'Budget deleted!');

        return $this->redirectToRoute('budget_index');
    }
}
