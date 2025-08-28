<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'tokenable_id',
        'tokenable_type',
        'abilities',
        'last_used_at',
        'expires_at',
        'plain_token',
        'token',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relasi ke User (atau model lain yg bisa punya token).
     */
    public function user()
    {
        return $this->morphTo('tokenable');
    }

    public function tokenable()
    {
        return $this->morphTo();
    }
}
