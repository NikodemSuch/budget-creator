<?php

namespace AppBundle\Controller;

use AppBundle\Report\Report;
use AppBundle\Form\ReportType;
use AppBundle\Service\ReportManager;
use AppBundle\Repository\BudgetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @IsGranted("ROLE_USER")
 * @Route("report")
 */
class ReportController extends Controller
{
    private $reportManager;
    private $budgetRepository;

    public function __construct(ReportManager $reportManager, BudgetRepository $budgetRepository)
    {
        $this->reportManager = $reportManager;
        $this->budgetRepository = $budgetRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="report")
     */
    public function report(Request $request, UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $budgets = $this->budgetRepository->findBy([ 'owner' => $userGroups ]);

        $form = $this->createForm(ReportType::class, null, [ 'budgets' => $budgets ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $formData = $form->getData();

            $report = new Report();
            $report->setTitle($formData['title']);
            $report->setStartDate($formData['start']);
            $report->setEndDate($formData['end']);
            $report->setDetail($formData['details']);
            $report->setBudgets($formData['budgets']->toArray());

            $report = $this->reportManager->createReport($report);

            return $this->render('Report/show.html.twig', [
                'report' => $report,
            ]);
        }

        return $this->render('Report/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
