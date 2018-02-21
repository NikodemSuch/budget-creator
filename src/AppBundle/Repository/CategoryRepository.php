<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getByGroup($categoryGroup)
    {
        return $this->createQueryBuilder('category')
            ->where('category.group = :group')
            ->setParameter('group', $categoryGroup)
            ->getQuery()
            ->getArrayResult();
    }

    public function getCountByGroup($categoryGroup)
    {
        return $this->createQueryBuilder('category')
            ->where('category.group = :group')
            ->setParameter('group', $categoryGroup)
            ->select('count(category.group) as categoriesNumber')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
