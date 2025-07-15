<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'color'
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'master_application_id', 'id')->withTrashed();
    }
}
