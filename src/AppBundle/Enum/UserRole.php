<?php

namespace AppBundle\Enum;

use MyCLabs\Enum\Enum;

class UserRole extends Enum
{
    const SUPERADMIN    = 'ROLE_SUPERADMIN';
    const ADMIN         = 'ROLE_ADMIN';
    const PRIVILEGEUSER = 'ROLE_PRIVILEGEUSER';
    const USER          = 'ROLE_USER';
}
