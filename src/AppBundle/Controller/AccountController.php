<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Service\GroupInvitationManager;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_USER")
 * @Route("account")
 */
class AccountController extends Controller
{
    private $em;
    private $accountRepository;
    private $transactionRepository;

    public function __construct(
        EntityManagerInterface $em,
        AccountRepository $accountRepository,
        TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="account_index")
     * @Template("Account/index.html.twig")
     */
    public function indexAction(UserInterface $user, GroupInvitationManager $groupInvitationManager)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $accounts = $this->accountRepository->findBy([
            'owner' => $userGroups,
            'archived' => false,
        ]);
        $accountsBalances = array();

        foreach ($accounts as $account) {
            $accountBalance = $this->transactionRepository->getAccountBalance($account);
            array_push($accountsBalances, $accountBalance);
        }

        $accountsData = array_map(null, $accounts, $accountsBalances);

        return ['accounts_data' => $accountsData];
    }

    /**
     * @param User $user
     * @Route("/new", name="account_new")
     * @Template("Account/new.html.twig")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $account = new Account();

        $form = $this->createForm(AccountType::class, $account, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($account);
            $this->em->flush();

            $this->addFlash('success', 'Account created!');

            return $this->redirectToRoute('account_show', ['id' => $account->getId()]);
        }

        return [
            'account' => $account,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param User $user
     * @Route("/{id}", name="account_show")
     * @IsGranted("view", subject="account")
     * @Template("Account/show.html.twig")
     */
    public function showAction(Request $request, UserInterface $user, Account $account)
    {
        if ($account->isArchived()) {
            throw $this->createNotFoundException();
        }

        $page = $request->query->get('page') ?: 1;
        $resultsPerPage = $request->query->get('results') ?: 10;

        $query = $this->transactionRepository->getByAccountQuery($account);
        $adapter = new DoctrineORMAdapter($query);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($resultsPerPage);
        $pagerfanta->setCurrentPage($page);

        return [
            'transaction_pager' => $pagerfanta,
            'account' => $account,
            'account_balance' => $this->transactionRepository->getAccountBalance($account),
        ];
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="account_edit")
     * @IsGranted("edit", subject="account")
     * @Template("Account/edit.html.twig")
     */
    public function editAction(Request $request, Account $account, UserInterface $user)
    {
        if ($account->isArchived()) {
            throw $this->createNotFoundException();
        }

        $owner = $account->getOwner();
        $hasTransactions = (bool) $account->getTransactions()->count();

        $editForm = $this->createForm(AccountType::class, $account, [
            'user' => $user,
            'owner' => $owner,
            'has_transactions' => $hasTransactions,
        ]);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            if ($hasTransactions && $account->getOwner() != $owner) {
                throw new BadRequestHttpException();
            }

            $this->em->flush();
            $this->addFlash('success', 'Account updated!');

            return $this->redirectToRoute('account_show', ['id' => $account->getId()]);
        }

        return [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="account_delete")
     * @IsGranted("delete", subject="account")
     */
    public function deleteAction(Account $account)
    {
        if ($account->isArchived()) {
            throw $this->createNotFoundException();
        }

        $account->setArchived(true);
        $this->em->persist($account);
        $this->em->flush();

        $this->addFlash('success', 'Account deleted!');

        return $this->redirectToRoute('account_index');
    }
}
