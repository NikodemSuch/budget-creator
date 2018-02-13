<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Form\BudgetType;
use AppBundle\Repository\BudgetRepository;
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

    public function __construct(EntityManagerInterface $em, BudgetRepository $budgetRepository)
    {
        $this->em = $em;
        $this->budgetRepository = $budgetRepository;
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

        return $this->render('budget/index.html.twig', [
            'budgets' => $budgets,
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
     * @Route("/{id}/show", name="budget_show")
     */
    public function showAction(Budget $budget)
    {
        $deleteForm = $this->createDeleteForm($budget);

        return $this->render('budget/show.html.twig', [
            'budget' => $budget,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="budget_edit")
     */
    public function editAction(Request $request, Budget $budget, UserInterface $user)
    {
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
        $form = $this->createDeleteForm($budget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($budget);
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
