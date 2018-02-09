<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
*/
class Budget
{
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="UserGroup")
    */
    private $owner;

    /**
    * @ORM\Column(type="string", length=200)
    * @Assert\NotBlank()
    */
    private $name;

    public function setOwner(UserGroup $owner)
    {
        $this->owner = $owner;
    }

    public function getOwner(): UserGroup
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
}
