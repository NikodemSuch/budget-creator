<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Entity\Notification;
use AppBundle\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class NotificationManager
{
    private $em;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $em, NotificationRepository $notificationRepository)
    {
        $this->em = $em;
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(UserGroup $userGroup, string $content)
    {
        $notification = new Notification();
        $notification->setRecipient($userGroup);
        $notification->setContent($content);
        $users = $userGroup->getUsers()->toArray();

        foreach ($users as $user) {
            $user->addUnreadNotification($notification);
            $this->em->persist($user);
        }

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function setUnreadStatus(int $notificationId, User $user, bool $unreadStatus) {

        $notification = $this->notificationRepository->findOneBy(['id' => $notificationId]);

        if (!$unreadStatus && $user->getUnreadNotifications()->contains($notification)) {
            $user->removeUnreadNotification($notification);
            $this->em->persist($user);
            $this->em->flush();

            return new Response(200);
        }

        if ($unreadStatus && !$user->getUnreadNotifications()->contains($notification)) {
            $user->addUnreadNotification($notification);
            $this->em->persist($user);
            $this->em->flush();

            return new Response(200);
        }

        return new Response(400);
    }

}
