<?php

namespace AppBundle\Entity;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class ViewNotification
{
    private $notification;
    private $read;
    private $url;

    public function __construct(Notification $notification, bool $read, string $url = null)
    {
        $this->notification = $notification;
        $this->read = $read;
        $this->url = $url;
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

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
