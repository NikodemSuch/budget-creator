<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupInvitationRepository")
 */
class GroupInvitation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="invitations", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="invitations", cascade={"persist", "remove"})
     */
    private $userGroup;

    /**
     * @ORM\OneToOne(targetEntity="Notification")
     * @Assert\NotBlank()
     */
    private $notification;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $createdOn;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function __construct(User $user = null, UserGroup $userGroup = null)
    {
        $this->user = $user;
        $this->userGroup = $userGroup;
        $this->createdOn = new \DateTime();
        $this->active = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUserGroup(UserGroup $userGroup)
    {
        $this->userGroup = $userGroup;
    }

    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->getCreatedOn()->add(new \DateInterval('P7D'));
    }

    public function hasExpired(): bool
    {
        $now = new \DateTime();

        return $now > $this->getExpirationDate();
    }
}
