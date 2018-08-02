<?php

namespace AppBundle\Controller;

use AppBundle\Factory\ReportGeneratorFactory;
use AppBundle\Form\ReportType;
use AppBundle\Report\Report;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\BudgetRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @IsGranted("ROLE_USER")
 * @Route("report")
 */
class ReportController extends Controller
{
    private $reportGeneratorFactory;
    private $accountRepository;
    private $budgetRepository;

    public function __construct(
        ReportGeneratorFactory $reportGeneratorFactory,
        AccountRepository $accountRepository,
        BudgetRepository $budgetRepository)
    {
        $this->reportGeneratorFactory = $reportGeneratorFactory;
        $this->accountRepository = $accountRepository;
        $this->budgetRepository = $budgetRepository;
    }

    /**
     * @param User $user
     * @Route("/new", name="report_new")
     * @Template("Report/new.html.twig")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $report = new Report();
        $userGroups = $user->getUserGroups()->toArray();

        $accounts = $this->accountRepository->findBy(['owner' => $userGroups]);
        $budgets = $this->budgetRepository->findBy(['owner' => $userGroups]);

        $form = $this->createForm(ReportType::class, $report, [
            'accounts' => $accounts,
            'budgets' => $budgets,
        ]);

        $form = $this->setFormReportables($request, $form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->denyAccessUnlessGranted('create', $report);
            $generatePdf = $form->get('generatePdf')->isClicked();

            if ($generatePdf) {
                $reportGenerator = $this->reportGeneratorFactory->createInstance($report);
                $report = $reportGenerator->createReport($request->getLocale());
                return $this->generatePdf($user, $report);
            }

            return $this->redirectToRoute('report_show', [
                'request' => $request
            ], 307);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param User $user
     * @Route("/", name="report_show")
     * @Template("Report/show.html.twig")
     */
    public function showAction(Request $request, UserInterface $user)
    {
        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);
        $form = $this->setFormReportables($request, $form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->denyAccessUnlessGranted('create', $report);
            $generatePdf = $form->get('generatePdf')->isClicked();

            $reportGenerator = $this->reportGeneratorFactory->createInstance($report);
            $report = $reportGenerator->createReport($request->getLocale());

            if ($generatePdf) {
                return $this->generatePdf($user, $report);
            }
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    public function setFormReportables(Request $request, FormInterface $form)
    {
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

        return $form;
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
