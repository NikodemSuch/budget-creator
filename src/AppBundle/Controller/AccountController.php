<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Service\GroupInvitationManager;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("account")
 */
class AccountController extends Controller
{
    private $em;
    private $accountRepository;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $em, AccountRepository $accountRepository, TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param User $user
     * @Route("/", name="account_index")
     */
    public function indexAction(UserInterface $user, GroupInvitationManager $groupInvitationManager)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $accounts = $this->accountRepository->findBy([
            'owner' => $userGroups,
        ]);
        $accountsBalances = array();

        foreach ($accounts as $account) {
            $accountBalance = $this->transactionRepository->getAccountBalance($account);
            array_push($accountsBalances, $accountBalance);
        }

        $accountsData = array_map(null, $accounts, $accountsBalances);

        return $this->render('Account/index.html.twig', [
            'accounts_data' => $accountsData,
        ]);
    }

    /**
     * @param User $user
     * @Route("/new", name="account_new")
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

            return $this->redirectToRoute('account_show', [
                'id' => $account->getId()
            ]);
        }

        return $this->render('Account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}", name="account_show")
     * @IsGranted("view", subject="account")
     */
    public function showAction(UserInterface $user, Account $account)
    {
        $accountBalance = $this->transactionRepository->getAccountBalance($account);
        $transactions = $this->transactionRepository->getByAccount($account);

        return $this->render('Account/show.html.twig', [
            'transactions' => $transactions,
            'account' => $account,
            'account_balance' => $accountBalance,
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="account_edit")
     * @IsGranted("edit", subject="account")
     */
    public function editAction(Request $request, Account $account, UserInterface $user)
    {
        $editForm = $this->createForm(AccountType::class, $account, [
            'user' => $user,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Account updated!');

            return $this->redirectToRoute('account_show', [
                'id' => $account->getId()
            ]);
        }

        return $this->render('Account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="account_delete")
     * @IsGranted("delete", subject="account")
     */
    public function deleteAction(Account $account)
    {
        $this->em->remove($account);
        $this->em->flush();

        $this->addFlash('success', 'Account deleted!');

        return $this->redirectToRoute('account_index');
    }
}
