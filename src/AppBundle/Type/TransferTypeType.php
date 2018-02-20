<?php

namespace AppBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use AppBundle\Enum\TransferType;

class TransferTypeType extends Type
{
    public function getName()
    {
        return 'transferType';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR(256) COMMENT "transferType"';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (! TransferType::isValid($value)) {
            throw new \InvalidArgumentException(sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                TransferType::class,
                implode('", "', TransferType::keys())
            ));
        }
        return new TransferType($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string) $value;
    }
}
