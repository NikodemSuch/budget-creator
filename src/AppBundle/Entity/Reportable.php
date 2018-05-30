<?php

namespace AppBundle\Entity;

abstract class Reportable
{

    public function getPropertyName()
    {
        $fqcn = get_class($this);
        $className = preg_split("/\\\\/", $fqcn);
        $className = end($className);

        return strtolower($className);
    }
}
