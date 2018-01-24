<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\Column(type="string", length=45)
     * @Assert\NotBlank()
     */
    private $transferType;

    public function setTransactionMaster($transactionMaster)
    {
        $this->transactionMaster = $transactionMaster;
    }

    public function getTransactionMaster()
    {
        return $this->transactionMaster;
    }

    public function setTransferType($transferType)
    {
        $this->transferType = $transferType;
    }

    public function getTransferType()
    {
        return $this->transferType;
    }
}
