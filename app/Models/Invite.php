<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Invite
 *
 * @property int $id
 * @property-read \App\Models\User $invited
 * @property int $invited_id
 * @property-read \App\Models\User|null $inviter
 * @property int|null $inviter_id
 * @property-read \App\Models\Team $team
 * @property int $team_id
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $declined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\InviteFactory factory(...$parameters)
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @method static Builder|Invite onlyAccepted()
 * @method static Builder|Invite onlyDeclined()
 * @method static Builder|Invite onlyPending()
 * @method static Builder|Invite filterByStatus($status)
 * @mixin \Eloquent
 */
class Invite extends Model
{
    use HasFactory;

    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    /*
    |-------------------------------------------------------------
    | Methods
    |-------------------------------------------------------------
    */

    public function getStatus()
    {
        if ($this->accepted_at == null && $this->declined_at == null) {
            return self::STATUS_PENDING;
        }

        if ($this->accepted_at != null) {
            return self::STATUS_ACCEPTED;
        }

        if ($this->declined_at != null) {
            return self::STATUS_DECLINED;
        }
    }

    /*
    |-------------------------------------------------------------
    | Scopes
    |-------------------------------------------------------------
    */

    public function scopeFilterByStatus(Builder $query, $status)
    {
        return $query
            ->when($status == self::STATUS_PENDING, function ($query) {
                return $query->onlyPending();
            })->when($status == self::STATUS_ACCEPTED, function ($query) {
                return $query->onlyAccepted();
            })->when($status == self::STATUS_DECLINED, function ($query) {
                return $query->onlyDeclined();
            });
    }

    public function scopeOnlyPending(Builder $query)
    {
        return $query
            ->whereNull('accepted_at')
            ->whereNull('declined_at');
    }

    public function scopeOnlyAccepted(Builder $query)
    {
        return $query->whereNotNull('accepted_at');
    }

    public function scopeOnlyDeclined(Builder $query)
    {
        return $query->whereNotNull('declined_at');
    }

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invited()
    {
        return $this->belongsTo(User::class, 'invited_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
