<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    /*
    |-------------------------------------------------------------
    | Scopes
    |-------------------------------------------------------------
    */

    public function scopeOnlyPending(Builder $query) {
        return $query
            ->whereNull('accepted_at')
            ->whereNull('declined_at');
    }

    public function scopeOnlyAccepted(Builder $query) {
        return $query->whereNotNull('accepted_at');
    }

    public function scopeOnlyDeclined(Builder $query) {
        return $query->whereNotNull('declined_at');
    }

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function inviter() {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invited() {
        return $this->belongsTo(User::class, 'invited_id');
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }
}
