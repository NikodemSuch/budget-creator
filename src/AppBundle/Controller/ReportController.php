<?php

namespace AppBundle\Controller;

use AppBundle\Report\Report;
use AppBundle\Form\ReportType;
use AppBundle\Service\ReportManager;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\BudgetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @param User $user
     * @Route("/", name="report")
     */
    public function reportAction(Request $request, UserInterface $user)
    {
        $report = new Report();
        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->accountRepository->findBy(['owner' => $userGroups]);
        $budgets = $this->budgetRepository->findBy(['owner' => $userGroups]);

        $form = $this->createForm(ReportType::class, $report, [
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($report->getReportables()->toArray() as $reportable) {

                if (!in_array($reportable->getOwner(), $userGroups)) {
                    throw new BadRequestHttpException();
                }
            }
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
