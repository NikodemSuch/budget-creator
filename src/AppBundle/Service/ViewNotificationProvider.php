<?php

namespace AppBundle\Service;

use AppBundle\Entity\ViewNotification;
use AppBundle\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ViewNotificationProvider
{
    private $notificationRepository;
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage, NotificationRepository $notificationRepository)
    {
        if ($tokenStorage->getToken() instanceOf AnonymousToken || !$tokenStorage->getToken()) {
            $this->user = null;
            return;
        }
        $this->notificationRepository = $notificationRepository;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function getNotifications()
    {
        $userGroups = $this->user->getUserGroups()->toArray();
        $notifications = array();

        foreach ($userGroups as $userGroup) {
            $notifications = array_merge($notifications, $this->notificationRepository->findBy(['recipient' => $userGroup]));
        }

        // remove duplicates in array of objects
        $notifications = array_unique($notifications, SORT_REGULAR);
        $notifications = array_values($notifications);

        // sort notifications by date
        usort($notifications, function($a, $b) {
            return $b->getCreatedOn()->getTimestamp() - $a->getCreatedOn()->getTimestamp();
        });

        // get array of viewNotification objects
        $unreadNotifications = $this->user->getUnreadNotifications();
        $viewNotifications = array();

        foreach ($notifications as $notification) {
            $read = !$unreadNotifications->contains($notification);
            $viewNotification = new ViewNotification($notification, $read);
            
            array_push($viewNotifications, $viewNotification);
        }

        return array_slice($viewNotifications, 0, 30);
    }
}
