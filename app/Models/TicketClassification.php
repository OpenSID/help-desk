<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketClassification extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    /**
     * Relasi one-to-many ke model Ticket.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'classification_id');
    }
}
