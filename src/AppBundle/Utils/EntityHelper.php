<?php

namespace AppBundle\Utils;

class EntityHelper
{
    public static function setCreatedOn($createdOn)
    {
        if ($createdOn instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($createdOn);
        }

        elseif ($createdOn instanceof \DateTimeImmutable) {
            return $createdOn;
        }

        else {
            throw new \InvalidArgumentException('Argument needs to be DateTime or DateTimeImmutable object.');
        }
    }
}
