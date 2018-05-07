<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Account;
use AppBundle\Entity\Budget;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\Category;
use AppBundle\Entity\CategoryGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Service\UserManager;
use AppBundle\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class DataFixture extends Fixture
{
    private $userManager;
    private $admin;
    private $defaultUserGroup;
    private $defaultCategory;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        $this->createAdmin($manager);
        $this->createDefaultUserGroup();
        $this->createDefaultCategory($manager);

        // Seed the mt_rand function for deterministic value
        mt_srand(1000);

        $accounts = [];
        $budgets = [];

        for ($groupNum = 1; $groupNum <= 5; $groupNum++) {

            $userGroup = $this->createUserGroup($groupNum, $manager);
            $account = $this->createAccount($userGroup, $groupNum, $manager);
            $budget = $this->createBudget($userGroup, $groupNum, $manager);

            array_push($accounts, $account);
            array_push($budgets, $budget);

        }

        for ($transactionNum = 1; $transactionNum <= 10000; $transactionNum++) {
            $transaction = $this->createTransaction(
                $accounts[array_rand($accounts)],
                $budgets[array_rand($budgets)],
                $transactionNum,
                $manager
            );
        }

        $manager->persist($this->defaultUserGroup);
        $manager->flush();
    }

    public function createUserGroup(int $groupNum, ObjectManager $manager)
    {
        $userGroup = new UserGroup();
        $userGroup->setName("Test Group $groupNum");
        $userGroup->setIsDefaultGroup(false);

        for ($groupUserNum = 1; $groupUserNum <= 4 ; $groupUserNum++) {
            $userNum = ($groupNum-1)*4 + $groupUserNum;
            $user = $this->createUser($userNum, $manager);
            $userGroup->addUser($user);
        }

        $userGroup->setOwner($user);
        $userGroup->addUser($this->admin);
        $manager->persist($userGroup);

        return $userGroup;
    }

    public function createUser(int $userNum, ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername("TestUser$userNum");
        $user->setEmail("testuser$userNum@gmail.com");
        $user->setPlainPassword("user$userNum");

        $this->userManager->persistUserWithCredentials($user);
        $this->defaultUserGroup->addUser($user);

        $manager->persist($user);

        return $user;
    }

    public function createAccount(UserGroup $userGroup, int $groupNum, ObjectManager $manager)
    {
        $account = new Account();
        $account->setOwner($userGroup);
        $account->setName("Test Account $groupNum");
        $account->setCurrency("PLN");
        $manager->persist($account);

        return $account;
    }

    public function createBudget(UserGroup $userGroup, int $groupNum, ObjectManager $manager)
    {
        $budget = new Budget();
        $budget->setOwner($userGroup);
        $budget->setName("Test Budget $groupNum");
        $budget->setCurrency("PLN");
        $manager->persist($budget);

        return $budget;
    }

    public function createTransaction(Account $account, Budget $budget, int $transactionNum, ObjectManager $manager)
    {
        $transaction = new Transaction();
        $transaction->setCreator($this->admin);
        $transaction->setCategory($this->defaultCategory);
        $transaction->setAccount($account);
        $transaction->setBudget($budget);
        $transaction->setTitle("Test Transaction $transactionNum");
        $transaction->setAmount(rand(0, 10000));

        $start = new \DateTime('1st Jan 2012');
        $end = new \DateTime('1st Jan 2018');
        $random = new \DateTime('@' . mt_rand($start->getTimestamp(), $end->getTimestamp()));

        $transaction->setCreatedOn($random);

        $manager->persist($transaction);

        return $transaction;
    }

    public function createAdmin(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setUsername("admin");
        $admin->setEmail("admin@gmail.com");
        $admin->setPlainPassword("admin");
        $admin->setRole(UserRole::ADMIN());

        $this->userManager->persistUserWithCredentials($admin);
        $this->admin = $admin;
        $this->createAdminUserGroup($manager);

        $manager->persist($admin);
    }

    public function createAdminUserGroup(ObjectManager $manager)
    {
        $adminUserGroup = new UserGroup();
        $adminUserGroup->setName("Admin Group");
        $adminUserGroup->setIsDefaultGroup(false);
        $adminUserGroup->addUser($this->admin);
        $adminUserGroup->setOwner($this->admin);

        $manager->persist($adminUserGroup);
    }

    public function createDefaultUserGroup()
    {
        $defaultUserGroup = new UserGroup();
        $defaultUserGroup->setName("Default Group");
        $defaultUserGroup->setIsDefaultGroup(false);
        $defaultUserGroup->addUser($this->admin);
        $defaultUserGroup->setOwner($this->admin);

        $this->defaultUserGroup = $defaultUserGroup;
    }

    public function createDefaultCategory(ObjectManager $manager)
    {
        $categoryGroup = new CategoryGroup();
        $categoryGroup->setName("Default Category Group");

        $category = new Category();
        $category->setName("Default Category");
        $category->setGroup($categoryGroup);
        $categoryGroup->setDefaultCategory($category);

        $this->defaultCategory = $category;

        $manager->persist($category);
        $manager->persist($categoryGroup);
    }
}
