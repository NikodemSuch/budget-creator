<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Entity\Notification;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserGroupRepository;
use AppBundle\Repository\NotificationRepository;
use AppBundle\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class NotificationManager
{
    private $em;
    private $userRepository;
    private $userGroupRepository;
    private $notificationRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository,
        NotificationRepository $notificationRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(UserGroup $userGroup, string $content)
    {
        $notification = new Notification();
        $notification->addRecipient($userGroup);
        $notification->setContent($content);

        $this->persistNotification($notification);
    }

    public function persistNotification(Notification $notification)
    {
        $this->sendNotification($notification);
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function sendNotification(Notification $notification)
    {
        $users = $notification->getRecipients()->toArray();
        $users = $users[0]->getUsers()->toArray();

        foreach ($users as $user) {
            $user->addUnreadNotification($notification);
            $this->em->persist($user);
        }
    }

    public function setUnreadStatus(int $notificationId, User $user, bool $unreadStatus) {

        $notification = $this->notificationRepository->find($notificationId);

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
