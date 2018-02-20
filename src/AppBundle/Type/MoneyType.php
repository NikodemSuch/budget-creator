<?php
namespace AppBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MoneyType extends Type
{
    const NAME = 'money';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBigIntTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (int) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (string) $value;
    }

    public function getBindingType()
    {
        return \PDO::PARAM_INT;
    }

    public function getName()
    {
        return self::NAME;
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
