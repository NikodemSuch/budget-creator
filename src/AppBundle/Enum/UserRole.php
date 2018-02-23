<?php

namespace AppBundle\Enum;

use MyCLabs\Enum\Enum;

class UserRole extends Enum
{
    const ADMIN         = 'ROLE_ADMIN';
    const USER          = 'ROLE_USER';
}
