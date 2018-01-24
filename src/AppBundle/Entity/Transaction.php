<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="decimal", precision=12, scale=2)
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

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime()
    {
        return $this->dateTime;
    }

    public function setTransferSlave($transferSlave)
    {
        $this->transferSlave = $transferSlave;
    }

    public function getTransferSlave()
    {
        return $this->transferSlave;
    }

    public function setIsTransferHalf($isTransferHalf)
    {
        $this->isTransferHalf = $isTransferHalf;
    }

    public function getIsTransferHalf()
    {
        return $this->isTransferHalf;
    }
}
