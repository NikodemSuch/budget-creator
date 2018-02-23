<?php

namespace AppBundle\Exception;

class UserNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = "User not found.";
    }
}
