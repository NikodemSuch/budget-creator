<?php

namespace AppBundle\Service;

use AppBundle\Entity\ViewNotification;
use AppBundle\Service\NotificationManager;
use AppBundle\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ViewNotificationProvider
{
    private $notificationManager;
    private $notificationRepository;
    private $user;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        NotificationManager $notificationManager,
        NotificationRepository $notificationRepository)
    {
        if ($tokenStorage->getToken() instanceOf AnonymousToken || !$tokenStorage->getToken()) {
            $this->user = null;
            return;
        }
        $this->notificationManager = $notificationManager;
        $this->notificationRepository = $notificationRepository;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function getNotifications()
    {
        $userGroups = $this->user->getUserGroups()->toArray();
        $notifications = $this->notificationRepository->getByGroups($userGroups, $this->notificationManager->getEarliestCreatedOn());
        $unreadNotifications = $this->user->getUnreadNotifications();

        // merge all notifications from period of time with all unread notifications
        $notifications = array_merge($notifications, $unreadNotifications->toArray());

        // remove duplicates in array of objects
        $notifications = array_unique($notifications, SORT_REGULAR);
        $notifications = array_values($notifications);

        // sort notifications by date
        usort($notifications, function($a, $b) {
            return $b->getCreatedOn()->getTimestamp() - $a->getCreatedOn()->getTimestamp();
        });

        // get array of viewNotification objects
        $viewNotifications = array();

        foreach ($notifications as $notification) {
            $read = !$unreadNotifications->contains($notification);
            $viewNotification = new ViewNotification($notification, $read);

            array_push($viewNotifications, $viewNotification);
        }

        return array_slice($viewNotifications, 0, 30);
    }
}
