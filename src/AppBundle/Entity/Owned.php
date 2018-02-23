<?php

namespace AppBundle\Entity;

interface Owned
{
    public function getOwner(): ?UserGroup;
}
