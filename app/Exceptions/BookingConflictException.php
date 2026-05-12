<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class BookingConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Horário já está ocupado');
    }
}
