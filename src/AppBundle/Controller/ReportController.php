<?php

namespace AppBundle\Controller;

use AppBundle\Report\Report;
use AppBundle\Form\ReportType;
use AppBundle\Service\ReportManager;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\BudgetRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $reportData = $request->request->get('report');

        if ($reportData) {

            if ($reportData['type'] == 'accounts') {
                $accounts = $this->accountRepository->findById($reportData['accounts']);
                $form->get('accounts')->setData($accounts);
                $form->get('type')->setData($reportData['type']);
            }

            elseif ($reportData['type'] == 'budgets') {
                $budgets = $this->budgetRepository->findById($reportData['budgets']);
                $form->get('budgets')->setData($budgets);
                $form->get('type')->setData($reportData['type']);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->denyAccessUnlessGranted('create', $report);
            $report = $this->reportManager->createReport($report, $request->getLocale());
            $generatePdf = $form->get('generatePdf')->isClicked();

            if ($generatePdf) {
                return $this->generatePdf($user, $report);
            }

            return $this->render('Report/show.html.twig', [
                'report' => $report,
            ]);
        }

        return $this->render('Report/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function generatePdf(UserInterface $user, Report $report)
    {
        $filename = sprintf('report-%s-%s.pdf', $user, date('Y-m-d'));
        $html = $this->renderView('Report/pdf.html.twig', [
            'report' => $report,
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, [
                'orientation' => 'portrait',
                'enable-javascript' => false,
                'lowquality' => false,
                'page-size' => "A4",
                'encoding' => 'utf-8',
                'dpi' => 300,
            ]),
            $filename
        );
    }
}
