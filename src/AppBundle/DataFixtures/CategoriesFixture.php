<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Category;
use AppBundle\Entity\CategoryGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoriesFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $categoryGroup = new CategoryGroup();
            $categoryGroup->setName("CategoryGroup $i");

            for ($j = 1; $j <= 5 ; $j++) {
                $category = new Category();
                $category->setName("Category $j of CategoryGroup $i");
                $category->setGroup($categoryGroup);
                $manager->persist($category);
            }

            $categoryGroup->setDefaultCategory($category);
            $manager->persist($categoryGroup);
        }

        $manager->flush();
    }
}
