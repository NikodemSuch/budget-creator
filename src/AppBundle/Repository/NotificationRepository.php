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

    public function findByUserGroup($userGroup)
    {
        return $this->createQueryBuilder('notification')
            ->innerJoin('notification.recipient', 'notification_membership', 'WITH', 'notification_membership.id = :user_group_id')
            ->setParameter('user_group_id', $userGroup)
            ->getQuery()
            ->getResult();
    }
}
