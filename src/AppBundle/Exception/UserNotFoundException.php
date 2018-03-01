<?php

namespace AppBundle\Exception;

class UserNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("User not found");
    }
}
