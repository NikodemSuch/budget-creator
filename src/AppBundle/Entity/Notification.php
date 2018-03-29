<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $createdOn;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="notifications", cascade={"persist", "remove"})
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $routeName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $routeParameters;

    public function __construct()
    {
        $this->createdOn = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function setRecipient(UserGroup $recipient)
    {
        $this->recipient = $recipient;
    }

    public function getRecipient(): ?UserGroup
    {
        return $this->recipient;
    }

    public function setRouteName(string $routeName)
    {
        $this->routeName = $routeName;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

     public function setRouteParameters(array $routeParameters)
    {
        $this->routeParameters = $routeParameters;
    }

    public function getRouteParameters(): ?array
    {
        return $this->routeParameters;
    }
}