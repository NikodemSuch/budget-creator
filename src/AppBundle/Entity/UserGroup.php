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
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $name;

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
