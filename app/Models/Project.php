<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @method static \Database\Factories\ProjectFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @mixin \Eloquent
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function leader() {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }

    public function leaderNominations() {
        return $this->hasMany(LeaderNomination::class);
    }
}
