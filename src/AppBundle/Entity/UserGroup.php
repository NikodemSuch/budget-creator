<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(name="is_default_group", type="boolean")
     */
    private $isDefaultGroup;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIsDefaultGroup($isDefaultGroup)
    {
        $this->isDefaultGroup = $isDefaultGroup;
    }

    public function getIsDefaultGroup()
    {
        return $this->isDefaultGroup;
    }

}
