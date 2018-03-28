<?php

namespace AppBundle\Entity;

class ViewNotification
{
    private $notification;
    private $read;

    public function __construct(Notification $notification, bool $read)
    {
        $this->notification = $notification;
        $this->read = $read;
    }

    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setRead(bool $read)
    {
        $this->read = $read;
    }

    public function isRead(): ?bool
    {
        return $this->read;
    }
}
