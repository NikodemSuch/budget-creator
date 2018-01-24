<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_group")
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
     * @ORM\Column(type="string", length=45)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(name="is_default_group", type="boolean")
     */
    private $is_default_group;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIsDefaultGroup($is_default_group)
    {
        $this->is_default_group = $is_default_group;
    }

    public function getIsDefaultGroup()
    {
        return $this->is_default_group;
    }

}
