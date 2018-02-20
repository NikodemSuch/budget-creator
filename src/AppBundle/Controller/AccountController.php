<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
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
    public function indexAction(UserInterface $user)
    {
        $userGroups = $user->getUserGroups()->toArray();
        $accounts = $this->accountRepository->findBy([
            'owner' => $userGroups,
        ]);
        $accountsBalances = array();

        foreach ($accounts as $account) {
            $accountBalance = $this->transactionRepository->getAccountBalance($account->getId());
            array_push($accountsBalances, $accountBalance);
        }

        $accountsData = array_map(null, $accounts, $accountsBalances);

        return $this->render('account/index.html.twig', [
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

            return $this->redirectToRoute('account_show', [
                'id' => $account->getId()
            ]);
        }

        return $this->render('account/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}", name="account_show")
     */
    public function showAction(UserInterface $user, Account $account)
    {
        $this->denyAccessUnlessGranted('view', $account);
        $userGroups = $user->getUserGroups()->toArray();
        $deleteForm = $this->createDeleteForm($account);
        $accountBalance = $this->transactionRepository->getAccountBalance($account->getId());

        $transactions = $this->transactionRepository->findBy([
            'creator' => $userGroups,
            'account' => $account->getId(),
        ]);

        return $this->render('account/show.html.twig', [
            'transactions' => $transactions,
            'account' => $account,
            'account_balance' => $accountBalance,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param User $user
     * @Route("/{id}/edit", name="account_edit")
     */
    public function editAction(Request $request, Account $account, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('edit', $account);
        $editForm = $this->createForm(AccountType::class, $account, [
            'user' => $user,
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('account_show', [
                'id' => $account->getId()
            ]);
        }

        return $this->render('account/edit.html.twig', [
            'account' => $account,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="account_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Account $account)
    {
        $this->denyAccessUnlessGranted('delete', $account);
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($account);
            $this->em->flush();
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     * @param Account $account
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(Account $account)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('account_delete', ['id' => $account->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
