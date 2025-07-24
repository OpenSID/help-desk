<?php

namespace App\Services;

use App\Models\Ticket;

class PublicTicketService implements TicketServiceInterface
{
    public function getTicketByCode(string $code): ?Ticket
    {
        return Ticket::where('id', $code)->with('status')->first();
    }
}
