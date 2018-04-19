<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

     public function getByGroups($userGroups)
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.recipient IN (:userGroups)')
            ->setParameter('userGroups', $userGroups)
            ->getQuery()
            ->getResult();
    }

}
