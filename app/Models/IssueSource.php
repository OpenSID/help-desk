<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'color'
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'issue_source_id', 'id')->withTrashed();
    }
}
