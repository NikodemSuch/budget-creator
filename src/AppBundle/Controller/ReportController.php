<?php

namespace AppBundle\Controller;

use AppBundle\Report\Report;
use AppBundle\Form\ReportType;
use AppBundle\Service\ReportManager;
use AppBundle\Repository\AccountRepository;
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
    private $accountRepository;
    private $budgetRepository;

    public function __construct(
        ReportManager $reportManager,
        AccountRepository $accountRepository,
        BudgetRepository $budgetRepository)
    {
        $this->reportManager = $reportManager;
        $this->accountRepository = $accountRepository;
        $this->budgetRepository = $budgetRepository;
    }

     /**
     * @Route("/choose", name="choose_report_type")
     */
    public function chooseAction(Request $request)
    {
        return $this->render('Report/choose.html.twig');
    }

    /**
     * @param User $user
     * @Route("/", name="report")
     */
    public function reportAction(Request $request, UserInterface $user)
    {
        $report = new Report();
        $userGroups = $user->getUserGroups()->toArray();
        $reportType = $request->query->get('type');

        $accounts = $this->accountRepository->findBy(['owner' => $userGroups]);
        $budgets = $this->budgetRepository->findBy(['owner' => $userGroups]);

        $form = $this->createForm(ReportType::class, $report, [
            'accounts' => $accounts,
            'budgets' => $budgets,
            'report_type' => $reportType,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
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
