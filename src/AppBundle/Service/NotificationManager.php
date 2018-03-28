<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Entity\Notification;
use AppBundle\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class NotificationManager
{
    private $em;
    private $notificationRepository;
    private $router;

    public function __construct(EntityManagerInterface $em, NotificationRepository $notificationRepository, RouterInterface $router)
    {
        $this->em = $em;
        $this->notificationRepository = $notificationRepository;
        $this->router = $router;
    }

    public function createNotification(UserGroup $userGroup, string $content, $urlPath = null, array $urlParameters = null)
    {
        $notification = new Notification();
        $notification->setRecipient($userGroup);
        $notification->setContent($content);
        $users = $userGroup->getUsers()->toArray();


        foreach ($users as $user) {
            $user->addUnreadNotification($notification);
            $this->em->persist($user);
        }

        if (!empty($urlPath)) {
            $notification->setUrlPath($urlPath);
            $notification->setUrlParameters($urlParameters);
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
