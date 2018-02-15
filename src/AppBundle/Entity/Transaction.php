<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity="Account")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="Budget")
     */
    private $budget;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="money")
     * @Assert\NotBlank()
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $dateTime;

    /**
     * @ORM\OneToOne(targetEntity="Transaction")
     */
    private $transferSlave;

    /**
     * @ORM\Column(name="is_transfer_half", type="boolean")
     */
    private $isTransferHalf;

    public function __construct()
    {
        $this->dateTime = new \DateTime();
        $this->isTransferHalf = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setBudget(Budget $budget)
    {
        $this->budget = $budget;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setDateTime(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function setTransferSlave(Transaction $transferSlave)
    {
        $this->transferSlave = $transferSlave;
    }

    public function getTransferSlave(): Transaction
    {
        return $this->transferSlave;
    }

    public function setIsTransferHalf(boolean $isTransferHalf)
    {
        $this->isTransferHalf = $isTransferHalf;
    }

    public function getIsTransferHalf(): boolean
    {
        return $this->isTransferHalf;
    }
}
