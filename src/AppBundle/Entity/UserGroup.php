<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserGroupRepository")
 */
class UserGroup
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userGroups")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="GroupInvitation", mappedBy="userGroup", cascade={"persist", "remove"})
     */
    private $invitations;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="recipient")
     */
    private $notifications;

    /**
     * @ORM\Column(name="is_default_group", type="boolean")
     */
    private $isDefaultGroup;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $users = $this->getUsers()->toArray();

        if ($users != array_unique($users)) {
            $context->buildViolation("Duplicate user entries. Form contains username and email of the same user.")
                    ->addViolation();
        }
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        $this->users->add($user);

        // Simulate cascade persist on the inverse side (cascade="persist" doesn't work in here).
        // This line provides no need to $user->removeUserGroup($userGroup) when we delete User from group.
        // This really makes things easier in DataTransformer for UserGroup.

        $user->addUserGroup($this);
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->removeUserGroup($this);
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

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification)
    {
        $this->notifications->add($notification);
    }

    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    public function setIsDefaultGroup(bool $isDefaultGroup)
    {
        $this->isDefaultGroup = $isDefaultGroup;
    }

    public function getIsDefaultGroup(): bool
    {
        return $this->isDefaultGroup;
    }

    public function __toString() {
        return $this->name;
    }
}
