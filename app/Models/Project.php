<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Project
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property-read \App\Models\Team $team
 * @property int $team_id
 * @property-read \App\Models\User $leader
 * @property int $leader_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeaderNomination[] $leaderNominations
 * @property-read int|null $leader_nominations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Board[] $boards
 * @property-read int|null $boards_count
 * @method static \Database\Factories\ProjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @mixin \Eloquent
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /*
    |-------------------------------------------------------------
    | Methods
    |-------------------------------------------------------------
    */

    public function isLeader($user)
    {
        if ($user instanceof User) {
            return $this->leader_id == $user->id;
        } elseif (is_numeric($user)) {
            return $this->leader_id == $user;
        } else {
            return false;
        }
    }

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function leaderNominations()
    {
        return $this->hasMany(LeaderNomination::class);
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }
}
