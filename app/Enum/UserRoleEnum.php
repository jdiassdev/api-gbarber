<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRoleEnum: string
{
    case CLIENT = 'client';
    case BARBER = 'barber';
    case ADMIN = 'admin';
}
