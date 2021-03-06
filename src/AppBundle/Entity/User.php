<?php

namespace AppBundle\Entity;

use AppBundle\Enum\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="UserGroup", inversedBy="users", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="user_membership")
     */
    private $userGroups;

    /**
     * @ORM\OneToMany(targetEntity="GroupInvitation", mappedBy="user")
     */
    private $invitations;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="Notification")
     * @ORM\JoinTable(name="unread_notifications")
     */
    private $unreadNotifications;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="userRole")
     * @Assert\NotBlank()
     */
    private $role;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->unreadNotifications = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->isActive = true;
        $this->role = UserRole::USER();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserGroups($userGroups)
    {
        $this->userGroups = $userGroups;
    }

    public function getUserGroups()
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->add($userGroup);
    }

    public function removeUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }

    public function getDefaultGroup()
    {
        return $this->getUserGroups()
                    ->filter(function(UserGroup $userGroup) {
                        return $userGroup->getIsDefaultGroup() == true;
                    })->first();
    }

    public function setInvitations($invitations)
    {
        $this->invitations = $invitations;
    }

    public function getInvitations()
    {
        return $this->invitations;
    }

    public function addInvitation(GroupInvitation $invitation)
    {
        $this->invitations->add($invitation);
    }

    public function removeInvitation(GroupInvitation $invitation)
    {
        $this->invitations->removeElement($invitation);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function setUnreadNotifications($unreadNotifications)
    {
        $this->unreadNotifications = $unreadNotifications;
    }

    public function getUnreadNotifications()
    {
        return $this->unreadNotifications;
    }

    public function addUnreadNotification(Notification $unreadNotifications)
    {
        $this->unreadNotifications->add($unreadNotifications);
    }

    public function removeUnreadNotification(Notification $unreadNotifications)
    {
        $this->unreadNotifications->removeElement($unreadNotifications);
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role)
    {
        $this->role = $role;
    }

    public function getRoles(): Array
    {
        return [(string) $this->getRole()];
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    public function __toString() {
        return $this->username;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            ) = unserialize($serialized);
        }
    }
