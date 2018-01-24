<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class Category
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CategoryGroup")
     * @ORM\JoinColumn(name="categorygroup_id", referencedColumnName="id")
     */
    private $categoryGroup;

    /**
     * @ORM\Column(type="string", length=45)
     * @Assert\NotBlank()
     */
    private $name;

    public function setCategoryGroup($categoryGroup)
    {
        $this->categoryGroup = $categoryGroup;
    }

    public function getCategoryGroup()
    {
        return $this->categoryGroup;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
