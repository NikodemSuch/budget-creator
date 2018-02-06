<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Acelaya\Doctrine\Type\PhpEnumType;
use AppBundle\Type\TransferType;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 */
class Transfer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="transaction")
     */
    private $transactionMaster;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $transferType;

    public function __construct()
    {
        $this->transferType = TransferType::OTHER();
    }

    public function setTransactionMaster(Transaction $transactionMaster)
    {
        $this->transactionMaster = $transactionMaster;
    }

    public function getTransactionMaster(): Transaction
    {
        return $this->transactionMaster;
    }

    public function setTransferType(string $transferType)
    {
        $this->transferType = $transferType;
    }

    public function getTransferType(): string
    {
        return $this->transferType;
    }
}
