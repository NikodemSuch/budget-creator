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

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        // Default Category and Category Group //

        $categoryGroup = new CategoryGroup();
        $categoryGroup->setName("Default Category Group");

        $category = new Category();
        $category->setName("Default Category");
        $category->setGroup($categoryGroup);

        $categoryGroup->setDefaultCategory($category);

        $manager->persist($category);
        $manager->persist($categoryGroup);

        // Default Category and Category Group END //

        // Admin and Default User Group //

        $defaultUserGroup = new UserGroup();
        $defaultUserGroup->setName("Default Group");
        $defaultUserGroup->setIsDefaultGroup(false);

        $admin = new User();
        $admin->setUsername("admin");
        $admin->setEmail("admin@gmail.com");
        $admin->setPlainPassword("admin");
        $admin->setRole(UserRole::ADMIN());

        $this->userManager->persistUserWithCredentials($admin);

        $adminUserGroup = new UserGroup();
        $adminUserGroup->setName("Admin Group");
        $adminUserGroup->setIsDefaultGroup(false);
        $adminUserGroup->addUser($admin);
        $adminUserGroup->setOwner($admin);

        $defaultUserGroup->addUser($admin);
        $defaultUserGroup->setOwner($admin);
        $manager->persist($admin);
        $manager->persist($adminUserGroup);

        // Admin and Default User Group END //

        for ($i = 1; $i <= 5; $i++) {

            $userGroup = new UserGroup();
            $userGroup->setName("Test Group $i");
            $userGroup->setIsDefaultGroup(false);

            for ($j = 1; $j <= 4 ; $j++) {
                $userNum = ($i-1)*4+$j;
                $user = new User();
                $user->setUsername("TestUser$userNum");
                $user->setEmail("testuser$userNum@gmail.com");
                $user->setPlainPassword("user$userNum");

                $this->userManager->persistUserWithCredentials($user);

                $defaultUserGroup->addUser($user);
                $userGroup->addUser($user);
                $manager->persist($user);
            }

            $userGroup->setOwner($user);
            $userGroup->addUser($admin);
            $manager->persist($userGroup);

            $account = new Account();
            $account->setOwner($userGroup);
            $account->setName("Test Account $i");
            $account->setCurrency("PLN");
            $manager->persist($account);

            $budget = new Budget();
            $budget->setOwner($userGroup);
            $budget->setName("Test Budget $i");
            $budget->setCurrency("PLN");
            $manager->persist($budget);

            for ($k = 1; $k <= 4000; $k++) {

                $transaction = new Transaction();
                $transaction->setCreator($admin);
                $transaction->setAccount($account);
                $transaction->setBudget($budget);
                $transaction->setCategory($category);
                $transaction->setTitle("Test Transaction $k");
                $transaction->setAmount(rand(0, 10000));

                $start = new \Datetime('1st Jan 2012');
                $end = new \Datetime('1st Jan 2018');
                $random = new \DateTime('@' . mt_rand($start->getTimestamp(), $end->getTimestamp()));

                $transaction->setCreatedOn($random);

                $manager->persist($transaction);
            }
        }

        $manager->persist($defaultUserGroup);
        $manager->flush();
    }
}
