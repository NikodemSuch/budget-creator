<?php

namespace AppBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use AppBundle\Enum\UserRole;

class UserRoleType extends Type
{
    public function getName()
    {
        return 'userRole';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR(256) COMMENT "userRole"';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (! UserRole::isValid($value)) {
            throw new \InvalidArgumentException(sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                UserRole::class,
                implode('", "', UserRole::keys())
            ));
        }
        return new UserRole($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string) $value;
    }
}
