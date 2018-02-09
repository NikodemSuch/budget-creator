<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
*/
class CategoryGroup
{
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;

    /**
    * @ORM\OneToOne(targetEntity="Category")
    */
    private $defaultCategory;

    /**
    * @ORM\Column(type="string", length=100)
    * @Assert\NotBlank()
    */
    private $name;

    public function setDefaultCategory(Category $defaultCategory)
    {
        $this->defaultCategory = $defaultCategory;
    }

    public function getDefaultCategory(): Category
    {
        return $this->defaultCategory;
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
