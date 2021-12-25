<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Invite
 *
 * @property int $id
 * @property int $team_id
 * @property int|null $inviter_id
 * @property int $invited_id
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $declined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $invited
 * @property-read \App\Models\User|null $inviter
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\InviteFactory factory(...$parameters)
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite onlyAccepted()
 * @method static Builder|Invite onlyDeclined()
 * @method static Builder|Invite onlyPending()
 * @method static Builder|Invite query()
 * @method static Builder|Invite sortByStatus($status)
 * @mixin \Eloquent
 */
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

    public function scopeSortByStatus(Builder $query, $status) {
        return $query
            ->when($status == 'pending', function($query) {
                return $query->onlyPending();
            })->when($status == 'accepted', function($query) {
                return $query->onlyAccepted();
            })->when($status == 'declined', function($query) {
                return $query->onlyDeclined();
            });
    }

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
