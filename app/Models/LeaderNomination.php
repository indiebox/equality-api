<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LeaderNomination
 *
 * @property int $id
 * @property-read \App\Models\Project $project
 * @property int $project_id
 * @property-read \App\Models\User $nominated
 * @property int $nominated_id
 * @property-read \App\Models\User $voter
 * @property int $voter_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\LeaderNominationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @mixin \Eloquent
 */
class LeaderNomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'voter_id',
        'project_id',
        'nominated_id',
    ];

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function voter() {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function nominated() {
        return $this->belongsTo(User::class, 'nominated_id');
    }
}
