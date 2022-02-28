<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Team
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $url
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invite[] $invites
 * @property-read int|null $invites_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LeaderNomination[] $projectsLeaderNominations
 * @property-read int|null $projects_leader_nominations_count
 * @method static \Database\Factories\TeamFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @mixin \Eloquent
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'url',
    ];

    /*
    |-------------------------------------------------------------
    | Methods
    |-------------------------------------------------------------
    */

    public function isMember($user) {
        if ($this->relationLoaded('members')) {
            return $this->members->contains($user);
        }

        $key = $user;

        if ($user instanceof Model) {
            $key = $user->getKey();
        }

        return $this->members()->wherePivot('user_id', $key)->exists();
    }

    public function creator() {
        if ($this->relationLoaded('members')) {
            return $this->members->first(function($member) {
                return $member->pivot->is_creator;
            });
        }

        return $this->members()->wherePivot('is_creator', true)->first();
    }

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function projects() {
        return $this->hasMany(Project::class);
    }

    public function members() {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->withTimestamps('joined_at')
            ->withPivot('is_creator');
    }

    public function invites() {
        return $this->hasMany(Invite::class);
    }

    public function projectsLeaderNominations() {
        return $this->hasManyThrough(LeaderNomination::class, Project::class);
    }
}
