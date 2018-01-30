<?php
namespace AppBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * 2 precision money type, using decimal as an SQL type, and integer as PHP type.
 */
class MoneyType extends Type
{
    const MONEY = 'MONEY';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['scale'] = 2;
        return $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return $sqlExpr . ' / 100';
    }

    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return $sqlExpr . ' * 100';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (int) $value;
    }

    public function getBindingType()
    {
        return \PDO::PARAM_INT;
    }

    public function getName()
    {
        return self::MONEY;
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
