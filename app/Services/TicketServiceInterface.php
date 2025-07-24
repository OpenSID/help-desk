<?php

namespace App\Services;

use App\Models\Ticket;

interface TicketServiceInterface
{
    public function getTicketByCode(string $code): ?Ticket;
}
