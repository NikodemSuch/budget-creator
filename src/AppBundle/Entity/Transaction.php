<?php
namespace AppBundle\Entity;

use AppBundle\Utils\EntityHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionRepository")
 */
class Transaction implements Owned
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
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank()
     */
    private $createdOn;

    /**
     * @ORM\OneToOne(targetEntity="Transaction")
     */
    private $transferSlave;

    /**
     * @ORM\Column(name="is_transfer_half", type="boolean")
     */
    private $isTransferHalf;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (!$this->getBudget()) {
            $context->buildViolation("This value is not valid.")
                ->addViolation();

            if (!$this->getAccount()) {
                $context->buildViolation("This value is not valid.")
                    ->addViolation();
            }

            return;
        }

        $accountCurrency = $this->getAccount()->getCurrency();
        $budgetCurrency = $this->getBudget()->getCurrency();

        if ($accountCurrency != $budgetCurrency) {
            $context->buildViolation("Currency of budget ($budgetCurrency) is not the same as currency of account ($accountCurrency).")
                ->addViolation();
        }

        $accountOwner = $this->getAccount()->getOwner();
        $budgetOwner = $this->getBudget()->getOwner();

        if ( $accountOwner != $budgetOwner ) {
            $context->buildViolation("Owner of budget ($accountOwner) is not the same as owner of account ($budgetOwner).")
                ->addViolation();
        }
    }

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
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

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = EntityHelper::SetCreatedOn($createdOn);
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setTransferSlave(Transaction $transferSlave)
    {
        $this->transferSlave = $transferSlave;
    }

    public function getTransferSlave(): Transaction
    {
        return $this->transferSlave;
    }

    public function setIsTransferHalf(bool $isTransferHalf)
    {
        $this->isTransferHalf = $isTransferHalf;
    }

    public function getIsTransferHalf(): bool
    {
        return $this->isTransferHalf;
    }

    public function getOwner(): ?UserGroup
    {
        return $this->getAccount()->getOwner();
    }
}
