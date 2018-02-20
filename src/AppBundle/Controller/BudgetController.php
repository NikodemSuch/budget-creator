<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Form\BudgetType;
use AppBundle\Repository\BudgetRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
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
        ]);
        $budgetsBalances = array();

        foreach ($budgets as $budget) {
            $budgetBalance = $this->transactionRepository->getBudgetBalance($budget->getId());
            array_push($budgetsBalances, $budgetBalance);
        }

        $budgetsData = array_map(null, $budgets, $budgetsBalances);

        return $this->render('budget/index.html.twig', [
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

            return $this->redirectToRoute('budget_show', [
                'id' => $budget->getId()
            ]);
        }

        return $this->render('budget/new.html.twig', [
            'budget' => $budget,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="budget_show")
     */
    public function showAction(UserInterface $user, Budget $budget)
    {
        $this->denyAccessUnlessGranted('view', $budget);
        $userGroups = $user->getUserGroups()->toArray();
        $deleteForm = $this->createDeleteForm($budget);
        $budgetBalance = $this->transactionRepository->getBudgetBalance($budget->getId());

        $transactions = $this->transactionRepository->findBy([
            'creator' => $userGroups,
            'budget' => $budget->getId(),
        ]);

        return $this->render('budget/show.html.twig', [
            'transactions' => $transactions,
            'budget' => $budget,
            'budget_balance' => $budgetBalance,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="budget_edit")
     */
    public function editAction(Request $request, Budget $budget, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('edit', $budget);
        $editForm = $this->createForm(BudgetType::class, $budget, [
            'user' => $user,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('budget_show', [
                'id' => $budget->getId()
            ]);
        }

        return $this->render('budget/edit.html.twig', [
            'budget' => $budget,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="budget_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Budget $budget)
    {
        $this->denyAccessUnlessGranted('delete', $budget);
        $form = $this->createDeleteForm($budget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($budget);
            $this->em->flush();
        }

        return $this->redirectToRoute('budget_index');
    }

    /**
     * @param Budget $budget
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(Budget $budget)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('budget_delete', ['id' => $budget->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
