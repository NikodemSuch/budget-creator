<?php

namespace AppBundle\Entity;

abstract class Reportable {

    public function getPropertyName() {
        $fqcn = get_class($this);
        $className = basename($fqcn);

        return strtolower($className);
    }
}
