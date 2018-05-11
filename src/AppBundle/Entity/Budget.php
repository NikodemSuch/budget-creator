<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BudgetRepository")
 */
class Budget implements Owned, Reportable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserGroup")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank()
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="budget", fetch="EXTRA_LAZY")
     */
    private $transactions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archived;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->archived = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setOwner(UserGroup $owner)
    {
        $this->owner = $owner;
    }

    public function getOwner(): ?UserGroup
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

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setArchived(bool $archived)
    {
        $this->archived = $archived;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function __toString()
    {
        return $this->name;
    }
}
