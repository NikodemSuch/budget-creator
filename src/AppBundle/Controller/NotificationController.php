<?php

namespace AppBundle\Controller;

use AppBundle\Service\NotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("notification")
 */
class NotificationController extends Controller
{
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
    * @Route("/markAsRead", name="notification_mark_as_read")
    */
    public function markAsReadAction(Request $request, UserInterface $user)
    {
        $notificationId = $request->request->get('notificationId');

        return $this->notificationManager->setUnreadStatus($notificationId, $user, false);
    }

    /**
    * @Route("/markAsUnread", name="notification_mark_as_unread")
    */
    public function markAsUnReadAction(Request $request, UserInterface $user)
    {
        $notificationId = $request->request->get('notificationId');

        return $this->notificationManager->setUnreadStatus($notificationId, $user, true);
    }
}
