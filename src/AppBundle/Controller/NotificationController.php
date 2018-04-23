<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Notification;
use AppBundle\Repository\NotificationRepository;
use AppBundle\Service\NotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_USER")
 * @Route("notification")
 */
class NotificationController extends Controller
{
    private $notificationManager;
    private $router;

    public function __construct(NotificationManager $notificationManager, RouterInterface $router)
    {
        $this->notificationManager = $notificationManager;
        $this->router = $router;
    }

    /**
    * @Route("/{id}", name="notification_redirect")
    */
    public function redirectAction(Request $request, UserInterface $user, Notification $notification)
    {
        if ($notification->getRouteName()) {

            if (!$notification->getPreventMarkingAsRead()) {
                $this->notificationManager->setUnreadStatus($notification->getId(), $user, false);
            }

            return $this->redirectToRoute($notification->getRouteName(), $notification->getRouteParameters());
        }

        else {
            throw new RouteNotFoundException();
        }
    }

    /**
    * @Route("/mark-as-read", name="notification_mark-as-read")
    */
    public function markAsReadAction(Request $request, UserInterface $user)
    {
        $notificationId = $request->request->get('notificationId');

        return $this->notificationManager->setUnreadStatus($notificationId, $user, false);
    }

    /**
    * @Route("/mark-as-unread", name="notification_mark-as-unread")
    */
    public function markAsUnReadAction(Request $request, UserInterface $user)
    {
        $notificationId = $request->request->get('notificationId');

        return $this->notificationManager->setUnreadStatus($notificationId, $user, true);
    }
}
