<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
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
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $group;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setGroup(CategoryGroup $group)
    {
        $this->group = $group;
    }

    public function getGroup(): ?CategoryGroup
    {
        return $this->group;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
