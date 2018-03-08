<?php

namespace AppBundle\Service;

use AppBundle\Entity\ViewNotification;
use AppBundle\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationGlobal
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
            $notifications = array_merge($notifications, $this->notificationRepository->findByUserGroup($userGroup));
        }

        // remove duplicates in array of objects
        $notifications = array_unique($notifications, SORT_REGULAR);
        $notifications = array_values($notifications);

        // sort notifications by date
        usort($notifications, function($a, $b) {
            return strtotime($b->getCreatedOn()->format('Y-m-d H:i:s')) - strtotime($a->getCreatedOn()->format('Y-m-d H:i:s'));
        });

        // get array of viewNotification objects
        $unreadNotifications = $this->user->getUnreadNotifications();
        $viewNotifications = array();

        foreach ($notifications as $notification) {
            if ($unreadNotifications->contains($notification)) {
                $read = false;
            }
            else {
                $read = true;
            }
            $viewNotification = new ViewNotification($notification, $read);
            array_push($viewNotifications, $viewNotification);
        }

        return array_slice($viewNotifications, 0, 30);
    }
}
